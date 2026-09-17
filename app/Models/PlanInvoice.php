<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanInvoice extends Model
{
    protected $fillable = [
        'user_subscription_id',
        'user_id',
        'plan_id',
        'invoice_number',
        'invoice_date',
        'billing_name',
        'billing_email',
        'billing_phone',
        'plan_name',
        'base_amount',
        'cgst_percent',
        'sgst_percent',
        'igst_percent',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'tax_total',
        'total_amount',
        'currency',
        'payment_reference',
        'razorpay_order_id',
        'razorpay_payment_id',
        'subscription_starts_at',
        'subscription_expires_at',
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
        'invoice_date'             => 'date',
        'base_amount'              => 'float',
        'cgst_percent'             => 'float',
        'sgst_percent'             => 'float',
        'igst_percent'             => 'float',
        'cgst_amount'              => 'float',
        'sgst_amount'              => 'float',
        'igst_amount'              => 'float',
        'tax_total'                => 'float',
        'total_amount'             => 'float',
        'subscription_starts_at'   => 'datetime',
        'subscription_expires_at'  => 'datetime',
        'meta'                     => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function visibleTaxes(): array
    {
        return Plan::visibleTaxLines(
            (float) $this->base_amount,
            $this->cgst_percent,
            $this->sgst_percent,
            $this->igst_percent,
            $this->cgst_amount,
            $this->sgst_amount,
            $this->igst_amount
        );
    }

    public function toPublicArray(): array
    {
        return [
            'id'                       => $this->id,
            'invoice_number'           => $this->invoice_number,
            'invoice_date'             => $this->invoice_date?->toDateString(),
            'status'                   => $this->status,
            'billing'                  => [
                'name'  => $this->billing_name,
                'email' => $this->billing_email,
                'phone' => $this->billing_phone,
            ],
            'seller'                   => [
                'name'    => $this->seller_name,
                'gstin'   => $this->seller_gstin,
                'address' => $this->seller_address,
                'email'   => $this->seller_email,
                'phone'   => $this->seller_phone,
            ],
            'plan'                     => [
                'id'   => $this->plan_id,
                'name' => $this->plan_name,
            ],
            'pricing'                  => [
                'original_base' => (float) (data_get($this->meta, 'discount.original_base', $this->base_amount)),
                'discount'      => [
                    'applied'    => (bool) data_get($this->meta, 'discount.applied', false),
                    'percent'    => (float) data_get($this->meta, 'discount.percent', 0),
                    'amount'     => (float) data_get($this->meta, 'discount.amount', 0),
                    'expires_at' => data_get($this->meta, 'discount.expires_at'),
                ],
                'base'          => $this->base_amount,
                'cgst_percent'  => Plan::filledTaxPercent($this->cgst_percent),
                'sgst_percent'  => Plan::filledTaxPercent($this->sgst_percent),
                'igst_percent'  => Plan::filledTaxPercent($this->igst_percent),
                'cgst_amount'   => Plan::filledTaxPercent($this->cgst_percent) !== null ? (float) $this->cgst_amount : 0,
                'sgst_amount'   => Plan::filledTaxPercent($this->sgst_percent) !== null ? (float) $this->sgst_amount : 0,
                'igst_amount'   => Plan::filledTaxPercent($this->igst_percent) !== null ? (float) $this->igst_amount : 0,
                'tax_total'     => $this->tax_total,
                'taxes'         => $this->visibleTaxes(),
                'credit'        => [
                    'applied'         => (bool) data_get($this->meta, 'credit.applied', false),
                    'amount'          => (float) data_get($this->meta, 'credit.amount', 0),
                    'remaining_days'  => (int) data_get($this->meta, 'credit.remaining_days', 0),
                    'used_days'       => (int) data_get($this->meta, 'credit.used_days', 0),
                    'from_plan_name'  => data_get($this->meta, 'credit.from_plan_name'),
                ],
                'plan_total'    => (float) data_get($this->meta, 'plan_total', $this->total_amount),
                'total'         => $this->total_amount,
                'currency'      => $this->currency,
            ],
            'payment'                  => [
                'reference'           => $this->payment_reference,
                'razorpay_order_id'   => $this->razorpay_order_id,
                'razorpay_payment_id' => $this->razorpay_payment_id,
            ],
            'subscription'             => [
                'id'         => $this->user_subscription_id,
                'starts_at'  => $this->subscription_starts_at?->toDateTimeString(),
                'expires_at' => $this->subscription_expires_at?->toDateTimeString(),
            ],
            'generated_by'             => $this->generated_by,
            'created_at'               => $this->created_at?->toDateTimeString(),
        ];
    }
}
