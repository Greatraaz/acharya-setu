<?php

namespace App\Services;

use App\Models\ConsultationSession;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * In-house counselling and in-house mentor minute ledger.
 *
 * A confirmed plan booking scheduled after the covering subscription expires
 * is kept on the calendar, but its minutes are not counted as used.
 */
class CounsellingBenefitService
{
    /**
     * @param  Collection<int, ConsultationSession>  $sessions
     */
    public function annotate(Collection $sessions): void
    {
        $codes = $sessions
            ->map(fn (ConsultationSession $session) => $this->subscriptionCode($session))
            ->filter()
            ->unique()
            ->values();

        $subscriptions = $codes->isEmpty()
            ? collect()
            : UserSubscription::with('plan:id,name')
                ->whereIn('subscription_id', $codes)
                ->get()
                ->keyBy('subscription_id');

        foreach ($sessions as $session) {
            $session->setAttribute('plan_lapse', $this->lapse($session, $subscriptions));
        }
    }

    /**
     * @param  Collection<string, UserSubscription>|null  $subscriptions
     * @return array<string, mixed>|null
     */
    public function lapse(ConsultationSession $session, ?Collection $subscriptions = null): ?array
    {
        if ($session->payment_method !== 'plan' || $session->status !== ConsultationSession::STATUS_UPCOMING) {
            return null;
        }

        $code = $this->subscriptionCode($session);
        if ($code === null) {
            return null;
        }

        $subscription = $subscriptions?->get($code)
            ?? UserSubscription::with('plan:id,name')->where('subscription_id', $code)->first();

        if (! $subscription?->expires_at || ! $session->scheduled_at) {
            return null;
        }

        $expires = $subscription->expires_at->copy()->timezone('Asia/Kolkata');
        $scheduled = $session->scheduled_at->copy()->timezone('Asia/Kolkata');

        if ($scheduled->lte($expires)) {
            return null;
        }

        $planName = $subscription->plan?->name ?? 'plan';
        $expiresLabel = $expires->format('d M Y, h:i A');
        $sessionLabel = $scheduled->format('d M Y, h:i A');

        return [
            'subscription_id' => $subscription->subscription_id,
            'plan_name'       => $planName,
            'expires_at'      => $expires->toDateTimeString(),
            'scheduled_at'    => $scheduled->toDateTimeString(),
            'mentee_message'  => "This booking stays confirmed, but your {$planName} plan expires on {$expiresLabel}, before the session on {$sessionLabel}. In-house counselling will not cover it. Cancel the booking, or pay for it as a normal session.",
            'mentor_message'  => "This booking stays confirmed, but the mentee's {$planName} plan expires on {$expiresLabel}, before the session on {$sessionLabel}. In-house counselling will not cover it.",
        ];
    }

    public function countedMinutes(User $user, ?int $mentorId = null): int
    {
        return (int) $this->periodSessions($user, $mentorId)
            ->reject(fn (ConsultationSession $session) => filled($session->plan_lapse))
            ->sum('duration_minutes');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function history(User $user, string $scope): array
    {
        $mentorId = $scope === 'in_house' ? (int) ($user->assigned_mentor_id ?? 0) : null;
        if ($scope === 'in_house' && $mentorId <= 0) {
            return [];
        }

        $sessions = $this->periodSessions($user, $scope === 'in_house' ? $mentorId : null);
        $sessions->loadMissing('mentor:id,name', 'mentee:id,name');

        return $sessions->map(fn (ConsultationSession $session) => $this->historyRow($session, 'mentee'))->all();
    }

    /**
     * Plan-covered sessions this mentor delivered for mentees assigned to them.
     *
     * @return list<array<string, mixed>>
     */
    public function mentorHistory(User $mentor): array
    {
        $sessions = ConsultationSession::query()
            ->with(['mentee:id,name,assigned_mentor_id'])
            ->where('mentor_id', $mentor->id)
            ->where('payment_method', 'plan')
            ->where(function ($q) {
                $q->where('status', '!=', ConsultationSession::STATUS_CANCELLED)
                    ->orWhere('forfeit_plan_benefit', true);
            })
            ->whereHas('mentee', fn ($q) => $q->where('assigned_mentor_id', $mentor->id))
            ->latest('scheduled_at')
            ->limit(20)
            ->get();

        $this->annotate($sessions);

        return $sessions->map(fn (ConsultationSession $session) => $this->historyRow($session, 'mentor'))->all();
    }

    /**
     * @return array{
     *   limit: int|null,
     *   unlimited: bool,
     *   used: int,
     *   raw_used: int,
     *   remaining: int|null,
     *   rows: list<array<string, mixed>>,
     *   lapsed: list<array<string, mixed>>
     * }
     */
    public function snapshot(User $user): array
    {
        $allowance = $user->planSessionAllowance();
        $rows = $this->history($user, 'counselling');
        $rows = array_merge($rows, $this->uncoveredFutureRows($user, $rows));
        $lapsed = array_values(array_filter($rows, fn (array $row) => ! $row['counts']));
        $used = (int) array_sum(array_map(
            fn (array $row) => $row['counts'] ? (int) $row['minutes'] : 0,
            $rows
        ));
        $raw = (int) array_sum(array_column($rows, 'minutes'));
        $unlimited = (bool) ($allowance['unlimited'] ?? false);
        $limit = $unlimited ? null : ($allowance['minutes_limit'] ?? null);

        return [
            'limit'     => $limit,
            'unlimited' => $unlimited,
            'used'      => $used,
            'raw_used'  => $raw,
            'remaining' => $unlimited || $limit === null ? null : max(0, (int) $limit - $used),
            'rows'      => $rows,
            'lapsed'    => $lapsed,
        ];
    }

    public function reconcile(User $user): string
    {
        $snapshot = $this->snapshot($user);
        $excluded = count($snapshot['lapsed']);
        $limit = $snapshot['unlimited'] ? 'unlimited' : (($snapshot['limit'] ?? 0).' min');

        $description = "Reconciled in-house counselling for {$user->name} ({$user->email}). "
            ."Counted {$snapshot['used']} min of {$limit}. "
            ."{$excluded} confirmed booking(s) are scheduled after the plan expires and are not counted.";

        ActivityLogger::record('counselling_reconciled', $description, 'subscriptions', 'info');

        return $excluded > 0
            ? "Usage reconciled. {$snapshot['used']} min counted. {$excluded} future booking(s) are no longer covered because the plan expires first."
            : "Usage already matches the ledger. {$snapshot['used']} min counted this period.";
    }

    /**
     * Confirmed plan bookings that sit after the covering plan expires.
     *
     * @param  list<array<string, mixed>>  $already
     * @return list<array<string, mixed>>
     */
    private function uncoveredFutureRows(User $user, array $already): array
    {
        $refs = array_filter(array_column($already, 'ref'));
        $sessions = ConsultationSession::query()
            ->with('mentor:id,name')
            ->where('mentee_id', $user->id)
            ->where('payment_method', 'plan')
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->where('scheduled_at', '>', now())
            ->when($refs !== [], fn ($q) => $q->whereNotIn('booking_ref', $refs))
            ->orderBy('scheduled_at')
            ->get();

        $this->annotate($sessions);

        return $sessions
            ->filter(fn (ConsultationSession $session) => filled($session->plan_lapse))
            ->map(fn (ConsultationSession $session) => $this->historyRow($session, 'mentee'))
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, ConsultationSession>
     */
    private function periodSessions(User $user, ?int $mentorId = null): Collection
    {
        [$start, $end] = $user->planUsageWindow();

        $query = ConsultationSession::query()
            ->where('mentee_id', $user->id)
            ->where('payment_method', 'plan')
            ->whereBetween('scheduled_at', [$start, $end])
            ->where(function ($q) {
                $q->where('status', '!=', ConsultationSession::STATUS_CANCELLED)
                    ->orWhere('forfeit_plan_benefit', true);
            })
            ->orderBy('scheduled_at');

        if ($mentorId) {
            $query->where('mentor_id', $mentorId);
        }

        $sessions = $query->get();
        $this->annotate($sessions);

        return $sessions;
    }

    /**
     * @return array<string, mixed>
     */
    private function historyRow(ConsultationSession $session, string $audience): array
    {
        $lapse = $session->plan_lapse;
        $with = $audience === 'mentor'
            ? ($session->mentee->name ?? 'Mentee')
            : ($session->mentor->name ?? 'Mentor');

        return [
            'at'       => $session->scheduled_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A'),
            'with'     => $with,
            'title'    => $session->title ?: 'Session',
            'minutes'  => (int) $session->duration_minutes,
            'status'   => ucfirst((string) $session->status),
            'counts'   => $lapse === null,
            'note'     => $audience === 'mentor'
                ? ($lapse['mentor_message'] ?? 'Counted toward this mentee’s plan.')
                : ($lapse['mentee_message'] ?? 'Counted toward this period.'),
            'ref'      => $session->booking_ref,
        ];
    }

    private function subscriptionCode(ConsultationSession $session): ?string
    {
        $ref = (string) $session->payment_reference;
        if (! str_starts_with($ref, 'PLAN-')) {
            return null;
        }

        $code = substr($ref, 5);

        return $code !== '' && $code !== 'FREE' ? $code : null;
    }
}
