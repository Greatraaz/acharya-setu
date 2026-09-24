<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerServiceInvoice extends Model
{
    protected $fillable = [
        'career_service_request_id',
        'user_id',
        'invoice_number',
        'invoice_date',
        'billing_name',
        'billing_email',
        'billing_phone',
        'service_type',
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
        'invoice_date'    => 'date',
        'base_amount'     => 'float',
        'wallet_amount'   => 'float',
        'razorpay_amount' => 'float',
        'total_amount'    => 'float',
        'meta'            => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(CareerServiceRequest::class, 'career_service_request_id');
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

    public function serviceLabel(): string
    {
        return $this->service_type === CareerServiceRequest::TYPE_LINKEDIN
            ? 'LinkedIn optimisation'
            : 'Resume development';
    }

    public function toPublicArray(): array
    {
        return [
            'id'             => $this->id,
            'type'           => 'career_service',
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
                'address' => $this->seller_address,
                'email'   => $this->seller_email,
                'phone'   => $this->seller_phone,
            ],
            'service'        => [
                'request_id' => $this->career_service_request_id,
                'type'       => $this->service_type,
                'label'      => $this->serviceLabel(),
                'description'=> $this->description,
                'plan_slug'  => $this->plan_slug,
            ],
            'pricing'        => [
                'base'            => $this->base_amount,
                'wallet_amount'   => $this->wallet_amount,
                'razorpay_amount' => $this->razorpay_amount,
                'total'           => $this->total_amount,
                'currency'        => $this->currency,
                'tax_applicable'  => false,
                'tax_total'       => 0,
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
