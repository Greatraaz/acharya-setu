<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Plan;
use App\Models\UserSubscription;
use App\Services\PlanCheckoutService;
use App\Services\PlanInvoiceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────────
    // 1. List all active plans
    // GET /api/plans
    // ─────────────────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $billing = Plan::normalizeBilling($request->query('billing', 'monthly'));
        $checkout = app(PlanCheckoutService::class);
        $current = $this->paidActiveForRequest($request);
        $plans = Plan::active()->orderBy('price_monthly', 'asc')->get()
            ->map(fn (Plan $plan) => $plan->toPublicArray(
                $checkout->quote($plan, $current, $billing),
                $billing
            ));

        return response()->json([
            'status'  => true,
            'message' => 'Plans fetched successfully.',
            'billing' => $billing,
            'data'    => $plans,
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 2. Single plan detail
    // GET /api/plans/{slug}
    // ─────────────────────────────────────────────────────────────────────────────

    public function show(Request $request, $id): JsonResponse
    {
        $plan = Plan::active()->where('id', $id)->first();

        if (!$plan) {
            return response()->json([
                'status'  => false,
                'message' => 'Plan not found.',
            ], 404);
        }

        $billing = Plan::normalizeBilling($request->query('billing', 'monthly'));

        return response()->json([
            'status'  => true,
            'message' => 'Plan fetched successfully.',
            'billing' => $billing,
            'data'    => $plan->toPublicArray(
                app(PlanCheckoutService::class)->quote($plan, $this->paidActiveForRequest($request), $billing),
                $billing
            ),
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 3. Purchase / Subscribe to a plan
    // POST /api/plans/subscribe
    // ─────────────────────────────────────────────────────────────────────────────

    public function subscribe(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $plan = Plan::active()->find($id);

        if (!$plan) {
            return response()->json([
                'status'  => false,
                'message' => 'Plan not found or currently inactive.',
            ], 404);
        }

        $current = $this->currentSubscription($user->id);

        // Same plan already active — no need to subscribe again
        if (
            $current
            && (int) $current->plan_id === (int) $plan->id
            && $current->status === 'active'
            && $current->payment_status === 'paid'
            && $current->expires_at
            && $current->expires_at->isFuture()
        ) {
            return response()->json([
                'status'  => false,
                'message' => 'You already have an active subscription for this plan.',
            ], 422);
        }

        $checkout = app(PlanCheckoutService::class);
        $billing = Plan::normalizeBilling($request->input('billing', 'monthly'));
        $isUpgrade = $checkout->isPaidActive($current) && (int) $current->plan_id !== (int) $plan->id;
        $quote = $checkout->quote($plan, $isUpgrade ? $current : null, $billing);
        $pricing = $quote['pricing'];
        $price = (float) $quote['payable'];

        if (! $checkout->requiresOnlinePayment($quote)) {
            $subscription = $this->activateOrUpgradeSubscription($user->id, $plan, null, $quote);
            $invoice = app(PlanInvoiceService::class)->ensureForSubscription($subscription->fresh(), 'system');

            return response()->json([
                'status'  => true,
                'message' => $isUpgrade ? 'Plan upgraded successfully.' : 'Plan subscribed successfully.',
                'data'    => [
                    'subscription_id'   => $subscription->subscription_id,
                    'plan_name'         => $plan->plan_name,
                    'amount_paid'       => $subscription->amount_paid,
                    'currency'          => $subscription->currency,
                    'payment_status'    => $subscription->payment_status,
                    'is_upgrade'        => $isUpgrade,
                    'pricing'           => $pricing,
                    'checkout'          => [
                        'is_upgrade' => $quote['is_upgrade'],
                        'plan_total' => $quote['plan_total'],
                        'payable'    => $quote['payable'],
                        'credit'     => $quote['credit'],
                    ],
                    'invoice'           => $invoice->toPublicArray(),
                    'starts_at'         => $subscription->starts_at?->toDateTimeString(),
                    'expires_at'        => $subscription->expires_at?->toDateTimeString(),
                ],
            ], 201);
        }

        $creds = $this->razorpayCredentials();
        if (empty($creds['key']) || empty($creds['secret'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment gateway is not configured.',
            ], 503);
        }

        if (! ($creds['enabled'] ?? true)) {
            return response()->json([
                'status'  => false,
                'message' => 'Razorpay is disabled in admin settings.',
            ], 503);
        }

        $receipt = 'plan_' . $user->id . '_' . $plan->id . '_' . time();
        $currency = $pricing['currency'];
        $amountInPaise = (int) round($price * 100);

        if ($amountInPaise < 100) {
            return response()->json([
                'status'  => false,
                'message' => 'Plan amount must be at least 1 INR for online payment.',
            ], 422);
        }

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountInPaise,
                    'currency' => $currency,
                    'receipt'  => Str::limit($receipt, 40, ''),
                    'notes'    => [
                        'user_id'           => (string) $user->id,
                        'plan_id'           => (string) $plan->id,
                        'is_upgrade'        => $isUpgrade ? '1' : '0',
                        'base'              => (string) $pricing['base'],
                        'cgst'              => (string) $pricing['cgst_amount'],
                        'sgst'              => (string) $pricing['sgst_amount'],
                        'igst'              => (string) ($pricing['igst_amount'] ?? 0),
                        'discount_percent'  => (string) ($pricing['discount_percent'] ?? 0),
                        'discount_amount'   => (string) ($pricing['discount_amount'] ?? 0),
                        'credit_amount'     => (string) ($quote['credit']['amount'] ?? 0),
                        'payable'           => (string) $price,
                        'billing'           => $billing,
                    ],
                ]);

            if (! $response->successful()) {
                $razorpayError = $response->json('error.description')
                    ?? $response->json('error.reason')
                    ?? $response->body();

                Log::error('Razorpay order create failed for plan purchase.', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Unable to initiate payment right now.',
                    'error'   => config('app.debug') ? $razorpayError : null,
                ], 502);
            }

            $order = $response->json();
        } catch (\Throwable $e) {
            Log::error('Razorpay order create exception for plan purchase.', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to initiate payment right now.',
            ], 502);
        }

        // Reuse existing row (upgrade / change) instead of creating another subscription
        $subscription = $this->upsertPendingSubscription(
            $user->id,
            $plan,
            $currency,
            $order['id'] ?? null,
            $isUpgrade,
            $quote
        );

        return response()->json([
            'status'  => true,
            'message' => $isUpgrade ? 'Upgrade payment order created.' : 'Payment order created.',
            'data'    => [
                'plan_id'            => $plan->id,
                'plan_name'          => $plan->plan_name,
                'subscription_id'    => $subscription->subscription_id,
                'razorpay_order_id'  => $order['id'] ?? null,
                'amount'             => $price,
                'amount_paise'       => $amountInPaise,
                'pricing'            => $pricing,
                'checkout'           => [
                    'is_upgrade' => $quote['is_upgrade'],
                    'plan_total' => $quote['plan_total'],
                    'payable'    => $quote['payable'],
                    'credit'     => $quote['credit'],
                ],
                'currency'           => $currency,
                'razorpay_key'       => $creds['key'],
                'payment_status'     => 'pending',
                'is_upgrade'         => $isUpgrade,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 3b. Verify plan payment and activate subscription
    // POST /api/plans/subscribe/{id}/verify
    // ─────────────────────────────────────────────────────────────────────────────
    public function verifySubscriptionPayment(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        $plan = Plan::active()->find($id);

        if (! $plan) {
            return response()->json([
                'status'  => false,
                'message' => 'Plan not found or currently inactive.',
            ], 404);
        }

        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $creds = $this->razorpayCredentials();
        if (empty($creds['secret'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment gateway is not configured.',
            ], 503);
        }

        $expectedSig = hash_hmac(
            'sha256',
            $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'],
            $creds['secret']
        );

        if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment signature verification failed.',
            ], 422);
        }

        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->latest('id')
            ->first();

        if (! $subscription) {
            return response()->json([
                'status'  => false,
                'message' => 'Pending subscription not found for this payment order.',
            ], 404);
        }

        // Already verified for this exact payment + target plan
        if (
            $subscription->payment_status === 'paid'
            && $subscription->status === 'active'
            && (int) $subscription->plan_id === (int) $plan->id
            && $subscription->razorpay_payment_id === $data['razorpay_payment_id']
        ) {
            return response()->json([
                'status'  => true,
                'message' => 'Subscription already activated.',
                'data'    => [
                    'subscription' => $this->formatVerifiedSubscription($subscription, $plan),
                ],
            ], 200);
        }

        $checkout = app(PlanCheckoutService::class);
        $quote = $checkout->quoteFromSnapshot($subscription, $plan);
        $isUpgrade = (bool) $quote['is_upgrade']
            || ((int) $subscription->plan_id !== (int) $plan->id
                && $subscription->status === 'active'
                && $subscription->payment_status === 'paid');

        $subscription = $checkout->activate($subscription, $plan, $quote, [
            'payment_reference'   => $data['razorpay_payment_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_order_id'   => $data['razorpay_order_id'],
        ]);

        // Clean up any leftover duplicate rows for this user (from older flow)
        $this->expireOtherSubscriptions($user->id, $subscription->id);
        $subscription->refresh();
        $invoice = app(PlanInvoiceService::class)->ensureForSubscription($subscription, 'system');

        return response()->json([
            'status'  => true,
            'message' => $isUpgrade
                ? 'Payment verified and plan upgraded.'
                : 'Payment verified and subscription activated.',
            'data'    => [
                'subscription' => array_merge(
                    $this->formatVerifiedSubscription($subscription, $plan),
                    [
                        'payment_reference' => $subscription->payment_reference,
                        'payment_status'    => $subscription->payment_status,
                        'is_upgrade'        => $isUpgrade,
                        'checkout'          => [
                            'is_upgrade' => $quote['is_upgrade'],
                            'plan_total' => $quote['plan_total'],
                            'payable'    => $quote['payable'],
                            'credit'     => $quote['credit'],
                        ],
                        'invoice'           => $invoice->toPublicArray(),
                    ]
                ),
            ],
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 4. Check active subscription (authenticated user)
    // GET /api/plans/subscription/active
    // ─────────────────────────────────────────────────────────────────────────────

    public function activeSubscription(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscription = UserSubscription::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->latest('starts_at')
            ->first();

        if (!$subscription) {
            return response()->json([
                'status'  => false,
                'message' => 'No active subscription found.',
                'data'    => null,
            ], 200);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Active subscription fetched successfully.',
            'data'    => [
                'subscription_id' => $subscription->subscription_id,
                'plan'            => $subscription->plan?->toPublicArray(),
                'amount_paid'     => $subscription->amount_paid,
                'currency'        => $subscription->currency,
                'status'          => $subscription->status,
                'starts_at'       => $subscription->starts_at?->toDateTimeString(),
                'expires_at'      => $subscription->expires_at?->toDateTimeString(),
                'days_remaining'  => $subscription->daysRemaining(),
                'entitlements'    => [
                    'progress_report_enabled' => (bool) ($subscription->plan?->progress_report_enabled),
                    'sessions'                => $user->planSessionAllowance(),
                ],
            ],
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 4b. Plan consumption (included free sessions used this month)
    // GET /api/v1/mentee/plans/subscription/consumption
    // ─────────────────────────────────────────────────────────────────────────────

    public function subscriptionConsumption(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->activeSubscription();
        $allowance = $user->planSessionAllowance();
        [$periodStart, $periodEnd] = $user->planUsageWindow();

        if (! $subscription) {
            return response()->json([
                'status'  => true,
                'message' => 'No active subscription. Session fees apply for bookings.',
                'data'    => [
                    'has_active_subscription' => false,
                    'subscription'            => null,
                    'period'                  => [
                        'label'     => 'billing_period',
                        'timezone'  => 'Asia/Kolkata',
                        'starts_at' => $periodStart->toDateTimeString(),
                        'ends_at'   => $periodEnd->toDateTimeString(),
                    ],
                    'sessions'                => [
                        'included_limit'      => null,
                        'used'                => (int) $allowance['used'],
                        'remaining'           => null,
                        'unlimited'           => false,
                        'percent_used'        => null,
                        'next_booking_free'   => false,
                        'resets_at'           => $periodEnd->copy()->toDateTimeString(),
                    ],
                    'progress_report_enabled' => false,
                ],
            ], 200);
        }

        $limit = $allowance['minutes_limit'] ?? $allowance['limit'];
        $used = (int) ($allowance['minutes_used'] ?? $allowance['used'] ?? 0);
        $unlimited = (bool) $allowance['unlimited'];
        $remaining = $allowance['minutes_remaining'] ?? $allowance['remaining'];
        $maxSession = $allowance['max_session_minutes'] ?? null;
        $percentUsed = null;

        if (! $unlimited && is_int($limit) && $limit > 0) {
            $percentUsed = min(100, round(($used / $limit) * 100, 1));
        } elseif (! $unlimited && $limit === 0) {
            $percentUsed = 100.0;
        }

        $nextFree = $unlimited || (is_int($remaining) && $remaining > 0);

        return response()->json([
            'status'  => true,
            'message' => 'Plan consumption fetched successfully.',
            'data'    => [
                'has_active_subscription' => true,
                'subscription'            => [
                    'id'              => (int) $subscription->id,
                    'subscription_id' => $subscription->subscription_id,
                    'status'          => $subscription->status,
                    'starts_at'       => $subscription->starts_at?->toDateTimeString(),
                    'expires_at'      => $subscription->expires_at?->toDateTimeString(),
                    'days_remaining'  => $subscription->daysRemaining(),
                    'plan'            => [
                        'id'                      => $subscription->plan?->id,
                        'name'                    => $subscription->plan?->name ?? $subscription->plan?->plan_name,
                        'sessions_per_month'      => $subscription->plan?->sessions_per_month,
                        'progress_report_enabled' => (bool) ($subscription->plan?->progress_report_enabled),
                    ],
                ],
                'period'                  => [
                    'label'     => 'billing_period',
                    'timezone'  => 'Asia/Kolkata',
                    'starts_at' => $periodStart->toDateTimeString(),
                    'ends_at'   => $periodEnd->toDateTimeString(),
                ],
                'sessions'                => [
                    // Minute-based free career counselling entitlement
                    'included_limit'       => $limit,
                    'used'                 => $used,
                    'remaining'            => $remaining,
                    'unlimited'            => $unlimited,
                    'percent_used'         => $percentUsed,
                    'max_session_minutes'  => $maxSession,
                    'unit'                 => 'minutes',
                    'benefit'              => $allowance['benefit'] ?? 'career_counselling',
                    'next_booking_free'    => $nextFree,
                    'resets_at'            => $periodEnd->copy()->toDateTimeString(),
                ],
                'progress_report_enabled' => (bool) ($subscription->plan?->progress_report_enabled),
            ],
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 5. Subscription history (authenticated user)
    // GET /api/plans/subscription/history
    // ─────────────────────────────────────────────────────────────────────────────

    public function subscriptionHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscriptions = UserSubscription::with(['plan', 'invoice'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($sub) {
                return [
                    'id'                => (int) $sub->id,
                    'subscription_id'   => $sub->subscription_id,
                    'plan_name'         => $sub->plan->plan_name ?? $sub->plan->name ?? 'N/A',
                    'amount_paid'       => $sub->amount_paid,
                    'currency'          => $sub->currency,
                    'payment_status'    => $sub->payment_status,
                    'status'            => $sub->status,
                    'starts_at'         => optional($sub->starts_at)->toDateTimeString(),
                    'expires_at'        => optional($sub->expires_at)->toDateTimeString(),
                    'is_active'         => $sub->isActive(),
                    'invoice'           => $sub->invoice ? [
                        'id'             => $sub->invoice->id,
                        'invoice_number' => $sub->invoice->invoice_number,
                        'invoice_date'   => $sub->invoice->invoice_date?->toDateString(),
                        'total_amount'   => $sub->invoice->total_amount,
                    ] : null,
                ];
            });

        return response()->json([
            'status'  => true,
            'message' => 'Subscription history fetched successfully.',
            'data'    => $subscriptions,
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 6. Cancel active subscription
    // POST /api/plans/subscription/cancel
    // ─────────────────────────────────────────────────────────────────────────────

    public function cancelSubscription(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subscription_id' => 'required|string',
        ]);

        $user = $request->user();

        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('subscription_id', $data['subscription_id'])
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->first();

        if (!$subscription) {
            return response()->json([
                'status'  => false,
                'message' => 'Active subscription not found.',
            ], 404);
        }

        $subscription->update(['status' => 'cancelled']);

        return response()->json([
            'status'  => true,
            'message' => 'Subscription cancelled successfully.',
        ], 200);
    }

    private function razorpayCredentials(): array
    {
        $settings = AppSetting::razorpay();

        return [
            'enabled' => $settings['enabled'] ?? true,
            'key'     => $settings['key'] ?: env('RAZORPAY_KEY_ID', ''),
            'secret'  => $settings['secret'] ?: env('RAZORPAY_KEY_SECRET', ''),
        ];
    }

    /**
     * Latest subscription row for this user (active preferred, else any latest).
     */
    private function currentSubscription(int $userId): ?UserSubscription
    {
        $active = UserSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->latest('starts_at')
            ->first();

        if ($active) {
            return $active;
        }

        return UserSubscription::where('user_id', $userId)
            ->latest('id')
            ->first();
    }

    private function paidActiveForRequest(Request $request): ?UserSubscription
    {
        $user = $request->user() ?: auth('sanctum')->user();
        if (! $user) {
            return null;
        }

        $current = $user->activeSubscription();

        return app(PlanCheckoutService::class)->isPaidActive($current) ? $current : null;
    }

    /**
     * Create pending row only if user has none; otherwise update the same row for upgrade/change.
     * For upgrades, keep current plan active until payment is verified.
     */
    private function upsertPendingSubscription(
        int $userId,
        Plan $plan,
        string $currency,
        ?string $razorpayOrderId,
        bool $isUpgrade = false,
        ?array $quote = null
    ): UserSubscription {
        $checkout = app(PlanCheckoutService::class);
        $quote = $quote ?? $checkout->quote($plan, $isUpgrade ? $this->currentSubscription($userId) : null);
        $subscription = $this->currentSubscription($userId);
        $price = (float) $quote['payable'];

        if ($subscription && $isUpgrade) {
            $subscription->update([
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => null,
                'meta'                => $checkout->storeSnapshot($subscription, $plan, $quote),
            ]);

            return $subscription->fresh();
        }

        $payload = [
            'plan_id'             => $plan->id,
            'amount_paid'         => $price,
            'currency'            => $currency,
            'payment_status'      => 'pending',
            'payment_reference'   => null,
            'razorpay_order_id'   => $razorpayOrderId,
            'razorpay_payment_id' => null,
            'status'              => 'pending',
            'starts_at'           => null,
            'expires_at'          => null,
            'meta'                => $checkout->storeSnapshot($subscription ?? new UserSubscription(), $plan, $quote),
        ];

        if ($subscription) {
            $subscription->update($payload);

            return $subscription->fresh();
        }

        return UserSubscription::create(array_merge($payload, [
            'user_id'         => $userId,
            'subscription_id' => 'SUB-' . mt_rand(10000000, 99999999),
        ]));
    }

    /**
     * Activate or upgrade on the same subscription row.
     */
    private function activateOrUpgradeSubscription(
        int $userId,
        Plan $plan,
        ?string $paymentReference,
        ?array $quote = null
    ): UserSubscription {
        $checkout = app(PlanCheckoutService::class);
        $subscription = $this->currentSubscription($userId);
        $quote = $quote ?? $checkout->quote(
            $plan,
            $checkout->isPaidActive($subscription) ? $subscription : null
        );

        if ($subscription) {
            $activated = $checkout->activate($subscription, $plan, $quote, [
                'payment_reference' => $paymentReference,
            ]);
            $this->expireOtherSubscriptions($userId, $activated->id);

            return $activated;
        }

        $startsAt = Carbon::now();
        $placeholder = new UserSubscription();
        $subscription = UserSubscription::create([
            'user_id'           => $userId,
            'subscription_id'   => 'SUB-' . mt_rand(10000000, 99999999),
            'plan_id'           => $plan->id,
            'amount_paid'       => (float) $quote['payable'],
            'currency'          => $quote['currency'] ?? 'INR',
            'payment_status'    => 'paid',
            'payment_reference' => $paymentReference,
            'status'            => 'active',
            'starts_at'         => $startsAt,
            'expires_at'        => $startsAt->copy()->addDays(
                $plan->billingDaysFor($quote['billing'] ?? ($quote['pricing']['billing'] ?? 'monthly'))
            ),
            'meta'              => $checkout->storeSnapshot($placeholder, $plan, $quote),
        ]);

        return $subscription;
    }

    /**
     * Verified subscription payload for subscribe verify responses.
     */
    private function formatVerifiedSubscription(UserSubscription $subscription, Plan $plan): array
    {
        return [
            'id'              => (int) $subscription->id,
            'subscription_id' => $subscription->subscription_id,
            'plan_id'         => (int) $subscription->plan_id,
            'plan_name'       => $plan->plan_name,
            'amount_paid'     => $subscription->amount_paid,
            'currency'        => $subscription->currency,
            'starts_at'       => $subscription->starts_at?->toDateTimeString(),
            'expires_at'      => $subscription->expires_at?->toDateTimeString(),
        ];
    }

    /**
     * Expire any other subscription rows for this user (legacy duplicates).
     */
    private function expireOtherSubscriptions(int $userId, int $keepId): void
    {
        UserSubscription::where('user_id', $userId)
            ->where('id', '!=', $keepId)
            ->whereIn('status', ['active', 'pending'])
            ->update(['status' => 'expired']);
    }
}
