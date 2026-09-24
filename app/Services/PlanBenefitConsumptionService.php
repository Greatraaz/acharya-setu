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
            'items'    => [
                $this->careerCounsellingItem($user, $billing),
                $this->careerServiceItem($user, 'resume', $billing),
                $this->careerServiceItem($user, 'linkedin', $billing),
                $this->mockInterviewItem($user, $billing),
            ],
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
            'message'           => $message,
        ];
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
     * Mock interview cadence from plan catalog (usage tracking not live yet).
     *
     * @return array<string, mixed>
     */
    private function mockInterviewItem(User $user, string $billing): array
    {
        $subscription = $user->activeSubscription();
        $slug = $subscription?->plan?->slug;

        // Matches starter catalog marketing rules.
        $cadenceMonths = match ($slug) {
            'premium' => 1,
            'growth'  => 3,
            default   => null,
        };

        $included = $cadenceMonths !== null;
        $label = 'Mock interview';

        if (! $included) {
            return [
                'key'              => 'mock_interview',
                'label'            => $label,
                'included'         => false,
                'tracking_enabled' => false,
                'unit'             => 'sessions',
                'included_limit'   => 0,
                'used'             => 0,
                'remaining'        => 0,
                'unlimited'        => false,
                'payment_required' => true,
                'status'           => 'addon',
                'next_free_at'     => null,
                'cadence_months'   => null,
                'period'           => null,
                'message'          => 'Mock interview is a paid add-on on your current plan.',
            ];
        }

        return [
            'key'              => 'mock_interview',
            'label'            => $label,
            'included'         => true,
            'tracking_enabled' => false,
            'unit'             => 'sessions',
            'included_limit'   => 1,
            'used'             => null,
            'remaining'        => null,
            'unlimited'        => false,
            'payment_required' => false,
            'status'           => 'available',
            'next_free_at'     => null,
            'cadence_months'   => $cadenceMonths,
            'period'           => [
                'label'  => $cadenceMonths === 1 ? 'per_month' : 'per_quarter',
                'months' => $cadenceMonths,
            ],
            'message'          => $cadenceMonths === 1
                ? 'Plan includes 1 free mock interview per month. Usage tracking will appear here once booked.'
                : 'Plan includes 1 free mock interview per quarter. Usage tracking will appear here once booked.',
        ];
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
