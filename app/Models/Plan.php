<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Plan extends Model
{
    protected $fillable = [
        'name',
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

    public function getNameAttribute($value): ?string
    {
        return $value ?? $this->attributes['plan_name'] ?? null;
    }

    public function getPlanNameAttribute($value): ?string
    {
        return $value ?? $this->attributes['name'] ?? null;
    }

    public function scopeBrief(Builder $query): Builder
    {
        $table = $query->getModel()->getTable();
        $columns = ['id', 'slug'];

        if (Schema::hasColumn($table, 'name')) {
            $columns[] = 'name';
        }

        if (Schema::hasColumn($table, 'plan_name')) {
            $columns[] = 'plan_name';
        }

        return $query->select(array_values(array_unique($columns)));
    }

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
