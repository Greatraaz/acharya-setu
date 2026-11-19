<?php

namespace App\Traits;

use App\Models\ConsultationSession;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;

trait HasSubscription
{
    public function subscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

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
     * True if the mentee has ever completed a paid plan subscription
     * (active, expired, or cancelled — as long as payment_status is paid).
     */
    public function hasEverSubscribed(): bool
    {
        return UserSubscription::where('user_id', $this->id)
            ->where('payment_status', 'paid')
            ->exists();
    }

    public function hasNeverSubscribed(): bool
    {
        return ! $this->hasEverSubscribed();
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
     * Free plan minutes for this billing period.
     *
     * Benefit pool depends on the session host:
     * - Expert → career_counselling
     * - Mentor → in_house_mentor
     *
     * Pass `$host` when checking a booking, or `$benefitKey` to read a specific pool.
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
     *   benefit: string|null,
     *   host_role: string|null
     * }
     */
    public function planSessionAllowance(
        ?int $requestedDuration = null,
        ?User $host = null,
        ?string $benefitKey = null,
    ): array {
        $subscription = $this->activeSubscription();
        $resolvedBenefit = $this->resolveSessionBenefitKey($host, $benefitKey);
        $hostRole = $this->hostRoleForBenefit($resolvedBenefit);

        $empty = [
            'covered'              => false,
            'minutes_limit'        => null,
            'minutes_used'         => 0,
            'minutes_remaining'    => null,
            'max_session_minutes'  => null,
            'unlimited'            => false,
            'plan_name'            => null,
            'subscription_id'      => null,
            'benefit'              => $resolvedBenefit,
            'host_role'            => $hostRole,
            // Legacy keys kept for older API clients
            'limit'                => null,
            'used'                 => 0,
            'remaining'            => null,
        ];

        if (! $subscription || ! $subscription->plan) {
            return $empty;
        }

        $plan = $subscription->plan;
        $limits = $plan->limits;
        if (is_string($limits)) {
            $limits = json_decode($limits, true) ?: [];
        }
        if (! is_array($limits)) {
            $limits = [];
        }

        $billing = $this->subscriptionBillingPeriod($subscription);
        $minutesLimit = $this->resolveBenefitMinutes($plan, $limits, $resolvedBenefit, $billing);
        $maxSession = $this->resolveFreeSessionMaxDuration($limits, $minutesLimit);

        $base = [
            'plan_name'           => $plan->name,
            'subscription_id'     => $subscription->subscription_id,
            'benefit'             => $resolvedBenefit,
            'host_role'           => $hostRole,
            'max_session_minutes' => $maxSession,
        ];

        if ($minutesLimit === null) {
            return array_merge($empty, $base);
        }

        $used = $this->freeSessionMinutesUsed($resolvedBenefit);

        // -1 = unlimited free minutes
        if ($minutesLimit < 0) {
            $covered = $requestedDuration === null
                || ($maxSession === null || $requestedDuration <= $maxSession);

            [$periodStart, $periodEnd] = $this->planUsageWindow();

            return array_merge($base, [
                'covered'           => $covered,
                'minutes_limit'     => -1,
                'minutes_used'      => $used,
                'minutes_remaining' => null,
                'unlimited'         => true,
                'payment_required'  => false,
                'next_free_at'      => null,
                'period'            => [
                    'starts_at' => $periodStart->toDateTimeString(),
                    'ends_at'   => $periodEnd->toDateTimeString(),
                ],
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

        [$periodStart, $periodEnd] = $this->planUsageWindow();
        $subEnd = $subscription->expires_at?->copy()->timezone('Asia/Kolkata');
        $nextFreeAt = null;
        if ($remaining <= 0 && $periodEnd && $subEnd && $periodEnd->lt($subEnd)) {
            $nextFreeAt = $periodEnd->copy();
        }

        return array_merge($base, [
            'covered'           => $covered,
            'minutes_limit'     => $minutesLimit,
            'minutes_used'      => $used,
            'minutes_remaining' => $remaining,
            'unlimited'         => false,
            'payment_required'  => $remaining <= 0,
            'next_free_at'      => $nextFreeAt?->toDateTimeString(),
            'period'            => [
                'starts_at' => $periodStart->toDateTimeString(),
                'ends_at'   => $periodEnd->toDateTimeString(),
            ],
            'limit'             => $minutesLimit,
            'used'              => $used,
            'remaining'         => $remaining,
        ]);
    }

    /**
     * Map booking host / explicit key → benefit pool.
     */
    public function resolveSessionBenefitKey(?User $host = null, ?string $benefitKey = null): string
    {
        if (is_string($benefitKey) && $benefitKey !== '') {
            $key = strtolower(trim($benefitKey));

            return match ($key) {
                'in_house', 'in_house_mentor', 'mentor' => 'in_house_mentor',
                default => 'career_counselling',
            };
        }

        if ($host?->isMentor()) {
            return 'in_house_mentor';
        }

        // Experts (and unscoped callers) use career counselling.
        return 'career_counselling';
    }

    public function hostRoleForBenefit(string $benefitKey): string
    {
        return $benefitKey === 'in_house_mentor' ? 'mentor' : 'expert';
    }

    /**
     * @param  array<string, mixed>  $limits
     */
    private function resolveBenefitMinutes($plan, array $limits, string $benefitKey, ?string $billing = null): ?int
    {
        if ($benefitKey === 'in_house_mentor') {
            return $plan->allowanceMinutes('in_house_mentor', $billing);
        }

        $fromBenefits = $plan->allowanceMinutes('career_counselling', $billing);
        if ($fromBenefits !== null) {
            return $fromBenefits;
        }

        return $this->resolveFreeSessionMinutes($limits);
    }

    private function subscriptionBillingPeriod(?UserSubscription $subscription): string
    {
        if (! $subscription) {
            return 'monthly';
        }

        $meta = is_array($subscription->meta) ? $subscription->meta : [];
        $billing = data_get($meta, 'checkout.billing')
            ?? data_get($meta, 'billing')
            ?? null;

        return \App\Models\Plan::normalizeBilling($billing);
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
        return (bool) $this->activeSubscription()?->plan;
    }

    /**
     * Free (plan-covered) minutes already used in the current monthly benefit bucket.
     */
    public function freeSessionMinutesUsed(?string $benefitKey = 'career_counselling'): int
    {
        return app(\App\Services\CounsellingBenefitService::class)
            ->countedMinutesForBenefit($this, $benefitKey ?? 'career_counselling');
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
            ->where(function ($q) {
                $q->where('status', '!=', ConsultationSession::STATUS_CANCELLED)
                    ->orWhere('forfeit_plan_benefit', true);
            })
            ->count();
    }

    /**
     * Current free-minute benefit window.
     *
     * Free counselling is marketed as "X min / month", so yearly subscriptions are
     * split into monthly buckets anchored to subscription starts_at. That lets the
     * dashboard show used/left and when the next free benefit unlocks.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    public function planUsageWindow(): array
    {
        $subscription = $this->activeSubscription();
        $now = Carbon::now('Asia/Kolkata');

        if (! $subscription?->starts_at) {
            return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        }

        $subStart = $subscription->starts_at->copy()->timezone('Asia/Kolkata');
        $subEnd = ($subscription->expires_at ?? $now->copy()->addMonth())
            ->copy()
            ->timezone('Asia/Kolkata');

        // Walk monthly buckets from subscription start until we find the one containing now.
        $periodStart = $subStart->copy();
        $periodEnd = $subStart->copy()->addMonth();

        while ($periodEnd->lte($now) && $periodEnd->lt($subEnd)) {
            $periodStart = $periodEnd->copy();
            $periodEnd = $periodStart->copy()->addMonth();
        }

        if ($periodEnd->gt($subEnd)) {
            $periodEnd = $subEnd->copy();
        }

        // Guard: if somehow before the sub starts, use the first bucket.
        if ($now->lt($subStart)) {
            $firstEnd = $subStart->copy()->addMonth();
            if ($firstEnd->gt($subEnd)) {
                $firstEnd = $subEnd->copy();
            }

            return [$subStart->copy(), $firstEnd];
        }

        return [$periodStart, $periodEnd];
    }
}
