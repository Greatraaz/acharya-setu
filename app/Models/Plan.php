<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'plan_name',
        'slug',
        'level',
        'price',
        'price_monthly',
        'price_yearly',
        'currency',
        'description',
        'duration',
        'features',
        'limits',
        'status',
        'badge_label',
        'badge_color',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
        'color',
        'icon',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'razorpay_monthly_plan_id',
        'razorpay_yearly_plan_id',
    ];

    protected $casts = [
        'features'      => 'array',
        'limits'        => 'array',
        'price'         => 'float',
        'price_monthly' => 'float',
        'price_yearly'  => 'float',
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
        'trial_days'    => 'integer',
        'sort_order'    => 'integer',
    ];

    protected $appends = [
        'formatted_price_monthly',
        'formatted_price_yearly',
        'yearly_savings_percent',
    ];

    protected static function booted(): void
    {
        static::saving(function (Plan $plan) {
            // Keep legacy API columns in sync with admin fields.
            if ($plan->isDirty('name') && Schema::hasColumn($plan->getTable(), 'plan_name')) {
                $plan->plan_name = $plan->name;
            }

            if ($plan->isDirty('plan_name') && blank($plan->name) && Schema::hasColumn($plan->getTable(), 'name')) {
                $plan->name = $plan->plan_name;
            }

            if ($plan->isDirty('price_monthly') && Schema::hasColumn($plan->getTable(), 'price')) {
                $plan->price = $plan->price_monthly;
            }

            if ($plan->isDirty('price') && $plan->price_monthly === null && Schema::hasColumn($plan->getTable(), 'price_monthly')) {
                $plan->price_monthly = $plan->price;
            }

            if ($plan->isDirty('is_active') && Schema::hasColumn($plan->getTable(), 'status')) {
                $plan->status = $plan->is_active ? 'active' : 'inactive';
            }

            if ($plan->isDirty('status') && $plan->is_active === null && Schema::hasColumn($plan->getTable(), 'is_active')) {
                $plan->is_active = $plan->status === 'active';
            }
        });
    }

    public function getNameAttribute($value): ?string
    {
        return $value ?? $this->attributes['plan_name'] ?? null;
    }

    public function getPlanNameAttribute($value): ?string
    {
        return $value ?? $this->attributes['name'] ?? null;
    }

    public function getPriceMonthlyAttribute($value): float
    {
        if ($value !== null) {
            return (float) $value;
        }

        return (float) ($this->attributes['price'] ?? 0);
    }

    public function getIsActiveAttribute($value): bool
    {
        if ($value !== null) {
            return (bool) $value;
        }

        return ($this->attributes['status'] ?? null) === 'active';
    }

    public function getFormattedPriceMonthlyAttribute(): string
    {
        $price = (float) $this->price_monthly;
        if ($price <= 0) {
            return 'Free';
        }

        return '₹'.number_format($price, 0);
    }

    public function getFormattedPriceYearlyAttribute(): string
    {
        $price = (float) ($this->attributes['price_yearly'] ?? $this->price_yearly ?? 0);
        if ($price <= 0) {
            return '—';
        }

        return '₹'.number_format($price, 0);
    }

    public function getYearlySavingsPercentAttribute(): int
    {
        $monthly = (float) $this->price_monthly;
        $yearly = (float) ($this->attributes['price_yearly'] ?? 0);

        if ($monthly <= 0 || $yearly <= 0) {
            return 0;
        }

        $full = $monthly * 12;
        if ($full <= $yearly) {
            return 0;
        }

        return (int) round((($full - $yearly) / $full) * 100);
    }

    /** Always return an array for admin views that call count($plan->features_list). */
    public function getFeaturesListAttribute(): array
    {
        $features = $this->features;

        if (is_string($features)) {
            $decoded = json_decode($features, true);
            $features = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode("\n", $features)));
        }

        return is_array($features) ? array_values($features) : [];
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function scopeActive($query)
    {
        $table = $query->getModel()->getTable();

        if (Schema::hasColumn($table, 'is_active')) {
            return $query->where('is_active', true);
        }

        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        $table = $query->getModel()->getTable();

        if (Schema::hasColumn($table, 'sort_order')) {
            return $query->orderBy('sort_order')->orderBy('id');
        }

        return $query->orderBy('id');
    }
}
