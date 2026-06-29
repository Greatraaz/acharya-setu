<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
 
class Plan extends Model
{
    use SoftDeletes;
 
    protected $fillable = [
        'name', 'slug', 'description', 'badge_label', 'badge_color',
        'price_monthly', 'price_yearly', 'currency', 'trial_days',
        'features', 'limits', 'is_active', 'is_featured', 'sort_order',
        'stripe_monthly_price_id', 'stripe_yearly_price_id',
        'razorpay_monthly_plan_id', 'razorpay_yearly_plan_id',
        'color', 'icon',
    ];
 
    protected $casts = [
        'features'    => 'array',
        'limits'      => 'array',
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly'  => 'decimal:2',
    ];
 
    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }
 
    // ── Scopes ────────────────────────────────────────────────
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
 
    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('price_monthly');
    }
 
    // ── Accessors ─────────────────────────────────────────────
    public function getFormattedPriceMonthlyAttribute(): string
    {
        return $this->price_monthly == 0
            ? 'Free'
            : config_val('currency_symbol', '₹') . number_format($this->price_monthly, 0);
    }
 
    public function getFormattedPriceYearlyAttribute(): string
    {
        return $this->price_yearly == 0
            ? 'Free'
            : config_val('currency_symbol', '₹') . number_format($this->price_yearly, 0);
    }
 
    public function getYearlySavingsPercentAttribute(): int
    {
        if ($this->price_monthly == 0 || $this->price_yearly == 0) return 0;
        $annualMonthly = $this->price_monthly * 12;
        return (int) round(($annualMonthly - $this->price_yearly) / $annualMonthly * 100);
    }
 
    public function getFeaturesListAttribute(): array
    {
        return $this->features ?? [];
    }
}