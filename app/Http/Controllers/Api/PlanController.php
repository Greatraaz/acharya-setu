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

        // Check if user already has an active subscription for this plan
        $alreadyActive = UserSubscription::where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'active')
            ->where('expires_at', '>', Carbon::now())
            ->exists();

        if ($alreadyActive) {
            return response()->json([
                'status'  => false,
                'message' => 'You already have an active subscription for this plan.',
            ], 422);
        }

        if ((float) $plan->price <= 0) {
            $subscription = $this->activateSubscription($user->id, $plan, null);

            return response()->json([
                'status'  => true,
                'message' => 'Plan subscribed successfully.',
                'data'    => [
                    'subscription_id'   => $subscription->subscription_id,
                    'plan_name'         => $plan->plan_name,
                    'amount_paid'       => $subscription->amount_paid,
                    'currency'          => $subscription->currency,
                    'payment_status'    => $subscription->payment_status,
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
                        'user_id' => (string) $user->id,
                        'plan_id' => (string) $plan->id,
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

        $subscription = UserSubscription::create([
            'user_id'           => $user->id,
            'plan_id'           => $plan->id,
            'subscription_id'   => 'SUB-' . mt_rand(10000000, 99999999),
            'amount_paid'       => $plan->price,
            'currency'          => $currency,
            'payment_status'    => 'pending',
            'status'            => 'pending',
            'razorpay_order_id' => $order['id'] ?? null,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Payment order created.',
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
            ->where('plan_id', $plan->id)
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->latest('id')
            ->first();

        if (! $subscription) {
            return response()->json([
                'status'  => false,
                'message' => 'Pending subscription not found for this payment order.',
            ], 404);
        }

        if ($subscription->payment_status === 'paid' && $subscription->status === 'active') {
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

        $this->deactivateExistingActiveSubscriptions($user->id);

        $startsAt = Carbon::now();
        $expiresAt = $startsAt->copy()->addDays((int) $plan->duration);

        $subscription->update([
            'payment_status'      => 'paid',
            'payment_reference'   => $data['razorpay_payment_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'status'              => 'active',
            'starts_at'           => $startsAt,
            'expires_at'          => $expiresAt,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Payment verified and subscription activated.',
            'data'    => [
                'subscription_id'   => $subscription->subscription_id,
                'plan_name'         => $plan->plan_name,
                'amount_paid'       => $subscription->amount_paid,
                'currency'          => $subscription->currency,
                'payment_reference' => $subscription->payment_reference,
                'payment_status'    => $subscription->payment_status,
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
                'starts_at'       => $subscription->starts_at->toDateTimeString(),
                'expires_at'      => $subscription->expires_at->toDateTimeString(),
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
                    'starts_at'         => $sub->starts_at->toDateTimeString(),
                    'expires_at'        => $sub->expires_at->toDateTimeString(),
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

    private function deactivateExistingActiveSubscriptions(int $userId): void
    {
        UserSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->update(['status' => 'expired']);
    }

    private function activateSubscription(int $userId, Plan $plan, ?string $paymentReference): UserSubscription
    {
        $this->deactivateExistingActiveSubscriptions($userId);

        $startsAt = Carbon::now();
        $expiresAt = $startsAt->copy()->addDays((int) $plan->duration);

        return UserSubscription::create([
            'user_id'           => $userId,
            'plan_id'           => $plan->id,
            'subscription_id'   => 'SUB-' . mt_rand(10000000, 99999999),
            'amount_paid'       => (float) $plan->price,
            'currency'          => strtoupper($plan->currency ?? 'INR'),
            'payment_status'    => 'paid',
            'payment_reference' => $paymentReference,
            'status'            => 'active',
            'starts_at'         => $startsAt,
            'expires_at'        => $expiresAt,
        ]);
    }
}
