<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Plan;
use App\Models\PlanInvoice;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PlanInvoiceService
{
    /**
     * Create invoice for a paid subscription if one does not already exist.
     */
    public function ensureForSubscription(UserSubscription $subscription, string $generatedBy = 'system'): PlanInvoice
    {
        $subscription->loadMissing(['user', 'plan', 'invoice']);

        if ($subscription->payment_status !== 'paid') {
            throw new InvalidArgumentException('Invoice can only be generated for paid subscriptions.');
        }

        return DB::transaction(function () use ($subscription, $generatedBy) {
            $locked = UserSubscription::with(['user', 'plan', 'invoice'])
                ->lockForUpdate()
                ->findOrFail($subscription->id);

            $existing = $this->existingInvoiceForPayment($locked);
            if ($existing) {
                return $existing;
            }

            $plan = $locked->plan;
            $user = $locked->user;
            $checkout = is_array($locked->meta) ? ($locked->meta['checkout'] ?? []) : [];
            $credit = is_array($checkout['credit'] ?? null) ? $checkout['credit'] : [];
            $creditAmount = (float) ($credit['amount'] ?? 0);

            $pricing = $checkout['pricing'] ?? ($plan
                ? $plan->pricingBreakdown('monthly')
                : [
                    'base' => (float) $locked->amount_paid,
                    'cgst_percent' => 0,
                    'sgst_percent' => 0,
                    'igst_percent' => 0,
                    'cgst_amount' => 0,
                    'sgst_amount' => 0,
                    'igst_amount' => 0,
                    'tax_total' => 0,
                    'total' => (float) $locked->amount_paid,
                    'currency' => strtoupper($locked->currency ?? 'INR'),
                ]);

            $payable = (float) ($locked->amount_paid
                ?: ($checkout['payable'] ?? $pricing['total'] ?? 0));

            // Reverse-engineer tax only when the charged amount is a discounted
            // plan total with no leftover-day credit snapshot.
            if (
                $creditAmount <= 0
                && $payable > 0
                && abs($payable - (float) ($pricing['total'] ?? 0)) > 0.05
            ) {
                $base = round($payable / (1 + ((float) (Plan::filledTaxPercent($pricing['cgst_percent'] ?? null) ?? 0) + (float) (Plan::filledTaxPercent($pricing['sgst_percent'] ?? null) ?? 0) + (float) (Plan::filledTaxPercent($pricing['igst_percent'] ?? null) ?? 0)) / 100), 2);
                if ($base <= 0) {
                    $base = $payable;
                }
                $cgstAmount = round($base * (float) $pricing['cgst_percent'] / 100, 2);
                $sgstAmount = round($base * (float) $pricing['sgst_percent'] / 100, 2);
                $igstAmount = round($base * (float) ($pricing['igst_percent'] ?? 0) / 100, 2);
                $taxTotal = round($cgstAmount + $sgstAmount + $igstAmount, 2);
                $pricing = array_merge($pricing, [
                    'base' => $base,
                    'cgst_amount' => $cgstAmount,
                    'sgst_amount' => $sgstAmount,
                    'igst_amount' => $igstAmount,
                    'tax_total' => $taxTotal,
                    'total' => $payable,
                ]);
            } else {
                $pricing['total'] = $payable;
            }

            $seller = AppSetting::billing();
            $prefix = $this->invoicePrefix();

            return PlanInvoice::create([
                'user_subscription_id'    => $locked->id,
                'user_id'                 => $locked->user_id,
                'plan_id'                 => $locked->plan_id,
                'invoice_number'          => $this->nextInvoiceNumber($prefix),
                'invoice_date'            => now()->toDateString(),
                'billing_name'            => $user?->name,
                'billing_email'           => $user?->email,
                'billing_phone'           => $user?->phone,
                'plan_name'               => $plan?->name ?? $plan?->plan_name ?? 'Subscription Plan',
                'base_amount'             => $pricing['base'],
                'cgst_percent'            => Plan::filledTaxPercent($pricing['cgst_percent'] ?? null) ?? 0,
                'sgst_percent'            => Plan::filledTaxPercent($pricing['sgst_percent'] ?? null) ?? 0,
                'igst_percent'            => Plan::filledTaxPercent($pricing['igst_percent'] ?? null) ?? 0,
                'cgst_amount'             => Plan::filledTaxPercent($pricing['cgst_percent'] ?? null) !== null ? $pricing['cgst_amount'] : 0,
                'sgst_amount'             => Plan::filledTaxPercent($pricing['sgst_percent'] ?? null) !== null ? $pricing['sgst_amount'] : 0,
                'igst_amount'             => Plan::filledTaxPercent($pricing['igst_percent'] ?? null) !== null ? ($pricing['igst_amount'] ?? 0) : 0,
                'tax_total'               => $pricing['tax_total'],
                'total_amount'            => $pricing['total'],
                'currency'                => $pricing['currency'] ?? strtoupper($locked->currency ?? 'INR'),
                'payment_reference'       => $locked->payment_reference ?: $locked->razorpay_payment_id,
                'razorpay_order_id'       => $locked->razorpay_order_id,
                'razorpay_payment_id'     => $locked->razorpay_payment_id,
                'subscription_starts_at'  => $locked->starts_at,
                'subscription_expires_at' => $locked->expires_at,
                'seller_name'             => $seller['company_name'],
                'seller_gstin'            => $seller['gstin'],
                'seller_address'          => $seller['address'],
                'seller_email'            => $seller['email'],
                'seller_phone'            => $seller['phone'],
                'status'                  => 'issued',
                'generated_by'            => $generatedBy,
                'meta'                    => [
                    'subscription_code' => $locked->subscription_id,
                    'billing_days'      => $plan?->billingDays(),
                    'discount'          => [
                        'applied'       => (bool) ($pricing['discount_active'] ?? false),
                        'percent'       => (float) ($pricing['discount_percent'] ?? 0),
                        'amount'        => (float) ($pricing['discount_amount'] ?? 0),
                        'original_base' => (float) ($pricing['original_base'] ?? $pricing['base']),
                        'expires_at'    => $pricing['discount_expires_at'] ?? null,
                    ],
                    'credit'            => [
                        'applied'         => $creditAmount > 0,
                        'amount'          => $creditAmount,
                        'remaining_days'  => (int) ($credit['remaining_days'] ?? 0),
                        'used_days'       => (int) ($credit['used_days'] ?? 0),
                        'total_days'      => (int) ($credit['total_days'] ?? 0),
                        'daily_rate'      => (float) ($credit['daily_rate'] ?? 0),
                        'from_plan_id'    => $credit['from_plan_id'] ?? null,
                        'from_plan_name'  => $credit['from_plan_name'] ?? null,
                    ],
                    'plan_total'        => (float) ($checkout['plan_total'] ?? $pricing['original_total'] ?? $pricing['total'] ?? $payable),
                ],
            ]);
        });
    }

    private function existingInvoiceForPayment(UserSubscription $locked): ?PlanInvoice
    {
        $ref = $locked->payment_reference ?: $locked->razorpay_payment_id;

        $query = PlanInvoice::where('user_subscription_id', $locked->id)
            ->where('plan_id', $locked->plan_id);

        if ($ref) {
            $query->where(function ($q) use ($ref, $locked) {
                $q->where('payment_reference', $ref);
                if ($locked->razorpay_payment_id) {
                    $q->orWhere('razorpay_payment_id', $locked->razorpay_payment_id);
                }
            });
        } else {
            $query->whereNull('payment_reference')
                ->where('subscription_starts_at', $locked->starts_at);
        }

        return $query->latest('id')->first();
    }

    private function invoicePrefix(): string
    {
        $raw = trim((string) AppSetting::get('invoice_prefix', 'INV'));

        return strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $raw) ?: 'INV');
    }

    private function nextInvoiceNumber(string $prefix): string
    {
        $period = now()->format('Ym');
        $needle = $prefix.'-'.$period.'-';

        $latest = PlanInvoice::where('invoice_number', 'like', $needle.'%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $needle.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
