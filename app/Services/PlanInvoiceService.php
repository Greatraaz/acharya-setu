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

        if ($subscription->invoice) {
            return $subscription->invoice;
        }

        if ($subscription->payment_status !== 'paid') {
            throw new InvalidArgumentException('Invoice can only be generated for paid subscriptions.');
        }

        return DB::transaction(function () use ($subscription, $generatedBy) {
            $locked = UserSubscription::with(['user', 'plan', 'invoice'])
                ->lockForUpdate()
                ->findOrFail($subscription->id);

            if ($locked->invoice) {
                return $locked->invoice;
            }

            $plan = $locked->plan;
            $user = $locked->user;
            $pricing = $plan
                ? $plan->pricingBreakdown('monthly')
                : [
                    'base' => (float) $locked->amount_paid,
                    'cgst_percent' => 0,
                    'sgst_percent' => 0,
                    'cgst_amount' => 0,
                    'sgst_amount' => 0,
                    'tax_total' => 0,
                    'total' => (float) $locked->amount_paid,
                    'currency' => strtoupper($locked->currency ?? 'INR'),
                ];

            // Prefer amount actually charged if it differs from live plan pricing.
            $total = (float) ($locked->amount_paid ?: $pricing['total']);
            if ($total > 0 && abs($total - (float) $pricing['total']) > 0.05) {
                $base = round($total / (1 + ((float) $pricing['cgst_percent'] + (float) $pricing['sgst_percent']) / 100), 2);
                if ($base <= 0) {
                    $base = $total;
                }
                $cgstAmount = round($base * (float) $pricing['cgst_percent'] / 100, 2);
                $sgstAmount = round($base * (float) $pricing['sgst_percent'] / 100, 2);
                $taxTotal = round($cgstAmount + $sgstAmount, 2);
                $pricing = array_merge($pricing, [
                    'base' => $base,
                    'cgst_amount' => $cgstAmount,
                    'sgst_amount' => $sgstAmount,
                    'tax_total' => $taxTotal,
                    'total' => $total,
                ]);
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
                'cgst_percent'            => $pricing['cgst_percent'],
                'sgst_percent'            => $pricing['sgst_percent'],
                'cgst_amount'             => $pricing['cgst_amount'],
                'sgst_amount'             => $pricing['sgst_amount'],
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
                ],
            ]);
        });
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
