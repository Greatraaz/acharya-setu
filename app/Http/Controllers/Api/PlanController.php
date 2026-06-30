<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $startsAt  = Carbon::now();
        $expiresAt = $startsAt->copy()->addDays($plan->duration);

        $subscription = UserSubscription::create([
            'user_id'         => $user->id,
            'plan_id'         => $plan->id,
            'subscription_id' => 'SUB-' . mt_rand(10000000, 99999999),
            'amount_paid'     => $plan->price, 
            'status'          => 'active',
            'starts_at'       => $startsAt,
            'expires_at'      => $expiresAt,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Plan subscribed successfully.',
            'data'    => [
                'subscription_id'   => $subscription->subscription_id,
                'plan_name'         => $plan->plan_name,
                'amount_paid'       => $subscription->amount_paid,
                'starts_at'         => $subscription->starts_at->toDateTimeString(),
                'expires_at'        => $subscription->expires_at->toDateTimeString(),
            ],
        ], 201);
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
}
