<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Plan;
use App\Models\User;
use App\Services\CareerServiceService;
use App\Services\OfferService;
use App\Services\PlanBenefitConsumptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    private const USER_FIELDS = [
        'id', 'name', 'email', 'role', 'bio', 'expertise',
        'field', 'college', 'year', 'company', 'designation',
        'experience_years', 'rating', 'total_sessions', 'avatar_url',
        'gender', 'phone', 'linkedin', 'onboarding_completed',
        'onboarding_step', 'mentor_status', 'subscription_plan',
        'education_stream', 'career_goals', 'strengths',
        'preferences', 'is_active', 'isVerifiedEmail', 'created_at',
        'assigned_mentor_id',
    ];

    public function show(
        Request $request,
        CareerServiceService $careerServices,
        OfferService $offers,
        PlanBenefitConsumptionService $benefits,
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing(['assignedMentor:id,name,avatar_url,email']);

        $subscription = $user->activeSubscription();
        $subscription?->loadMissing('plan');
        $plan = $subscription?->plan;
        $billing = $this->resolveBilling($subscription);
        [$periodStart, $periodEnd] = $user->planUsageWindow();
        $wallet = $user->walletSummary();
        $app = AppSetting::app();
        $razorpay = AppSetting::razorpay();
        $careerAddons = AppSetting::careerAddons();
        $benefitConsumption = $benefits->forUser($user);

        $couponsAvailable = $offers->availableCouponsFor($user)->count();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Mentee config fetched successfully.',
            'data'       => [
                'user' => array_merge($user->only(self::USER_FIELDS), [
                    'wallet_balance' => (float) $user->wallet_balance,
                ]),

                'wallet' => [
                    'balance'         => (float) ($wallet['balance'] ?? 0),
                    'currency'        => $app['currency'] ?? 'INR',
                    'currency_symbol' => $app['currency_symbol'] ?? '₹',
                    'total_credited'  => (float) ($wallet['total_credited'] ?? 0),
                    'total_debited'   => (float) ($wallet['total_debited'] ?? 0),
                    'total_refunded'  => (float) ($wallet['total_refunded'] ?? 0),
                ],

                'subscription' => $this->subscriptionPayload($subscription, $plan, $billing),

                'entitlements' => [
                    'can_access_progress_report' => $user->canAccessProgressReport(),
                    'period'                     => [
                        'label'     => 'monthly_bucket',
                        'timezone'  => 'Asia/Kolkata',
                        'starts_at' => $periodStart->toDateTimeString(),
                        'ends_at'   => $periodEnd->toDateTimeString(),
                    ],
                ],

                // Dashboard: used / remaining / next_free for counselling, resume, LinkedIn, mock.
                'benefits' => $benefitConsumption,

                'career_services' => [
                    'prices'   => $careerServices->prices(),
                    'resume'   => $careerServices->quote($user, 'resume'),
                    'linkedin' => $careerServices->quote($user, 'linkedin'),
                ],

                'mentor' => $user->assignedMentor ? [
                    'id'         => $user->assignedMentor->id,
                    'name'       => $user->assignedMentor->name,
                    'email'      => $user->assignedMentor->email,
                    'avatar_url' => $user->assignedMentor->avatar_url,
                ] : null,

                'app' => [
                    'name'             => $app['name'] ?? 'Vedrix',
                    'currency'         => $app['currency'] ?? 'INR',
                    'currency_symbol'  => $app['currency_symbol'] ?? '₹',
                    'timezone'         => $app['timezone'] ?? 'Asia/Kolkata',
                    'date_format'      => $app['date_format'] ?? 'd/m/Y',
                    'maintenance_mode' => (bool) ($app['maintenance_mode'] ?? false),
                    'razorpay'         => [
                        'enabled' => (bool) ($razorpay['enabled'] ?? false),
                        'mode'    => $razorpay['mode'] ?? 'test',
                        'key'     => $razorpay['key'] ?? null,
                    ],
                    'career_addons'    => $careerAddons,
                ],

                'flags' => [
                    'onboarding_required'  => ! (bool) $user->onboarding_completed,
                    'has_active_plan'      => (bool) $subscription,
                    'has_assigned_mentor'  => (bool) $user->assigned_mentor_id,
                    'can_book_with_wallet' => (float) $user->wallet_balance > 0,
                ],

                'counts' => [
                    'coupons_available' => (int) $couponsAvailable,
                ],
            ],
        ]);
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

    private function subscriptionPayload($subscription, $plan, string $billing): ?array
    {
        if (! $subscription) {
            return null;
        }

        return [
            'has_active'      => true,
            'id'              => (int) $subscription->id,
            'subscription_id' => $subscription->subscription_id,
            'status'          => $subscription->status,
            'payment_status'  => $subscription->payment_status,
            'billing'         => $billing,
            'amount_paid'     => (float) $subscription->amount_paid,
            'currency'        => $subscription->currency ?: 'INR',
            'starts_at'       => $subscription->starts_at?->toDateTimeString(),
            'expires_at'      => $subscription->expires_at?->toDateTimeString(),
            'days_remaining'  => $subscription->daysRemaining(),
            'plan'            => $plan ? $this->slimPlanPayload($plan, $billing) : null,
        ];
    }

    /**
     * Compact plan for config — no duplicate benefit/feature/pricing variants.
     */
    private function slimPlanPayload(Plan $plan, string $billing): array
    {
        $billing = Plan::normalizeBilling($billing);
        $limits = is_array($plan->limits) ? $plan->limits : [];

        return [
            'id'          => $plan->id,
            'name'        => $plan->name,
            'slug'        => $plan->slug,
            'description' => $plan->description,
            'billing'     => $billing,
            'price'       => (float) ($billing === 'yearly' ? ($plan->price_yearly ?? 0) : ($plan->price_monthly ?? 0)),
            'price_monthly' => (float) ($plan->price_monthly ?? 0),
            'price_yearly'  => (float) ($plan->price_yearly ?? 0),
            'currency'    => $plan->currency ?? 'INR',
            'duration'    => $plan->billingDaysFor($billing),
            'discount'    => $plan->publicDiscount(),
            'pricing'     => $plan->pricingBreakdown($billing),
            'benefits'    => array_values(array_map(
                static fn (array $row) => [
                    'label' => $row['label'] ?? '',
                    'value' => $row['value'] ?? '',
                ],
                $plan->benefitSummary($billing)
            )),
            'limits'      => [
                'free_session_minutes'      => $limits['free_session_minutes'] ?? null,
                'free_session_max_duration' => $limits['free_session_max_duration'] ?? null,
            ],
            'badge_label' => $plan->badge_label,
            'badge_color' => $plan->badge_color,
            'is_featured' => (bool) $plan->is_featured,
            'color'       => $plan->color,
        ];
    }
}
