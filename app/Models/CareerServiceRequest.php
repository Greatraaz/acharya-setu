<?php

namespace App\Models;

use App\Services\PublicFileStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CareerServiceRequest extends Model
{
    public const TYPE_RESUME = 'resume';

    public const TYPE_LINKEDIN = 'linkedin';

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'linkedin_url',
        'resume_path',
        'mentee_notes',
        'is_paid_addon',
        'amount',
        'currency',
        'payment_status',
        'payment_method',
        'wallet_amount',
        'razorpay_amount',
        'razorpay_order_id',
        'razorpay_payment_id',
        'payment_reference',
        'plan_slug',
        'admin_notes',
        'deliverable_path',
        'reviewed_by',
        'reviewed_at',
        'completed_at',
    ];

    protected $casts = [
        'is_paid_addon'   => 'boolean',
        'amount'          => 'decimal:2',
        'wallet_amount'   => 'decimal:2',
        'razorpay_amount' => 'decimal:2',
        'reviewed_at'     => 'datetime',
        'completed_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(CareerServiceInvoice::class, 'career_service_request_id');
    }

    public function resumeUrl(): ?string
    {
        return PublicFileStorage::url($this->resume_path);
    }

    public function deliverableUrl(): ?string
    {
        return PublicFileStorage::url($this->deliverable_path);
    }

    public function typeLabel(): string
    {
        return $this->type === self::TYPE_LINKEDIN ? 'LinkedIn optimisation' : 'Resume development';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_PAYMENT => 'Awaiting payment',
            self::STATUS_SUBMITTED       => 'Under review',
            self::STATUS_COMPLETED       => 'Completed',
            self::STATUS_CANCELLED       => 'Cancelled',
            default                      => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function toPublicArray(): array
    {
        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'type_label'      => $this->typeLabel(),
            'status'          => $this->status,
            'status_label'    => $this->statusLabel(),
            'linkedin_url'    => $this->linkedin_url,
            'resume_url'      => $this->resumeUrl(),
            'mentee_notes'    => $this->mentee_notes,
            'is_paid_addon'   => (bool) $this->is_paid_addon,
            'amount'          => (float) $this->amount,
            'currency'        => $this->currency,
            'payment_status'  => $this->payment_status,
            'payment_method'  => $this->payment_method,
            'wallet_amount'   => (float) ($this->wallet_amount ?? 0),
            'razorpay_amount' => (float) ($this->razorpay_amount ?? 0),
            'payment_reference' => $this->payment_reference,
            'plan_slug'       => $this->plan_slug,
            'admin_notes'     => $this->admin_notes,
            'deliverable_url' => $this->deliverableUrl(),
            'reviewed_at'     => $this->reviewed_at?->toDateTimeString(),
            'completed_at'    => $this->completed_at?->toDateTimeString(),
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
            'invoice'         => $this->relationLoaded('invoice') && $this->invoice
                ? $this->invoice->toPublicArray()
                : null,
        ];
    }
}
