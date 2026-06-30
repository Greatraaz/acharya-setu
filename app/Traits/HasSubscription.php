<?php

namespace App\Traits;

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
            ->whereHas('plan', fn($q) => $q->where('level', $level))
            ->exists();
    }
}
