<?php

namespace App\Traits;

use App\Models\ConsultationSession;
use App\Models\UserSubscription;
use Carbon\Carbon;

trait HasSubscription
{
    /**
     * Check if the user has any active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return UserSubscription::where('user_id', $this->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Get the current active subscription with plan details.
     */
    public function activeSubscription(): ?UserSubscription
    {
        return UserSubscription::with('plan')
            ->where('user_id', $this->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->latest('starts_at')
            ->first();
    }

    /**
     * Check if user is subscribed to a specific plan slug.
     * Usage: $user->hasSubscriptionOfLevel('premium')
     */
    public function hasSubscriptionOfLevel(string $level): bool
    {
        return UserSubscription::where('user_id', $this->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->whereHas('plan', fn ($q) => $q->where('slug', $level))
            ->exists();
    }

    /**
     * Free career-counselling minutes included in the active plan for this billing period.
     *
     * Essential example: 30 minutes once per period, only for a 30-minute booking
     * (duration must be ≤ remaining and ≤ free_session_max_duration).
     *
     * @return array{
     *   covered: bool,
     *   minutes_limit: int|null,
     *   minutes_used: int,
     *   minutes_remaining: int|null,
     *   max_session_minutes: int|null,
     *   unlimited: bool,
     *   plan_name: string|null,
     *   subscription_id: string|null,
     *   benefit: string|null
     * }
     */
    public function planSessionAllowance(?int $requestedDuration = null): array
    {
        $subscription = $this->activeSubscription();
        $empty = [
            'covered'              => false,
            'minutes_limit'        => null,
            'minutes_used'         => 0,
            'minutes_remaining'    => null,
            'max_session_minutes'  => null,
            'unlimited'            => false,
            'plan_name'            => null,
            'subscription_id'      => null,
            'benefit'              => null,
            // Legacy keys kept for older API clients
            'limit'                => null,
            'used'                 => 0,
            'remaining'            => null,
        ];

        if (! $subscription || ! $subscription->plan) {
            return $empty;
        }

        $limits = $subscription->plan->limits;
        if (is_string($limits)) {
            $limits = json_decode($limits, true) ?: [];
        }
        if (! is_array($limits)) {
            $limits = [];
        }

        $minutesLimit = $this->resolveFreeSessionMinutes($limits);
        $maxSession = $this->resolveFreeSessionMaxDuration($limits, $minutesLimit);

        $base = [
            'plan_name'           => $subscription->plan->name,
            'subscription_id'     => $subscription->subscription_id,
            'benefit'             => 'career_counselling',
            'max_session_minutes' => $maxSession,
        ];

        if ($minutesLimit === null) {
            return array_merge($empty, $base);
        }

        $used = $this->freeSessionMinutesUsed();

        // -1 = unlimited free minutes
        if ($minutesLimit < 0) {
            $covered = $requestedDuration === null
                || ($maxSession === null || $requestedDuration <= $maxSession);

            return array_merge($base, [
                'covered'           => $covered,
                'minutes_limit'     => -1,
                'minutes_used'      => $used,
                'minutes_remaining' => null,
                'unlimited'         => true,
                'limit'             => -1,
                'used'              => $used,
                'remaining'         => null,
            ]);
        }

        $remaining = max(0, $minutesLimit - $used);
        $covered = false;
        if ($requestedDuration !== null && $requestedDuration > 0) {
            $covered = $remaining >= $requestedDuration
                && ($maxSession === null || $requestedDuration <= $maxSession)
                && in_array($requestedDuration, ConsultationSession::BOOKING_DURATIONS, true);
        }

        return array_merge($base, [
            'covered'           => $covered,
            'minutes_limit'     => $minutesLimit,
            'minutes_used'      => $used,
            'minutes_remaining' => $remaining,
            'unlimited'         => false,
            'limit'             => $minutesLimit,
            'used'              => $used,
            'remaining'         => $remaining,
        ]);
    }

    /**
     * @param  array<string, mixed>  $limits
     */
    private function resolveFreeSessionMinutes(array $limits): ?int
    {
        foreach (['free_session_minutes', 'career_counselling_minutes'] as $key) {
            if (array_key_exists($key, $limits) && $limits[$key] !== '' && $limits[$key] !== null) {
                return (int) $limits[$key];
            }
        }

        // Legacy: count-based sessions (each counts as one booking, not minutes).
        // Treat as N × 30 minutes with max session 30 for backward compatibility.
        if (array_key_exists('sessions', $limits) && $limits['sessions'] !== '' && $limits['sessions'] !== null) {
            $sessions = (int) $limits['sessions'];
            if ($sessions < 0) {
                return -1;
            }

            return $sessions * 30;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $limits
     */
    private function resolveFreeSessionMaxDuration(array $limits, ?int $minutesLimit): ?int
    {
        foreach (['free_session_max_duration', 'career_counselling_max_session_minutes'] as $key) {
            if (array_key_exists($key, $limits) && $limits[$key] !== '' && $limits[$key] !== null) {
                return max(ConsultationSession::MIN_SLOT_MINUTES, (int) $limits[$key]);
            }
        }

        if ($minutesLimit !== null && $minutesLimit > 0) {
            return min($minutesLimit, max(ConsultationSession::BOOKING_DURATIONS));
        }

        if ($minutesLimit !== null && $minutesLimit < 0) {
            return max(ConsultationSession::BOOKING_DURATIONS);
        }

        // Legacy sessions count → 30-minute free bookings only
        if (array_key_exists('sessions', $limits) && $limits['sessions'] !== '' && $limits['sessions'] !== null) {
            return 30;
        }

        return null;
    }

    /**
     * Whether the mentee can view progress / journey reports under their active plan.
     */
    public function canAccessProgressReport(): bool
    {
        $subscription = $this->activeSubscription();
        if (! $subscription?->plan) {
            return false;
        }

        return (bool) ($subscription->plan->progress_report_enabled ?? false);
    }

    /**
     * Free (plan-covered) minutes already used in the current subscription period.
     */
    public function freeSessionMinutesUsed(): int
    {
        [$start, $end] = $this->planUsageWindow();

        return (int) ConsultationSession::where('mentee_id', $this->id)
            ->where('payment_method', 'plan')
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', '!=', ConsultationSession::STATUS_CANCELLED)
            ->sum('duration_minutes');
    }

    /**
     * @deprecated Use freeSessionMinutesUsed(); kept for older call sites.
     */
    public function sessionsUsedThisMonth(): int
    {
        [$start, $end] = $this->planUsageWindow();

        return ConsultationSession::where('mentee_id', $this->id)
            ->where('payment_method', 'plan')
            ->whereBetween('scheduled_at', [$start, $end])
            ->where('status', '!=', ConsultationSession::STATUS_CANCELLED)
            ->count();
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    public function planUsageWindow(): array
    {
        $subscription = $this->activeSubscription();

        if ($subscription?->starts_at) {
            $start = $subscription->starts_at->copy()->timezone('Asia/Kolkata');
            $end = ($subscription->expires_at ?? Carbon::now('Asia/Kolkata'))
                ->copy()
                ->timezone('Asia/Kolkata');

            return [$start, $end];
        }

        $now = Carbon::now('Asia/Kolkata');

        return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
    }
}
