<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;

/**
 * Dashboard-facing plan benefit consumption (used / left / next free).
 */
class PlanBenefitConsumptionService
{
    public function __construct(private CareerServiceService $careerServices)
    {
    }

    /**
     * @return array{
     *   billing: string,
     *   timezone: string,
     *   items: list<array<string, mixed>>
     * }
     */
    public function forUser(User $user): array
    {
        $subscription = $user->activeSubscription();
        $billing = $this->resolveBilling($subscription);

        return [
            'billing'  => $billing,
            'timezone' => 'Asia/Kolkata',
            'items'    => array_map(
                fn (array $item) => $this->withDisplay($item),
                [
                    $this->careerCounsellingItem($user, $billing),
                    $this->inHouseMentorItem($user, $billing),
                    $this->careerServiceItem($user, 'resume', $billing),
                    $this->careerServiceItem($user, 'linkedin', $billing),
                    $this->mockInterviewItem($user, $billing),
                ]
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function careerCounsellingItem(User $user, string $billing): array
    {
        $allowance = $user->planSessionAllowance();
        [$periodStart, $periodEnd] = $user->planUsageWindow();
        $subscription = $user->activeSubscription();
        $subEnd = $subscription?->expires_at?->copy()->timezone('Asia/Kolkata');

        $included = $allowance['minutes_limit'] !== null || (bool) $allowance['unlimited'];
        $unlimited = (bool) $allowance['unlimited'];
        $limit = $unlimited ? null : $allowance['minutes_limit'];
        $used = (int) ($allowance['minutes_used'] ?? 0);
        $remaining = $unlimited ? null : ($allowance['minutes_remaining'] ?? 0);

        $paymentRequired = $included && ! $unlimited && (int) $remaining <= 0;
        $nextFreeAt = null;

        if ($paymentRequired && $periodEnd && $subEnd && $periodEnd->lt($subEnd)) {
            // Yearly (or multi-month) plan: next monthly bucket still inside the subscription.
            $nextFreeAt = $periodEnd->copy();
        }

        $status = $this->status(
            included: $included,
            remaining: $unlimited ? 1 : (int) $remaining,
            nextFreeAt: $nextFreeAt,
            billing: $billing,
            unlimited: $unlimited,
        );

        $message = match ($status) {
            'addon' => 'Career counselling is not included on your current plan. Book as a paid session.',
            'available' => $unlimited
                ? 'Unlimited free career counselling minutes on your plan.'
                : "You have {$remaining} of {$limit} free counselling minutes left this period.",
            'next_free' => 'You\'ve used this period\'s free counselling minutes. Next free benefit on '
                .$nextFreeAt->timezone('Asia/Kolkata')->format('d M Y').'. Extra sessions require payment.',
            'used' => 'You\'ve used your free counselling minutes for this plan period. Extra sessions require payment.',
            default => 'Career counselling entitlement unavailable.',
        };

        return [
            'key'               => 'career_counselling',
            'label'             => 'Career counselling',
            'included'          => $included,
            'tracking_enabled'  => true,
            'unit'              => 'minutes',
            'included_limit'    => $unlimited ? null : $limit,
            'used'              => $used,
            'remaining'         => $remaining,
            'unlimited'         => $unlimited,
            'payment_required'  => $paymentRequired,
            'status'            => $status,
            'next_free_at'      => $nextFreeAt?->toDateTimeString(),
            'period'            => [
                'label'     => 'monthly_bucket',
                'starts_at' => $periodStart->toDateTimeString(),
                'ends_at'   => $periodEnd->toDateTimeString(),
            ],
            'history'           => app(CounsellingBenefitService::class)->history($user, 'counselling'),
            'message'           => $message,
        ];
    }

    /**
     * In-house mentor minutes from the plan benefit, minus plan-covered time
     * already booked with the mentee's assigned mentor this period.
     *
     * @return array<string, mixed>
     */
    private function inHouseMentorItem(User $user, string $billing): array
    {
        $subscription = $user->activeSubscription();
        $plan = $subscription?->plan;
        $allowance = $plan?->allowanceMinutes('in_house_mentor', $billing);
        [$periodStart, $periodEnd] = $user->planUsageWindow();
        $subEnd = $subscription?->expires_at?->copy()->timezone('Asia/Kolkata');

        $included = $allowance !== null;
        $unlimited = $allowance !== null && $allowance < 0;
        $limit = $unlimited ? null : $allowance;
        $used = $included ? $this->inHouseMinutesUsed($user) : 0;
        $remaining = $unlimited ? null : max(0, (int) $limit - $used);
        $paymentRequired = $included && ! $unlimited && (int) $remaining <= 0;
        $nextFreeAt = null;

        if ($paymentRequired && $periodEnd && $subEnd && $periodEnd->lt($subEnd)) {
            $nextFreeAt = $periodEnd->copy();
        }

        $status = $this->status(
            included: $included,
            remaining: $unlimited ? 1 : (int) $remaining,
            nextFreeAt: $nextFreeAt,
            billing: $billing,
            unlimited: $unlimited,
        );

        $left = $unlimited ? 'unlimited' : $this->formatDuration((int) $remaining);
        $cap = $unlimited ? null : $this->formatDuration((int) $limit);

        $message = match ($status) {
            'addon' => 'In-house mentor time is not included on your current plan.',
            'available' => $unlimited
                ? 'Unlimited free in-house mentor time on your plan.'
                : "You have {$left} of {$cap} free in-house mentor time left this period.",
            'next_free' => 'You\'ve used this period\'s free in-house mentor time. Next free benefit on '
                .$nextFreeAt->timezone('Asia/Kolkata')->format('d M Y').'. Extra sessions require payment.',
            'used' => 'You\'ve used your free in-house mentor time for this plan period. Extra sessions require payment.',
            default => 'In-house mentor entitlement unavailable.',
        };

        return [
            'key'               => 'in_house_mentor',
            'label'             => 'In-house mentor',
            'included'          => $included,
            'tracking_enabled'  => true,
            'unit'              => 'minutes',
            'included_limit'    => $unlimited ? null : $limit,
            'used'              => $used,
            'remaining'         => $remaining,
            'unlimited'         => $unlimited,
            'payment_required'  => $paymentRequired,
            'status'            => $status,
            'next_free_at'      => $nextFreeAt?->toDateTimeString(),
            'period'            => [
                'label'     => 'monthly_bucket',
                'starts_at' => $periodStart->toDateTimeString(),
                'ends_at'   => $periodEnd->toDateTimeString(),
            ],
            'history'           => app(CounsellingBenefitService::class)->history($user, 'in_house'),
            'message'           => $message,
        ];
    }

    private function inHouseMinutesUsed(User $user): int
    {
        if (! $user->assigned_mentor_id) {
            return 0;
        }

        return app(CounsellingBenefitService::class)->countedMinutes($user, (int) $user->assigned_mentor_id);
    }

    /**
     * @return array<string, mixed>
     */
    private function careerServiceItem(User $user, string $type, string $billing): array
    {
        $quote = $this->careerServices->quote($user, $type);
        $ent = $quote['entitlement'] ?? [];

        $included = (bool) ($ent['included'] ?? false);
        $limit = $included ? 1 : 0;
        $used = (int) ($ent['used'] ?? 0);
        $remaining = (int) ($ent['remaining'] ?? 0);
        $months = $ent['months'] ?? null;
        $nextFreeAt = isset($ent['next_free_at']) && $ent['next_free_at']
            ? Carbon::parse($ent['next_free_at'])->timezone('Asia/Kolkata')
            : null;
        $paymentRequired = (bool) ($quote['is_free'] ?? false) === false;

        $status = $this->status(
            included: $included,
            remaining: $remaining,
            nextFreeAt: $nextFreeAt,
            billing: $billing,
            unlimited: false,
        );

        $label = $type === 'linkedin' ? 'LinkedIn/profile optimisation' : 'Resume development';

        $message = $ent['label'] ?? match ($status) {
            'addon' => "{$label} is a paid add-on on your current plan.",
            'available' => "You have {$remaining} free {$label} credit left.",
            'next_free' => "You've used your free {$label}. Next free benefit on "
                .($nextFreeAt?->format('d M Y') ?? '—').'. Extra requests require payment.',
            'used' => "You've used your free {$label} for this window. Extra requests require payment.",
            default => $label,
        };

        return [
            'key'               => $type,
            'label'             => $label,
            'included'          => $included,
            'tracking_enabled'  => true,
            'unit'              => 'credits',
            'included_limit'    => $included ? $limit : 0,
            'used'              => $used,
            'remaining'         => $remaining,
            'unlimited'         => false,
            'payment_required'  => $paymentRequired,
            'status'            => $status,
            'next_free_at'      => $nextFreeAt?->toDateTimeString(),
            'cadence_months'    => $months,
            'period'            => [
                'label'     => 'rolling_window',
                'months'    => $months,
                'starts_at' => $ent['window_starts_at'] ?? null,
                'ends_at'   => $nextFreeAt?->toDateTimeString(),
            ],
            'amount'            => (float) ($quote['amount'] ?? 0),
            'is_free'           => (bool) ($quote['is_free'] ?? false),
            'message'           => $message,
        ];
    }

    /**
     * Mock interview entitlement + consumption from MockInterviewService.
     *
     * @return array<string, mixed>
     */
    private function mockInterviewItem(User $user, string $billing): array
    {
        $quote = app(MockInterviewService::class)->quote($user, 60);
        $ent = $quote['entitlement'] ?? [];

        $included = (bool) ($ent['included'] ?? false);
        $used = (int) ($ent['used'] ?? 0);
        $remaining = (int) ($ent['remaining'] ?? 0);
        $months = $ent['months'] ?? null;
        $nextFreeAt = isset($ent['next_free_at']) && $ent['next_free_at']
            ? Carbon::parse($ent['next_free_at'])->timezone('Asia/Kolkata')
            : null;
        $paymentRequired = ! (bool) ($quote['is_free'] ?? false);

        $status = $this->status(
            included: $included,
            remaining: $remaining,
            nextFreeAt: $nextFreeAt,
            billing: $billing,
            unlimited: false,
        );

        $label = 'Mock interview';
        $message = $ent['label'] ?? match ($status) {
            'addon' => 'Mock interview is a paid add-on on your current plan.',
            'available' => "You have {$remaining} free mock interview left.",
            'next_free' => "You've used your free mock interview. Next free on "
                .($nextFreeAt?->format('d M Y') ?? '—').'. Extra bookings require payment.',
            'used' => "You've used your free mock interview for this window. Extra bookings require payment.",
            default => $label,
        };

        return [
            'key'               => 'mock_interview',
            'label'             => $label,
            'included'          => $included,
            'tracking_enabled'  => true,
            'unit'              => 'sessions',
            'included_limit'    => $included ? 1 : 0,
            'used'              => $used,
            'remaining'         => $remaining,
            'unlimited'         => false,
            'payment_required'  => $paymentRequired,
            'status'            => $status,
            'next_free_at'      => $remaining > 0 ? null : $nextFreeAt?->toDateTimeString(),
            'cadence_months'    => $months,
            'rate_per_minute'   => (float) ($quote['rate_per_minute'] ?? 0),
            'amount_sample_60'  => (float) ($quote['amount'] ?? 0),
            'is_free'           => (bool) ($quote['is_free'] ?? false),
            'period'            => [
                'label'     => $months === 1 ? 'per_month' : ($months === 3 ? 'per_quarter' : 'rolling_window'),
                'months'    => $months,
                'starts_at' => $ent['window_starts_at'] ?? null,
                'ends_at'   => $nextFreeAt?->toDateTimeString(),
            ],
            'message'           => $message,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function withDisplay(array $item): array
    {
        $status = (string) ($item['status'] ?? 'addon');
        $item['status_label'] = match ($status) {
            'available' => 'Available',
            'next_free' => 'Next free',
            'used'      => 'Used up',
            default     => 'Not included',
        };

        if (! ($item['included'] ?? false)) {
            $item['left_label'] = 'Not included';

            return $item;
        }

        if ($item['unlimited'] ?? false) {
            $item['left_label'] = 'Unlimited';

            return $item;
        }

        $remaining = (int) ($item['remaining'] ?? 0);
        $limit = (int) ($item['included_limit'] ?? 0);
        $unit = (string) ($item['unit'] ?? '');

        if ($unit === 'minutes') {
            $item['left_label'] = $this->formatDuration($remaining).' left of '.$this->formatDuration($limit);

            return $item;
        }

        $noun = match ($item['key'] ?? '') {
            'resume' => 'resume',
            'linkedin' => 'profile review',
            'mock_interview' => 'mock interview',
            default => 'credit',
        };
        $plural = $limit === 1 ? $noun : $noun.'s';
        $item['left_label'] = $remaining.' of '.$limit.' free '.$plural.' left';

        return $item;
    }

    private function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 min';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours === 0) {
            return $mins.' min';
        }

        $hourLabel = $hours === 1 ? '1 hr' : $hours.' hrs';
        if ($mins === 0) {
            return $hourLabel.' ('.$minutes.' min)';
        }

        return $hourLabel.' '.$mins.' min ('.$minutes.' min)';
    }

    private function status(
        bool $included,
        int $remaining,
        ?Carbon $nextFreeAt,
        string $billing,
        bool $unlimited = false,
    ): string {
        if (! $included) {
            return 'addon';
        }
        if ($unlimited || $remaining > 0) {
            return 'available';
        }
        if ($nextFreeAt !== null) {
            return 'next_free';
        }

        // Monthly package (or last bucket of yearly): no further free period inside this sub.
        return 'used';
    }

    private function resolveBilling($subscription): string
    {
        if (! $subscription) {
            return 'monthly';
        }

        $meta = is_array($subscription->meta) ? $subscription->meta : [];
        $billing = data_get($meta, 'checkout.billing')
            ?? data_get($meta, 'billing')
            ?? null;

        return Plan::normalizeBilling($billing);
    }
}
