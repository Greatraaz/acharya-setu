<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    public const AUDIENCE_NEW_JOINEE = 'new_joinee';

    public const AUDIENCE_SELECTED_MENTEES = 'selected_mentees';

    protected $fillable = [
        'title',
        'audience',
        'amount',
        'coupon_code',
        'usage_limit',
        'usage_count',
        'min_session_amount',
        'starts_at',
        'expires_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'min_session_amount' => 'decimal:2',
        'usage_limit'        => 'integer',
        'usage_count'        => 'integer',
        'is_active'          => 'boolean',
        'starts_at'          => 'date',
        'expires_at'         => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mentees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'offer_mentee', 'offer_id', 'mentee_id')
            ->withTimestamps();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(OfferRedemption::class);
    }

    public function isNewJoineeOffer(): bool
    {
        return $this->audience === self::AUDIENCE_NEW_JOINEE;
    }

    public function isCouponOffer(): bool
    {
        return $this->audience === self::AUDIENCE_SELECTED_MENTEES;
    }

    public function isWithinDates(?\Carbon\Carbon $at = null): bool
    {
        $at = $at ?? now();

        return $at->toDateString() >= $this->starts_at->toDateString()
            && $at->toDateString() <= $this->expires_at->toDateString();
    }

    public function isActiveNow(): bool
    {
        return $this->is_active && $this->isWithinDates();
    }

    public function hasRemainingUses(): bool
    {
        if ($this->usage_limit === null) {
            return true;
        }

        return (int) $this->usage_count < (int) $this->usage_limit;
    }

    public function audienceLabel(): string
    {
        return match ($this->audience) {
            self::AUDIENCE_NEW_JOINEE      => 'New joinee wallet credit',
            self::AUDIENCE_SELECTED_MENTEES => 'Coupon for selected mentees',
            default                        => ucfirst(str_replace('_', ' ', $this->audience)),
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithinDates(Builder $query, ?\Carbon\Carbon $at = null): Builder
    {
        $date = ($at ?? now())->toDateString();

        return $query->whereDate('starts_at', '<=', $date)
            ->whereDate('expires_at', '>=', $date);
    }
}
