<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\UserSubscription;
use Carbon\Carbon;

class PlanCheckoutService
{
    /**
     * Price a plan for this user: leftover unused days of the current plan
     * are credited against the new plan total.
     *
     * Example: Plan A ₹30 / 30 days, 5 days left → credit ₹5.
     * Plan B ₹60 → payable ₹55. New billing cycle and benefits start now.
     *
     * @return array{
     *   is_upgrade: bool,
     *   plan_total: float,
     *   payable: float,
     *   currency: string,
     *   pricing: array<string, mixed>,
     *   credit: array<string, mixed>
     * }
     */
    public function quote(Plan $plan, ?UserSubscription $current = null): array
    {
        $pricing = $plan->pricingBreakdown('monthly');
        $planTotal = round((float) $pricing['total'], 2);
        $credit = $this->unusedCredit($current, $plan);
        $creditAmount = round((float) $credit['amount'], 2);
        $payable = round(max(0, $planTotal - $creditAmount), 2);

        return [
            'is_upgrade' => (bool) $credit['is_upgrade'],
            'plan_total' => $planTotal,
            'payable'    => $payable,
            'currency'   => $pricing['currency'] ?? 'INR',
            'pricing'    => $pricing,
            'credit'     => $credit,
        ];
    }

    /**
     * @return array{
     *   is_upgrade: bool,
     *   amount: float,
     *   remaining_days: int,
     *   used_days: int,
     *   total_days: int,
     *   daily_rate: float,
     *   from_plan_id: int|null,
     *   from_plan_name: string|null
     * }
     */
    public function unusedCredit(?UserSubscription $current, Plan $target): array
    {
        $empty = [
            'is_upgrade'      => false,
            'amount'          => 0.0,
            'remaining_days'  => 0,
            'used_days'       => 0,
            'total_days'      => 0,
            'daily_rate'      => 0.0,
            'from_plan_id'    => null,
            'from_plan_name'  => null,
        ];

        if (! $current || ! $this->isPaidActive($current)) {
            return $empty;
        }

        $current->loadMissing('plan');

        if ((int) $current->plan_id === (int) $target->id) {
            return $empty;
        }

        $tz = 'Asia/Kolkata';
        $now = Carbon::now($tz)->startOfDay();
        $starts = $current->starts_at?->copy()->timezone($tz)->startOfDay();
        $expires = $current->expires_at?->copy()->timezone($tz)->startOfDay();

        if (! $starts || ! $expires || $expires->lte($now)) {
            return $empty;
        }

        $totalDays = max(1, (int) $starts->diffInDays($expires));
        $remainingDays = max(0, (int) $now->diffInDays($expires));
        $remainingDays = min($remainingDays, $totalDays);
        $usedDays = max(0, $totalDays - $remainingDays);

        $paid = (float) ($current->amount_paid ?? 0);
        $dailyRate = $totalDays > 0 ? round($paid / $totalDays, 4) : 0.0;
        $amount = round($dailyRate * $remainingDays, 2);

        return [
            'is_upgrade'      => true,
            'amount'          => $amount,
            'remaining_days'  => $remainingDays,
            'used_days'       => $usedDays,
            'total_days'      => $totalDays,
            'daily_rate'      => $dailyRate,
            'from_plan_id'    => (int) $current->plan_id,
            'from_plan_name'  => $current->plan?->name,
        ];
    }

    public function isPaidActive(?UserSubscription $subscription): bool
    {
        if (! $subscription) {
            return false;
        }

        return $subscription->status === 'active'
            && $subscription->payment_status === 'paid'
            && $subscription->expires_at
            && $subscription->expires_at->isFuture();
    }

    /** Razorpay minimum is ₹1. Anything smaller activates without a charge. */
    public function requiresOnlinePayment(array $quote): bool
    {
        return (int) round((float) ($quote['payable'] ?? 0) * 100) >= 100;
    }

    public function snapshot(Plan $plan, array $quote): array
    {
        return [
            'plan_id'    => (int) $plan->id,
            'is_upgrade' => (bool) ($quote['is_upgrade'] ?? false),
            'plan_total' => (float) ($quote['plan_total'] ?? 0),
            'payable'    => (float) ($quote['payable'] ?? 0),
            'currency'   => $quote['currency'] ?? 'INR',
            'pricing'    => $quote['pricing'] ?? $plan->pricingBreakdown('monthly'),
            'credit'     => $quote['credit'] ?? [],
            'quoted_at'  => now()->toDateTimeString(),
        ];
    }

    public function quoteFromSnapshot(UserSubscription $subscription, Plan $plan): array
    {
        $checkout = is_array($subscription->meta) ? ($subscription->meta['checkout'] ?? null) : null;

        if (
            is_array($checkout)
            && (int) ($checkout['plan_id'] ?? 0) === (int) $plan->id
            && array_key_exists('payable', $checkout)
        ) {
            $pricing = $checkout['pricing'] ?? $plan->pricingBreakdown('monthly');

            return [
                'is_upgrade' => (bool) ($checkout['is_upgrade'] ?? false),
                'plan_total' => (float) ($checkout['plan_total'] ?? $pricing['total'] ?? 0),
                'payable'    => (float) $checkout['payable'],
                'currency'   => $checkout['currency'] ?? ($pricing['currency'] ?? 'INR'),
                'pricing'    => $pricing,
                'credit'     => $checkout['credit'] ?? $this->unusedCredit(null, $plan),
            ];
        }

        return $this->quote($plan, $this->isPaidActive($subscription) ? $subscription : null);
    }

    public function storeSnapshot(UserSubscription $subscription, Plan $plan, array $quote): array
    {
        $meta = is_array($subscription->meta) ? $subscription->meta : [];
        $meta['checkout'] = $this->snapshot($plan, $quote);

        return $meta;
    }

    public function activate(
        UserSubscription $subscription,
        Plan $plan,
        array $quote,
        array $payment = []
    ): UserSubscription {
        $startsAt = Carbon::now();
        $expiresAt = $startsAt->copy()->addDays($plan->billingDays());

        $subscription->update([
            'plan_id'             => $plan->id,
            'amount_paid'         => (float) $quote['payable'],
            'currency'            => $quote['currency'] ?? 'INR',
            'payment_status'      => 'paid',
            'payment_reference'   => $payment['payment_reference'] ?? $payment['razorpay_payment_id'] ?? $subscription->payment_reference,
            'razorpay_order_id'   => $payment['razorpay_order_id'] ?? $subscription->razorpay_order_id,
            'razorpay_payment_id' => $payment['razorpay_payment_id'] ?? $subscription->razorpay_payment_id,
            'status'              => 'active',
            'starts_at'           => $startsAt,
            'expires_at'          => $expiresAt,
            'meta'                => $this->storeSnapshot($subscription, $plan, $quote),
        ]);

        return $subscription->fresh(['plan']);
    }
}
