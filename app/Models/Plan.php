<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'plan_name',
        'slug',
        'level',
        'price',
        'description',
        'duration',
        'features',
        'status',
    ];

    protected $casts = [
        'features' => 'array',
        'price'    => 'float',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
