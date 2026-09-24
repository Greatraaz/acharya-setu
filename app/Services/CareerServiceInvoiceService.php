<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\CareerServiceInvoice;
use App\Models\CareerServiceRequest;
use Illuminate\Support\Facades\DB;

class CareerServiceInvoiceService
{
    /**
     * Create an invoice for a paid or free (plan-included) career service request.
     */
    public function ensureForRequest(CareerServiceRequest $request, string $generatedBy = 'system'): ?CareerServiceInvoice
    {
        $request->loadMissing(['user', 'invoice']);

        if ($request->invoice) {
            return $request->invoice;
        }

        if (! in_array($request->payment_status, ['paid', 'free'], true)) {
            return null;
        }

        return DB::transaction(function () use ($request, $generatedBy) {
            $locked = CareerServiceRequest::with(['user', 'invoice'])
                ->lockForUpdate()
                ->findOrFail($request->id);

            if ($locked->invoice) {
                return $locked->invoice;
            }

            if (! in_array($locked->payment_status, ['paid', 'free'], true)) {
                return null;
            }

            $seller = AppSetting::billing();
            $user = $locked->user;
            $total = round((float) $locked->amount, 2);
            $walletAmount = round((float) ($locked->wallet_amount ?? 0), 2);
            $razorAmount = round((float) ($locked->razorpay_amount ?? 0), 2);

            $paymentMethod = $locked->payment_status === 'free'
                ? 'plan'
                : (string) ($locked->payment_method ?: 'razorpay');

            if ($paymentMethod === 'plan') {
                $total = 0;
                $walletAmount = 0;
                $razorAmount = 0;
            } elseif ($paymentMethod === 'wallet') {
                $walletAmount = $total;
                $razorAmount = 0;
            } elseif ($paymentMethod === 'razorpay') {
                $razorAmount = $total;
                $walletAmount = 0;
            } elseif ($paymentMethod === 'hybrid') {
                if ($walletAmount <= 0 && $razorAmount <= 0) {
                    $razorAmount = $total;
                }
            }

            $paymentRef = $locked->payment_reference
                ?: $locked->razorpay_payment_id
                ?: ($paymentMethod === 'plan' ? 'PLAN-CS-'.$locked->id : null)
                ?: ($paymentMethod === 'wallet' ? 'WAL-CS-'.$locked->id : null);

            return CareerServiceInvoice::create([
                'career_service_request_id' => $locked->id,
                'user_id'                   => $locked->user_id,
                'invoice_number'            => $this->nextInvoiceNumber(),
                'invoice_date'              => now()->toDateString(),
                'billing_name'              => $user?->name,
                'billing_email'             => $user?->email,
                'billing_phone'             => $user?->phone,
                'service_type'              => $locked->type,
                'description'               => $locked->typeLabel().' — career service',
                'payment_method'            => $paymentMethod,
                'base_amount'               => $total,
                'wallet_amount'             => $walletAmount,
                'razorpay_amount'           => $razorAmount,
                'total_amount'              => $total,
                'currency'                  => strtoupper($locked->currency ?: 'INR'),
                'payment_reference'         => $paymentRef,
                'razorpay_order_id'         => $locked->razorpay_order_id,
                'razorpay_payment_id'       => $locked->razorpay_payment_id,
                'plan_slug'                 => $locked->plan_slug,
                'seller_name'               => $seller['company_name'],
                'seller_gstin'              => null,
                'seller_address'            => $seller['address'],
                'seller_email'              => $seller['email'],
                'seller_phone'              => $seller['phone'],
                'status'                    => 'issued',
                'generated_by'              => $generatedBy,
                'meta'                      => [
                    'request_status' => $locked->status,
                    'is_paid_addon'  => (bool) $locked->is_paid_addon,
                    'tax_applicable' => false,
                ],
            ]);
        });
    }

    /**
     * Backfill invoices for existing paid/free requests that have none yet.
     */
    public function backfillMissing(string $generatedBy = 'system'): int
    {
        $count = 0;

        CareerServiceRequest::query()
            ->whereIn('payment_status', ['paid', 'free'])
            ->whereDoesntHave('invoice')
            ->orderBy('id')
            ->chunkById(50, function ($rows) use (&$count, $generatedBy) {
                foreach ($rows as $request) {
                    if ($this->ensureForRequest($request, $generatedBy)) {
                        $count++;
                    }
                }
            });

        return $count;
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', (string) AppSetting::get('career_invoice_prefix', 'CIN')) ?: 'CIN');
        $period = now()->format('Ym');
        $needle = $prefix.'-'.$period.'-';

        $latest = CareerServiceInvoice::where('invoice_number', 'like', $needle.'%')
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
