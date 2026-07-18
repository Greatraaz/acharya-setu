<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'subscription_id',
        'amount_paid',
        'currency',
        'payment_status',
        'payment_reference',
        'razorpay_order_id',
        'razorpay_payment_id',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
        'amount_paid' => 'float',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        if (
            $this->status !== 'active'
            || $this->payment_status !== 'paid'
            || ! $this->starts_at
            || ! $this->expires_at
        ) {
            return false;
        }

        return Carbon::now()->between($this->starts_at, $this->expires_at);
    }

    public function daysRemaining(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        return (int) Carbon::now()->diffInDays($this->expires_at, false);
    }
}
