<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionInvoice extends Model
{
    protected $fillable = [
        'consultation_session_id',
        'user_id',
        'mentor_id',
        'invoice_number',
        'invoice_date',
        'billing_name',
        'billing_email',
        'billing_phone',
        'description',
        'payment_method',
        'base_amount',
        'wallet_amount',
        'razorpay_amount',
        'total_amount',
        'currency',
        'payment_reference',
        'razorpay_order_id',
        'razorpay_payment_id',
        'booking_ref',
        'session_at',
        'duration_minutes',
        'seller_name',
        'seller_gstin',
        'seller_address',
        'seller_email',
        'seller_phone',
        'status',
        'generated_by',
        'meta',
    ];

    protected $casts = [
        'invoice_date'    => 'date',
        'session_at'      => 'datetime',
        'base_amount'     => 'float',
        'wallet_amount'   => 'float',
        'razorpay_amount' => 'float',
        'total_amount'    => 'float',
        'meta'            => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class, 'consultation_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'plan' => 'Plan (included session)',
            'free' => 'Free',
            'wallet' => 'Wallet',
            'razorpay' => 'Razorpay',
            'hybrid' => 'Wallet + Razorpay',
            default => ucfirst((string) $this->payment_method) ?: '—',
        };
    }

    public function toPublicArray(): array
    {
        return [
            'id'             => $this->id,
            'type'           => 'session',
            'invoice_number' => $this->invoice_number,
            'invoice_date'   => $this->invoice_date?->toDateString(),
            'status'         => $this->status,
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->paymentMethodLabel(),
            'billing'        => [
                'name'  => $this->billing_name,
                'email' => $this->billing_email,
                'phone' => $this->billing_phone,
            ],
            'seller'         => [
                'name'    => $this->seller_name,
                'gstin'   => $this->seller_gstin,
                'address' => $this->seller_address,
                'email'   => $this->seller_email,
                'phone'   => $this->seller_phone,
            ],
            'session'        => [
                'id'               => $this->consultation_session_id,
                'booking_ref'      => $this->booking_ref,
                'description'      => $this->description,
                'session_at'       => $this->session_at?->toDateTimeString(),
                'duration_minutes' => $this->duration_minutes,
                'mentor_id'        => $this->mentor_id,
            ],
            'pricing'        => [
                'base'            => $this->base_amount,
                'wallet_amount'   => $this->wallet_amount,
                'razorpay_amount' => $this->razorpay_amount,
                'total'           => $this->total_amount,
                'currency'        => $this->currency,
            ],
            'payment'        => [
                'reference'           => $this->payment_reference,
                'razorpay_order_id'   => $this->razorpay_order_id,
                'razorpay_payment_id' => $this->razorpay_payment_id,
            ],
            'created_at'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
