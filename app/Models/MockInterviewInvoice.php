<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockInterviewInvoice extends Model
{
    protected $fillable = [
        'mock_interview_request_id',
        'user_id',
        'invoice_number',
        'invoice_date',
        'billing_name',
        'billing_email',
        'billing_phone',
        'description',
        'duration_minutes',
        'payment_method',
        'base_amount',
        'wallet_amount',
        'razorpay_amount',
        'total_amount',
        'currency',
        'payment_reference',
        'razorpay_order_id',
        'razorpay_payment_id',
        'plan_slug',
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
        'invoice_date'     => 'date',
        'base_amount'      => 'float',
        'wallet_amount'    => 'float',
        'razorpay_amount'  => 'float',
        'total_amount'     => 'float',
        'duration_minutes' => 'integer',
        'meta'             => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MockInterviewRequest::class, 'mock_interview_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'plan' => 'Included in plan',
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
            'type'           => 'mock_interview',
            'invoice_number' => $this->invoice_number,
            'invoice_date'   => $this->invoice_date?->toDateString(),
            'status'         => $this->status,
            'payment_method' => $this->payment_method,
            'payment_method_label' => $this->paymentMethodLabel(),
            'duration_minutes' => $this->duration_minutes,
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
