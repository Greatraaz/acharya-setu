<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'price_monthly',
        'price_yearly',
        'discount_percent',
        'discount_expires_at',
        'currency',
        'cgst_percent',
        'sgst_percent',
        'igst_percent',
        'description',
        'duration',
        'limits',
        'benefits',
        'progress_report_enabled',
        'badge_label',
        'badge_color',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
        'color',
    ];

    protected $casts = [
        'limits'                   => 'array',
        'benefits'                 => 'array',
        'price_monthly'            => 'float',
        'price_yearly'             => 'float',
        'discount_percent'         => 'float',
        'discount_expires_at'      => 'date',
        'cgst_percent'             => 'float',
        'sgst_percent'             => 'float',
        'igst_percent'             => 'float',
        'progress_report_enabled'  => 'boolean',
        'is_active'                => 'boolean',
        'is_featured'              => 'boolean',
        'trial_days'               => 'integer',
        'sort_order'               => 'integer',
    ];

    protected $appends = [
        'formatted_price_monthly',
        'formatted_price_yearly',
        'yearly_savings_percent',
        'sessions_per_month',
    ];

    public function getPlanNameAttribute(): ?string
    {
        return $this->attributes['name'] ?? null;
    }

    public function getSessionsPerMonthAttribute(): ?int
    {
        $limits = $this->limits;
        if (is_string($limits)) {
            $limits = json_decode($limits, true) ?: [];
        }
        if (! is_array($limits) || ! array_key_exists('sessions', $limits) || $limits['sessions'] === '' || $limits['sessions'] === null) {
            return null;
        }

        return (int) $limits['sessions'];
    }

    /**
     * Admin badge tokens mapped to the colors shown on plan cards.
     *
     * @return array<string, array{key: string, bg: string, text: string, solid: string}>
     */
    public static function badgeColorPalettes(): array
    {
        return [
            'blue'   => ['key' => 'blue',   'bg' => '#dbeafe', 'text' => '#1d4ed8', 'solid' => '#2563eb'],
            'green'  => ['key' => 'green',  'bg' => '#dcfce7', 'text' => '#15803d', 'solid' => '#16a34a'],
            'orange' => ['key' => 'orange', 'bg' => '#ffedd5', 'text' => '#c2410c', 'solid' => '#ea580c'],
        ];
    }

    /**
     * Resolved badge colors for the current plan.
     *
     * @return array{key: string, bg: string, text: string, solid: string}
     */
    public function badgePalette(): array
    {
        $raw = strtolower(trim((string) ($this->badge_color ?? '')));
        $map = self::badgeColorPalettes();

        if (isset($map[$raw])) {
            return $map[$raw];
        }

        $hex = $this->normalizedHex($raw) ?? $this->normalizedHex((string) ($this->color ?? ''));
        if ($hex) {
            return ['key' => 'custom', 'bg' => $hex.'22', 'text' => $hex, 'solid' => $hex];
        }

        return $map['blue'];
    }

    private function normalizedHex(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value)) {
            return strtolower($value);
        }

        return null;
    }

    /**
     * Default benefit rows for a new plan. Labels are editable and shown on the frontend.
     *
     * @return array<int, array{label: string, value: string}>
     */
    public static function defaultBenefits(): array
    {
        return [
            ['label' => 'Career counselling', 'value' => ''],
            ['label' => 'In-house mentor allowance', 'value' => ''],
            ['label' => 'Senior marketplace mentor credit', 'value' => ''],
            ['label' => 'Resume development', 'value' => ''],
            ['label' => 'LinkedIn/profile optimisation', 'value' => ''],
            ['label' => 'Mock interview', 'value' => ''],
            ['label' => 'Group clinics/webinars', 'value' => ''],
            ['label' => 'Goal roadmap', 'value' => ''],
            ['label' => 'Priority booking', 'value' => ''],
            ['label' => 'Extra marketplace sessions', 'value' => ''],
            ['label' => 'Progress reports', 'value' => ''],
            ['label' => 'Support', 'value' => ''],
        ];
    }

    /**
     * Example starting catalog — each item is a separate plan row.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function starterCatalog(): array
    {
        return [
            [
                'name'           => 'Essential',
                'slug'           => 'essential',
                'description'    => 'Core mentoring allowance and standard marketplace access.',
                'price_monthly'  => 1499,
                'price_yearly'   => 14990,
                'sort_order'     => 1,
                'badge_label'    => null,
                'badge_color'    => null,
                'is_featured'    => false,
                'color'          => '#64748b',
                'benefits'       => [
                    ['label' => 'Career counselling', 'value' => '30 min / month'],
                    ['label' => 'In-house mentor allowance', 'value' => '60 min / month'],
                    ['label' => 'Senior marketplace mentor credit', 'value' => 'None / optional promo'],
                    ['label' => 'Resume development', 'value' => 'Paid add-on'],
                    ['label' => 'LinkedIn/profile optimisation', 'value' => 'Paid add-on'],
                    ['label' => 'Mock interview', 'value' => 'Paid add-on'],
                    ['label' => 'Group clinics/webinars', 'value' => '2 per month'],
                    ['label' => 'Goal roadmap', 'value' => 'Digital template'],
                    ['label' => 'Priority booking', 'value' => 'Standard'],
                    ['label' => 'Extra marketplace sessions', 'value' => 'Standard public rate'],
                    ['label' => 'Progress reports', 'value' => 'Basic'],
                    ['label' => 'Support', 'value' => 'Standard'],
                ],
            ],
            [
                'name'           => 'Growth',
                'slug'           => 'growth',
                'description'    => 'More in-house time, marketplace credit, and included career services.',
                'price_monthly'  => 2999,
                'price_yearly'   => 29990,
                'sort_order'     => 2,
                'badge_label'    => 'Most Popular',
                'badge_color'    => 'blue',
                'is_featured'    => true,
                'color'          => '#4f46e5',
                'benefits'       => [
                    ['label' => 'Career counselling', 'value' => '60 min / month'],
                    ['label' => 'In-house mentor allowance', 'value' => '120 min / month'],
                    ['label' => 'Senior marketplace mentor credit', 'value' => '₹1,250 value credit / month'],
                    ['label' => 'Resume development', 'value' => '1 complete resume / 6 months'],
                    ['label' => 'LinkedIn/profile optimisation', 'value' => '1 profile review / 6 months'],
                    ['label' => 'Mock interview', 'value' => '1 × 45/60 min per quarter'],
                    ['label' => 'Group clinics/webinars', 'value' => 'Unlimited eligible events'],
                    ['label' => 'Goal roadmap', 'value' => 'Quarterly guided review'],
                    ['label' => 'Priority booking', 'value' => 'Earlier access window, e.g. +24h'],
                    ['label' => 'Extra marketplace sessions', 'value' => 'Configurable member discount, e.g. 5%'],
                    ['label' => 'Progress reports', 'value' => 'Monthly'],
                    ['label' => 'Support', 'value' => 'Priority'],
                ],
            ],
            [
                'name'           => 'Premium',
                'slug'           => 'premium',
                'description'    => 'Highest allowance, concierge support, and priority booking.',
                'price_monthly'  => 5999,
                'price_yearly'   => 59990,
                'sort_order'     => 3,
                'badge_label'    => 'Best Value',
                'badge_color'    => 'orange',
                'is_featured'    => false,
                'color'          => '#d97706',
                'benefits'       => [
                    ['label' => 'Career counselling', 'value' => '60 min / month + priority'],
                    ['label' => 'In-house mentor allowance', 'value' => '180 min / month'],
                    ['label' => 'Senior marketplace mentor credit', 'value' => '₹2,500 value credit / month'],
                    ['label' => 'Resume development', 'value' => '1 complete resume / 3 months'],
                    ['label' => 'LinkedIn/profile optimisation', 'value' => '1 full optimisation / 3 months'],
                    ['label' => 'Mock interview', 'value' => '1 × 60 min per month'],
                    ['label' => 'Group clinics/webinars', 'value' => 'Unlimited + premium masterclasses'],
                    ['label' => 'Goal roadmap', 'value' => 'Monthly guided review'],
                    ['label' => 'Priority booking', 'value' => 'Earlier access window, e.g. +48h'],
                    ['label' => 'Extra marketplace sessions', 'value' => 'Configurable member discount, e.g. 10%'],
                    ['label' => 'Progress reports', 'value' => 'Monthly + quarterly consolidated'],
                    ['label' => 'Support', 'value' => 'Priority / concierge queue'],
                ],
            ],
        ];
    }

    /**
     * @param  array<int|string, mixed>|null  $raw
     * @return array<int, array{label: string, value: string}>
     */
    public static function normalizeBenefits(?array $raw): array
    {
        $raw = is_array($raw) ? $raw : [];

        if ($raw !== [] && ! array_is_list($raw) && isset($raw['career_counselling'])) {
            $raw = self::legacyBenefitsToRows($raw);
        }

        $rows = [];
        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }
            $label = trim((string) ($row['label'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($label === '' && $value === '') {
                continue;
            }
            $rows[] = [
                'label' => mb_substr($label, 0, 120),
                'value' => mb_substr($value, 0, 255),
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $b
     * @return array<int, array{label: string, value: string}>
     */
    private static function legacyBenefitsToRows(array $b): array
    {
        $addonOrEvery = function (array $row): string {
            if (($row['mode'] ?? 'addon') === 'addon') {
                return 'Paid add-on';
            }

            return '1 every '.(int) ($row['every_n_months'] ?? 6).' months';
        };

        $credit = $b['senior_marketplace_credit'] ?? [];
        $creditValue = match ($credit['mode'] ?? 'none') {
            'credit' => '₹'.number_format((float) ($credit['inr_per_month'] ?? 0), 0).' value credit / month',
            'promo'  => 'None / optional promo',
            default  => 'None',
        };

        $mock = $b['mock_interview'] ?? [];
        $mockValue = ($mock['mode'] ?? 'addon') === 'addon'
            ? 'Paid add-on'
            : ((int) ($mock['count'] ?? 1)).' × '.(int) ($mock['duration_minutes'] ?? 45).' min / '.(($mock['period'] ?? '') === 'month' ? 'month' : 'quarter');

        $clinics = $b['group_clinics'] ?? [];
        if (! empty($clinics['unlimited'])) {
            $clinicValue = ! empty($clinics['premium_masterclasses'])
                ? 'Unlimited + premium masterclasses'
                : 'Unlimited eligible events';
        } elseif ((int) ($clinics['count_per_month'] ?? 0) > 0) {
            $clinicValue = (int) $clinics['count_per_month'].' per month';
        } else {
            $clinicValue = 'None';
        }

        $careerMins = (int) ($b['career_counselling']['minutes_per_month'] ?? 0);
        $career = $careerMins > 0
            ? $careerMins.' min / month'.(! empty($b['career_counselling']['priority']) ? ' + priority' : '')
            : 'None';

        $inHouseMins = (int) ($b['in_house_mentor']['minutes_per_month'] ?? 0);
        $booking = $b['priority_booking'] ?? [];
        $market = $b['marketplace_sessions'] ?? [];

        return [
            ['label' => 'Career counselling', 'value' => $career],
            ['label' => 'In-house mentor allowance', 'value' => $inHouseMins > 0 ? $inHouseMins.' min / month' : 'None'],
            ['label' => 'Senior marketplace mentor credit', 'value' => $creditValue],
            ['label' => 'Resume development', 'value' => $addonOrEvery($b['resume_development'] ?? [])],
            ['label' => 'LinkedIn/profile optimisation', 'value' => $addonOrEvery($b['linkedin_optimisation'] ?? [])],
            ['label' => 'Mock interview', 'value' => $mockValue],
            ['label' => 'Group clinics/webinars', 'value' => $clinicValue],
            ['label' => 'Goal roadmap', 'value' => match ($b['goal_roadmap']['cadence'] ?? 'template') {
                'monthly'   => 'Monthly guided review',
                'quarterly' => 'Quarterly guided review',
                'none'      => 'None',
                default     => 'Digital template',
            }],
            ['label' => 'Priority booking', 'value' => ($booking['tier'] ?? 'standard') === 'earlier'
                ? 'Earlier access window, e.g. +'.(int) ($booking['hours'] ?? 0).'h'
                : 'Standard'],
            ['label' => 'Extra marketplace sessions', 'value' => ($market['mode'] ?? 'standard') === 'discount'
                ? 'Configurable member discount, e.g. '.rtrim(rtrim(number_format((float) ($market['discount_percent'] ?? 0), 2, '.', ''), '0'), '.').'%'
                : 'Standard public rate'],
            ['label' => 'Progress reports', 'value' => match ($b['progress_reports']['tier'] ?? 'basic') {
                'monthly_quarterly' => 'Monthly + quarterly consolidated',
                'monthly'           => 'Monthly',
                default             => 'Basic',
            }],
            ['label' => 'Support', 'value' => match ($b['support']['tier'] ?? 'standard') {
                'concierge' => 'Priority / concierge queue',
                'priority'  => 'Priority',
                default     => 'Standard',
            }],
        ];
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function resolvedBenefits(): array
    {
        $rows = self::normalizeBenefits(is_array($this->benefits) ? $this->benefits : []);

        return $rows !== [] ? $rows : self::defaultBenefits();
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function benefitSummary(): array
    {
        return array_values(array_filter(
            $this->resolvedBenefits(),
            fn (array $row) => $row['label'] !== '' || $row['value'] !== ''
        ));
    }

    public function hasActiveDiscount(): bool
    {
        if ((float) ($this->discount_percent ?? 0) <= 0) {
            return false;
        }

        if (! $this->discount_expires_at) {
            return true;
        }

        return now('Asia/Kolkata')->startOfDay()
            ->lte($this->discount_expires_at->copy()->endOfDay());
    }

    /**
     * @return array{percent: float, expires_at: string|null, is_active: bool, label: string}|null
     */
    public function publicDiscount(): ?array
    {
        $percent = round((float) ($this->discount_percent ?? 0), 2);
        if ($percent <= 0) {
            return null;
        }

        $active = $this->hasActiveDiscount();
        $label = rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.').'% off';
        if ($active && $this->discount_expires_at) {
            $label .= ' until '.$this->discount_expires_at->format('d M Y');
        } elseif (! $active) {
            $label .= ' (expired)';
        }

        return [
            'percent'    => $percent,
            'expires_at' => $this->discount_expires_at?->toDateString(),
            'is_active'  => $active,
            'label'      => $label,
        ];
    }

    /**
     * List price, optional % discount, then GST on the discounted base.
     *
     * @return array<string, mixed>
     */
    public function pricingBreakdown(string $billing = 'monthly'): array
    {
        $originalBase = $billing === 'yearly'
            ? (float) ($this->price_yearly ?? 0)
            : (float) ($this->price_monthly ?? 0);
        $originalBase = round($originalBase, 2);

        $discountActive = $this->hasActiveDiscount();
        $discountPercent = $discountActive ? round((float) ($this->discount_percent ?? 0), 2) : 0.0;
        $discountAmount = $discountActive
            ? round($originalBase * $discountPercent / 100, 2)
            : 0.0;
        $base = round(max(0, $originalBase - $discountAmount), 2);

        $cgstPercent = self::filledTaxPercent($this->cgst_percent);
        $sgstPercent = self::filledTaxPercent($this->sgst_percent);
        $igstPercent = self::filledTaxPercent($this->igst_percent);

        $originalTaxes = self::visibleTaxLines($originalBase, $cgstPercent, $sgstPercent, $igstPercent);
        $taxes = self::visibleTaxLines($base, $cgstPercent, $sgstPercent, $igstPercent);
        $originalTotal = round($originalBase + array_sum(array_column($originalTaxes, 'amount')), 2);

        $cgstAmount = $cgstPercent !== null ? round($base * $cgstPercent / 100, 2) : 0.0;
        $sgstAmount = $sgstPercent !== null ? round($base * $sgstPercent / 100, 2) : 0.0;
        $igstAmount = $igstPercent !== null ? round($base * $igstPercent / 100, 2) : 0.0;
        $taxTotal = round($cgstAmount + $sgstAmount + $igstAmount, 2);
        $total = round($base + $taxTotal, 2);

        return [
            'original_base'       => $originalBase,
            'discount_percent'    => $discountPercent,
            'discount_amount'     => $discountAmount,
            'discount_expires_at' => $this->discount_expires_at?->toDateString(),
            'discount_active'     => $discountActive,
            'original_total'      => $originalTotal,
            'base'                => $base,
            'cgst_percent'        => $cgstPercent,
            'sgst_percent'        => $sgstPercent,
            'igst_percent'        => $igstPercent,
            'cgst_amount'         => $cgstAmount,
            'sgst_amount'         => $sgstAmount,
            'igst_amount'         => $igstAmount,
            'tax_total'           => $taxTotal,
            'taxes'               => $taxes,
            'total'               => $total,
            'currency'            => strtoupper($this->currency ?? 'INR'),
            'billing'             => $billing,
        ];
    }

    public static function filledTaxPercent(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }

        $percent = round((float) $value, 2);

        return $percent > 0 ? $percent : null;
    }

    /**
     * Tax rows that were actually filled in admin (skip blank / 0%).
     *
     * @return array<int, array{code: string, percent: float, amount: float}>
     */
    public static function visibleTaxLines(
        float $base,
        mixed $cgstPercent,
        mixed $sgstPercent,
        mixed $igstPercent,
        ?float $cgstAmount = null,
        ?float $sgstAmount = null,
        ?float $igstAmount = null
    ): array {
        $lines = [];
        foreach ([
            ['CGST', $cgstPercent, $cgstAmount],
            ['SGST', $sgstPercent, $sgstAmount],
            ['IGST', $igstPercent, $igstAmount],
        ] as [$code, $percentRaw, $amountRaw]) {
            $percent = self::filledTaxPercent($percentRaw);
            if ($percent === null) {
                continue;
            }
            $lines[] = [
                'code'    => $code,
                'percent' => $percent,
                'amount'  => $amountRaw !== null ? round((float) $amountRaw, 2) : round($base * $percent / 100, 2),
            ];
        }

        return $lines;
    }

    public function billingDays(): int
    {
        return max(1, (int) ($this->duration ?: 30));
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

    public function getFeaturesListAttribute(): array
    {
        return collect($this->benefitSummary())
            ->map(function (array $row) {
                $label = trim((string) ($row['label'] ?? ''));
                $value = trim((string) ($row['value'] ?? ''));
                if ($label !== '' && $value !== '') {
                    return $label.': '.$value;
                }

                return $value !== '' ? $value : $label;
            })
            ->filter()
            ->values()
            ->all();
    }

    /** Public payload for web/API plan cards. */
    public function toPublicArray(?array $checkout = null): array
    {
        $sessions = $this->sessions_per_month;

        $payload = [
            'id'                      => $this->id,
            'name'                    => $this->name,
            'plan_name'               => $this->name,
            'slug'                    => $this->slug,
            'description'             => $this->description,
            'price'                   => (float) ($this->price_monthly ?? 0),
            'price_monthly'           => (float) ($this->price_monthly ?? 0),
            'price_yearly'            => (float) ($this->price_yearly ?? 0),
            'currency'                => $this->currency ?? 'INR',
            'duration'                => $this->billingDays(),
            'discount'                => $this->publicDiscount(),
            'pricing'                 => $this->pricingBreakdown('monthly'),
            'features'                => $this->features_list,
            'sessions_per_month'      => $sessions,
            'progress_report_enabled' => (bool) $this->progress_report_enabled,
            'benefits'                => $this->resolvedBenefits(),
            'benefit_summary'         => $this->benefitSummary(),
            'limits'                  => [
                'sessions' => $sessions,
            ],
            'tax'                     => [
                'cgst_percent' => Plan::filledTaxPercent($this->cgst_percent),
                'sgst_percent' => Plan::filledTaxPercent($this->sgst_percent),
                'igst_percent' => Plan::filledTaxPercent($this->igst_percent),
            ],
            'badge_label'             => $this->badge_label,
            'badge_color'             => $this->badge_color,
            'badge_palette'           => $this->badgePalette(),
            'is_featured'             => (bool) $this->is_featured,
            'color'                   => $this->color,
            'trial_days'              => (int) ($this->trial_days ?? 0),
        ];

        if ($checkout !== null) {
            $payload['checkout'] = [
                'is_upgrade' => (bool) ($checkout['is_upgrade'] ?? false),
                'plan_total' => (float) ($checkout['plan_total'] ?? $payload['pricing']['total']),
                'payable'    => (float) ($checkout['payable'] ?? $payload['pricing']['total']),
                'currency'   => $checkout['currency'] ?? $payload['currency'],
                'credit'     => $checkout['credit'] ?? null,
            ];
        }

        return $payload;
    }

    public function scopeBrief(Builder $query): Builder
    {
        return $query->select(['id', 'slug', 'name']);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
