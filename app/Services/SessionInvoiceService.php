<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ConsultationSession;
use App\Models\SessionInvoice;
use Illuminate\Support\Facades\DB;

class SessionInvoiceService
{
    public function ensureForSession(ConsultationSession $session, string $generatedBy = 'system'): ?SessionInvoice
    {
        $session->loadMissing(['mentee', 'mentor', 'sessionInvoice']);

        if ($session->sessionInvoice) {
            return $session->sessionInvoice;
        }

        // Only issue for confirmed paid/waived bookings
        if (! in_array($session->payment_status, ['paid', 'waived'], true)) {
            return null;
        }

        return DB::transaction(function () use ($session, $generatedBy) {
            $locked = ConsultationSession::with(['mentee', 'mentor', 'sessionInvoice'])
                ->lockForUpdate()
                ->findOrFail($session->id);

            if ($locked->sessionInvoice) {
                return $locked->sessionInvoice;
            }

            $seller = AppSetting::billing();
            $user = $locked->mentee;
            $total = (float) $locked->amount;
            $wallet = (float) ($locked->wallet_amount ?? 0);
            $razor = (float) ($locked->razorpay_amount ?? 0);

            if ($locked->payment_method === 'wallet') {
                $wallet = $total;
                $razor = 0;
            } elseif ($locked->payment_method === 'razorpay') {
                $razor = $total;
                $wallet = 0;
            } elseif ($locked->payment_method === 'plan' || $locked->payment_method === 'free') {
                $wallet = 0;
                $razor = 0;
                $total = 0;
            }

            return SessionInvoice::create([
                'consultation_session_id' => $locked->id,
                'user_id'                 => $locked->mentee_id,
                'mentor_id'               => $locked->mentor_id,
                'invoice_number'          => $this->nextInvoiceNumber(),
                'invoice_date'            => now()->toDateString(),
                'billing_name'            => $user?->name,
                'billing_email'           => $user?->email,
                'billing_phone'           => $user?->phone,
                'description'             => 'Mentorship session with '.($locked->mentor?->name ?? 'mentor'),
                'payment_method'          => $locked->payment_method,
                'base_amount'             => $total,
                'wallet_amount'           => $wallet,
                'razorpay_amount'         => $razor,
                'total_amount'            => $total,
                'currency'                => strtoupper($locked->currency ?? 'INR'),
                'payment_reference'       => $locked->payment_reference,
                'razorpay_order_id'       => $locked->razorpay_order_id,
                'razorpay_payment_id'     => $locked->razorpay_payment_id,
                'booking_ref'             => $locked->booking_ref,
                'session_at'              => $locked->scheduled_at,
                'duration_minutes'        => $locked->duration_minutes,
                'seller_name'             => $seller['company_name'],
                'seller_gstin'            => $seller['gstin'],
                'seller_address'          => $seller['address'],
                'seller_email'            => $seller['email'],
                'seller_phone'            => $seller['phone'],
                'status'                  => 'issued',
                'generated_by'            => $generatedBy,
                'meta'                    => [
                    'title' => $locked->title,
                ],
            ]);
        });
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', (string) AppSetting::get('session_invoice_prefix', 'SIN')) ?: 'SIN');
        $period = now()->format('Ym');
        $needle = $prefix.'-'.$period.'-';

        $latest = SessionInvoice::where('invoice_number', 'like', $needle.'%')
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
