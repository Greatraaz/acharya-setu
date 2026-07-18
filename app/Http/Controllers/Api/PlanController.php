<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Plan;
use App\Models\UserSubscription;
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

    public function index(): JsonResponse
    {
        $plans = Plan::active()->orderBy('price', 'asc')->get();

        return response()->json([
            'status'  => true,
            'message' => 'Plans fetched successfully.',
            'data'    => $plans,
        ], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────────
    // 2. Single plan detail
    // GET /api/plans/{slug}
    // ─────────────────────────────────────────────────────────────────────────────

    public function show($id): JsonResponse
    {
        $plan = Plan::active()->where('id', $id)->first();

        if (!$plan) {
            return response()->json([
                'status'  => false,
                'message' => 'Plan not found.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Plan fetched successfully.',
            'data'    => $plan,
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

        $isUpgrade = $current
            && $current->status === 'active'
            && $current->payment_status === 'paid'
            && $current->expires_at
            && $current->expires_at->isFuture()
            && (int) $current->plan_id !== (int) $plan->id;

        if ((float) $plan->price <= 0) {
            $subscription = $this->activateOrUpgradeSubscription($user->id, $plan, null);

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
        $amountInPaise = (int) round(((float) $plan->price) * 100);
        $currency = strtoupper($plan->currency ?? 'INR');

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
                        'user_id'    => (string) $user->id,
                        'plan_id'    => (string) $plan->id,
                        'is_upgrade' => $isUpgrade ? '1' : '0',
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
            $isUpgrade
        );

        return response()->json([
            'status'  => true,
            'message' => $isUpgrade ? 'Upgrade payment order created.' : 'Payment order created.',
            'data'    => [
                'plan_id'            => $plan->id,
                'plan_name'          => $plan->plan_name,
                'subscription_id'    => $subscription->subscription_id,
                'razorpay_order_id'  => $order['id'] ?? null,
                'amount'             => (float) $plan->price,
                'amount_paise'       => $amountInPaise,
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
                    'subscription_id' => $subscription->subscription_id,
                    'plan_name'       => $plan->plan_name,
                    'amount_paid'     => $subscription->amount_paid,
                    'currency'        => $subscription->currency,
                    'starts_at'       => $subscription->starts_at?->toDateTimeString(),
                    'expires_at'      => $subscription->expires_at?->toDateTimeString(),
                ],
            ], 200);
        }

        $isUpgrade = (int) $subscription->plan_id !== (int) $plan->id
            && $subscription->status === 'active'
            && $subscription->payment_status === 'paid';

        $startsAt = Carbon::now();
        $expiresAt = $startsAt->copy()->addDays((int) $plan->duration);

        // Upgrade / change plan on the same row — never create a second active subscription
        $subscription->update([
            'plan_id'             => $plan->id,
            'amount_paid'         => (float) $plan->price,
            'currency'            => strtoupper($plan->currency ?? 'INR'),
            'payment_status'      => 'paid',
            'payment_reference'   => $data['razorpay_payment_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'status'              => 'active',
            'starts_at'           => $startsAt,
            'expires_at'          => $expiresAt,
        ]);

        // Clean up any leftover duplicate rows for this user (from older flow)
        $this->expireOtherSubscriptions($user->id, $subscription->id);

        return response()->json([
            'status'  => true,
            'message' => $isUpgrade
                ? 'Payment verified and plan upgraded.'
                : 'Payment verified and subscription activated.',
            'data'    => [
                'subscription_id'   => $subscription->subscription_id,
                'plan_name'         => $plan->plan_name,
                'amount_paid'       => $subscription->amount_paid,
                'currency'          => $subscription->currency,
                'payment_reference' => $subscription->payment_reference,
                'payment_status'    => $subscription->payment_status,
                'is_upgrade'        => $isUpgrade,
                'starts_at'         => $subscription->starts_at?->toDateTimeString(),
                'expires_at'        => $subscription->expires_at?->toDateTimeString(),
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
                'plan'            => $subscription->plan,
                'amount_paid'     => $subscription->amount_paid,
                'currency'        => $subscription->currency,
                'status'          => $subscription->status,
                'starts_at'       => $subscription->starts_at?->toDateTimeString(),
                'expires_at'      => $subscription->expires_at?->toDateTimeString(),
                'days_remaining'  => $subscription->daysRemaining(),
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

        $subscriptions = UserSubscription::with('plan')
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($sub) {
                return [
                    'subscription_id'   => $sub->subscription_id,
                    'plan_name'         => $sub->plan->plan_name ?? 'N/A',
                    'amount_paid'       => $sub->amount_paid,
                    'currency'          => $sub->currency,
                    'payment_status'    => $sub->payment_status,
                    'status'            => $sub->status,
                    // Pending / unpaid rows may not have dates yet
                    'starts_at'         => optional($sub->starts_at)->toDateTimeString(),
                    'expires_at'        => optional($sub->expires_at)->toDateTimeString(),
                    'is_active'         => $sub->isActive(),
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

    /**
     * Create pending row only if user has none; otherwise update the same row for upgrade/change.
     * For upgrades, keep current plan active until payment is verified.
     */
    private function upsertPendingSubscription(
        int $userId,
        Plan $plan,
        string $currency,
        ?string $razorpayOrderId,
        bool $isUpgrade = false
    ): UserSubscription {
        $subscription = $this->currentSubscription($userId);

        if ($subscription && $isUpgrade) {
            // Keep current active plan until verify succeeds; only attach new order id
            $subscription->update([
                'razorpay_order_id'   => $razorpayOrderId,
                'razorpay_payment_id' => null,
            ]);

            return $subscription->fresh();
        }

        $payload = [
            'plan_id'             => $plan->id,
            'amount_paid'         => (float) $plan->price,
            'currency'            => $currency,
            'payment_status'      => 'pending',
            'payment_reference'   => null,
            'razorpay_order_id'   => $razorpayOrderId,
            'razorpay_payment_id' => null,
            'status'              => 'pending',
            'starts_at'           => null,
            'expires_at'          => null,
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
        ?string $paymentReference
    ): UserSubscription {
        $startsAt = Carbon::now();
        $expiresAt = $startsAt->copy()->addDays((int) $plan->duration);

        $payload = [
            'plan_id'           => $plan->id,
            'amount_paid'       => (float) $plan->price,
            'currency'          => strtoupper($plan->currency ?? 'INR'),
            'payment_status'    => 'paid',
            'payment_reference' => $paymentReference,
            'status'            => 'active',
            'starts_at'         => $startsAt,
            'expires_at'        => $expiresAt,
        ];

        $subscription = $this->currentSubscription($userId);

        if ($subscription) {
            $subscription->update($payload);
            $this->expireOtherSubscriptions($userId, $subscription->id);

            return $subscription->fresh();
        }

        $subscription = UserSubscription::create(array_merge($payload, [
            'user_id'         => $userId,
            'subscription_id' => 'SUB-' . mt_rand(10000000, 99999999),
        ]));

        return $subscription;
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
