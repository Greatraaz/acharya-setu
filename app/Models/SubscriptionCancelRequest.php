<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionCancelRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const APPROVED_BY_ADMIN = 'admin';
    public const APPROVED_BY_AUTO  = 'auto';

    protected $fillable = [
        'user_id',
        'user_subscription_id',
        'status',
        'reason',
        'admin_note',
        'requested_at',
        'auto_approve_at',
        'reviewed_at',
        'reviewed_by',
        'approved_by',
        'consumed_months',
        'refund_months',
        'paid_amount',
        'monthly_amount',
        'consumed_amount',
        'refund_amount',
        'refund_destination',
        'razorpay_refunded',
        'wallet_refunded',
        'razorpay_refund_id',
        'snapshot',
        'processed_at',
    ];

    protected $casts = [
        'requested_at'      => 'datetime',
        'auto_approve_at'   => 'datetime',
        'reviewed_at'       => 'datetime',
        'processed_at'      => 'datetime',
        'consumed_months'   => 'integer',
        'refund_months'     => 'integer',
        'paid_amount'       => 'float',
        'monthly_amount'    => 'float',
        'consumed_amount'   => 'float',
        'refund_amount'     => 'float',
        'razorpay_refunded' => 'float',
        'wallet_refunded'   => 'float',
        'snapshot'          => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'user_subscription_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function hoursUntilAutoApprove(): int
    {
        if (! $this->isPending() || ! $this->auto_approve_at) {
            return 0;
        }

        $seconds = $this->auto_approve_at->getTimestamp() - now()->getTimestamp();

        return (int) max(0, (int) ceil($seconds / 3600));
    }
}
