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
     * Check if user is subscribed to a specific plan level.
     * Usage: $user->hasSubscriptionOfLevel('premium')
     */
    public function hasSubscriptionOfLevel(string $level): bool
    {
        return UserSubscription::where('user_id', $this->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->whereHas('plan', fn ($q) => $q->where('level', $level))
            ->exists();
    }

    /**
     * Sessions included in the active plan for the current calendar month.
     *
     * @return array{
     *   covered: bool,
     *   limit: int|null,
     *   used: int,
     *   remaining: int|null,
     *   unlimited: bool,
     *   plan_name: string|null,
     *   subscription_id: string|null
     * }
     */
    public function planSessionAllowance(): array
    {
        $subscription = $this->activeSubscription();
        $empty = [
            'covered'         => false,
            'limit'           => null,
            'used'            => 0,
            'remaining'       => null,
            'unlimited'       => false,
            'plan_name'       => null,
            'subscription_id' => null,
        ];

        if (! $subscription || ! $subscription->plan) {
            return $empty;
        }

        $limits = $subscription->plan->limits;
        if (is_string($limits)) {
            $limits = json_decode($limits, true) ?: [];
        }
        if (! is_array($limits) || ! array_key_exists('sessions', $limits) || $limits['sessions'] === '' || $limits['sessions'] === null) {
            return array_merge($empty, [
                'plan_name'       => $subscription->plan->name,
                'subscription_id' => $subscription->subscription_id,
            ]);
        }

        $limit = (int) $limits['sessions'];
        $used = $this->sessionsUsedThisMonth();

        // -1 = unlimited included sessions
        if ($limit < 0) {
            return [
                'covered'         => true,
                'limit'           => -1,
                'used'            => $used,
                'remaining'       => null,
                'unlimited'       => true,
                'plan_name'       => $subscription->plan->name,
                'subscription_id' => $subscription->subscription_id,
            ];
        }

        $remaining = max(0, $limit - $used);

        return [
            'covered'         => $remaining > 0,
            'limit'           => $limit,
            'used'            => $used,
            'remaining'       => $remaining,
            'unlimited'       => false,
            'plan_name'       => $subscription->plan->name,
            'subscription_id' => $subscription->subscription_id,
        ];
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
     * Confirmed/upcoming sessions in the current month (counts against plan allowance).
     */
    public function sessionsUsedThisMonth(): int
    {
        $start = Carbon::now('Asia/Kolkata')->startOfMonth();
        $end = Carbon::now('Asia/Kolkata')->endOfMonth();

        return ConsultationSession::where('mentee_id', $this->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->whereNotIn('status', [
                ConsultationSession::STATUS_CANCELLED,
                ConsultationSession::STATUS_PENDING,
            ])
            ->where(function ($q) {
                $q->whereNull('payment_status')
                    ->orWhere('payment_status', '!=', 'pending');
            })
            ->count();
    }
}
