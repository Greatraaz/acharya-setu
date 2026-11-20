<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\MockInterviewInvoice;
use App\Models\MockInterviewRequest;
use Illuminate\Support\Facades\DB;

class MockInterviewInvoiceService
{
    public function ensureForRequest(MockInterviewRequest $request, string $generatedBy = 'system'): ?MockInterviewInvoice
    {
        $request->loadMissing(['user', 'invoice']);

        if ($request->invoice) {
            return $request->invoice;
        }

        if (! in_array($request->payment_status, ['paid', 'free'], true)) {
            return null;
        }

        return DB::transaction(function () use ($request, $generatedBy) {
            $locked = MockInterviewRequest::withoutGlobalScopes()
                ->with(['user', 'invoice'])
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
            }

            $paymentRef = $locked->payment_reference
                ?: $locked->razorpay_payment_id
                ?: ($paymentMethod === 'plan' ? 'PLAN-MI-'.$locked->id : null)
                ?: ($paymentMethod === 'wallet' ? 'WAL-MI-'.$locked->id : null);

            return MockInterviewInvoice::create([
                'mock_interview_request_id' => $locked->id,
                'user_id'                   => $locked->user_id,
                'invoice_number'            => $this->nextInvoiceNumber(),
                'invoice_date'              => now()->toDateString(),
                'billing_name'              => $user?->name,
                'billing_email'             => $user?->email,
                'billing_phone'             => $user?->phone,
                'description'               => 'Mock interview — '.$locked->duration_minutes.' minutes',
                'duration_minutes'          => (int) $locked->duration_minutes,
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
                'seller_name'               => $seller['company_name'] ?? 'Vedrix',
                'seller_gstin'              => null,
                'seller_address'            => $seller['address'] ?? null,
                'seller_email'              => $seller['email'] ?? null,
                'seller_phone'              => $seller['phone'] ?? null,
                'status'                    => 'issued',
                'generated_by'              => $generatedBy,
                'meta'                      => [
                    'request_status' => $locked->status,
                    'is_paid_addon'  => (bool) $locked->is_paid_addon,
                    'preferred_at'   => $locked->preferred_at?->toDateTimeString(),
                ],
            ]);
        });
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', (string) AppSetting::get('mock_interview_invoice_prefix', 'MIN')) ?: 'MIN');
        $date = now()->format('ymd');
        $needle = $prefix.'-'.$date.'-';

        $latest = MockInterviewInvoice::where('invoice_number', 'like', $needle.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $needle.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
