<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Plan;
use App\Models\UserSubscription;
use App\Services\PlanCheckoutService;
use App\Services\PlanInvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $plans = Plan::active()->ordered()->get();

        $current = UserSubscription::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where('expires_at', '>', Carbon::now())
            ->latest('starts_at')
            ->first();

        $checkout = app(PlanCheckoutService::class);
        $quotes = $plans->mapWithKeys(
            fn (Plan $plan) => [$plan->id => $checkout->quote($plan, $current)]
        );

        $status = $request->input('status');
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $history = UserSubscription::with(['plan', 'invoice'])
            ->where('user_id', $user->id)
            ->when(in_array($status, ['active', 'cancelled', 'expired', 'pending'], true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(function ($inner) use ($search) {
                $inner->where('subscription_id', 'like', '%'.$search.'%')
                    ->orWhereHas('plan', fn ($p) => $p->where('name', 'like', '%'.$search.'%'));
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('frontend.mentee.plans', compact('plans', 'current', 'quotes', 'history', 'status', 'search'));
    }

    /**
     * Start subscribe / upgrade. Free plans activate immediately.
     * Paid plans return Razorpay order JSON for Checkout.js.
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $user = auth()->user();

        $activePlan = Plan::active()->where('id', $plan->id)->first();
        if (! $activePlan) {
            return response()->json(['message' => 'Plan not found or currently inactive.'], 404);
        }
        $plan = $activePlan;

        $current = $this->currentSubscription($user->id);

        if (
            $current
            && (int) $current->plan_id === (int) $plan->id
            && $current->status === 'active'
            && $current->payment_status === 'paid'
            && $current->expires_at
            && $current->expires_at->isFuture()
        ) {
            return response()->json(['message' => 'You already have an active subscription for this plan.'], 422);
        }

        $checkout = app(PlanCheckoutService::class);
        $isUpgrade = $checkout->isPaidActive($current) && (int) $current->plan_id !== (int) $plan->id;
        $quote = $checkout->quote($plan, $isUpgrade ? $current : null);
        $pricing = $quote['pricing'];
        $price = (float) $quote['payable'];

        if (! $checkout->requiresOnlinePayment($quote)) {
            $subscription = $this->activateOrUpgradeSubscription($user->id, $plan, null, $quote);
            $invoice = app(PlanInvoiceService::class)->ensureForSubscription($subscription->fresh(), 'system');

            return response()->json([
                'message' => $isUpgrade ? 'Plan upgraded successfully.' : 'Plan subscribed successfully.',
                'free'    => true,
                'data'    => [
                    'subscription_id' => $subscription->subscription_id,
                    'plan_name'       => $plan->name,
                    'expires_at'      => $subscription->expires_at?->toDateTimeString(),
                    'pricing'         => $pricing,
                    'checkout'        => [
                        'is_upgrade' => $quote['is_upgrade'],
                        'plan_total' => $quote['plan_total'],
                        'payable'    => $quote['payable'],
                        'credit'     => $quote['credit'],
                    ],
                    'invoice'         => $invoice->toPublicArray(),
                ],
            ]);
        }

        $creds = $this->razorpayCredentials();
        if (empty($creds['key']) || empty($creds['secret'])) {
            return response()->json(['message' => 'Payment gateway is not configured.'], 503);
        }
        if (! ($creds['enabled'] ?? true)) {
            return response()->json(['message' => 'Razorpay is disabled in admin settings.'], 503);
        }

        $currency = $pricing['currency'];
        $amountInPaise = (int) round($price * 100);

        if ($amountInPaise < 100) {
            return response()->json(['message' => 'Plan amount must be at least ₹1 for online payment.'], 422);
        }

        $receipt = 'plan_'.$user->id.'_'.$plan->id.'_'.time();

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountInPaise,
                    'currency' => $currency,
                    'receipt'  => Str::limit($receipt, 40, ''),
                    'notes'    => [
                        'user_id'          => (string) $user->id,
                        'plan_id'          => (string) $plan->id,
                        'is_upgrade'       => $isUpgrade ? '1' : '0',
                        'source'           => 'web',
                        'base'             => (string) $pricing['base'],
                        'cgst'             => (string) $pricing['cgst_amount'],
                        'sgst'             => (string) $pricing['sgst_amount'],
                        'discount_percent' => (string) ($pricing['discount_percent'] ?? 0),
                        'discount_amount'  => (string) ($pricing['discount_amount'] ?? 0),
                        'credit_amount'    => (string) ($quote['credit']['amount'] ?? 0),
                        'payable'          => (string) $price,
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay order create failed for mentee web plan purchase.', [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);

                return response()->json(['message' => 'Unable to initiate payment right now.'], 502);
            }

            $order = $response->json();
        } catch (\Throwable $e) {
            Log::error('Razorpay order exception for mentee web plan purchase: '.$e->getMessage());

            return response()->json(['message' => 'Unable to initiate payment right now.'], 502);
        }

        $subscription = $this->upsertPendingSubscription(
            $user->id,
            $plan,
            $currency,
            $order['id'] ?? null,
            $isUpgrade,
            $quote
        );

        return response()->json([
            'free'               => false,
            'plan_id'            => $plan->id,
            'plan_name'          => $plan->name,
            'subscription_id'    => $subscription->subscription_id,
            'order_id'           => $order['id'] ?? null,
            'amount'             => $amountInPaise,
            'amount_rupees'      => $price,
            'pricing'            => $pricing,
            'checkout'           => [
                'is_upgrade' => $quote['is_upgrade'],
                'plan_total' => $quote['plan_total'],
                'payable'    => $quote['payable'],
                'credit'     => $quote['credit'],
            ],
            'currency'           => $currency,
            'key'                => $creds['key'],
            'name'               => 'Vedrix',
            'description'        => ($isUpgrade ? 'Upgrade to ' : 'Subscribe to ').$plan->name,
            'is_upgrade'         => $isUpgrade,
            'prefill'            => [
                'name'    => $user->name,
                'email'   => $user->email,
                'contact' => $user->phone ?? '',
            ],
        ]);
    }

    public function verify(Request $request, Plan $plan)
    {
        $user = auth()->user();

        $activePlan = Plan::active()->where('id', $plan->id)->first();
        if (! $activePlan) {
            return response()->json(['message' => 'Plan not found or currently inactive.'], 404);
        }
        $plan = $activePlan;

        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $creds = $this->razorpayCredentials();
        if (empty($creds['secret'])) {
            return response()->json(['message' => 'Payment gateway is not configured.'], 503);
        }

        $expectedSig = hash_hmac(
            'sha256',
            $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'],
            $creds['secret']
        );

        if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
            return response()->json(['message' => 'Payment signature verification failed.'], 422);
        }

        $subscription = UserSubscription::where('user_id', $user->id)
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->latest('id')
            ->first();

        if (! $subscription) {
            return response()->json(['message' => 'Pending subscription not found for this payment order.'], 404);
        }

        if (
            $subscription->payment_status === 'paid'
            && $subscription->status === 'active'
            && (int) $subscription->plan_id === (int) $plan->id
            && $subscription->razorpay_payment_id === $data['razorpay_payment_id']
        ) {
            return response()->json([
                'message' => 'Subscription already activated.',
            ]);
        }

        $checkout = app(PlanCheckoutService::class);
        $quote = $checkout->quoteFromSnapshot($subscription, $plan);

        $subscription = $checkout->activate($subscription, $plan, $quote, [
            'payment_reference'   => $data['razorpay_payment_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'razorpay_order_id'   => $data['razorpay_order_id'],
        ]);

        $this->expireOtherSubscriptions($user->id, $subscription->id);
        $subscription->refresh();
        $invoice = app(PlanInvoiceService::class)->ensureForSubscription($subscription, 'system');

        return response()->json([
            'message' => 'Payment verified. Your plan is now active!',
            'data'    => [
                'subscription_id' => $subscription->subscription_id,
                'plan_name'       => $plan->name,
                'expires_at'      => $subscription->expires_at?->toDateTimeString(),
                'pricing'         => $quote['pricing'],
                'checkout'        => [
                    'is_upgrade' => $quote['is_upgrade'],
                    'plan_total' => $quote['plan_total'],
                    'payable'    => $quote['payable'],
                    'credit'     => $quote['credit'],
                ],
                'invoice'         => $invoice->toPublicArray(),
            ],
        ]);
    }

    public function cancel(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
        ]);

        $subscription = UserSubscription::where('user_id', auth()->id())
            ->where('subscription_id', $request->subscription_id)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->first();

        if (! $subscription) {
            return back()->with('error', 'Active subscription not found.');
        }

        $subscription->update(['status' => 'cancelled']);

        return back()->with('success', 'Subscription cancelled successfully.');
    }

    private function razorpayCredentials(): array
    {
        $settings = AppSetting::razorpay();

        return [
            'enabled' => $settings['enabled'] ?? true,
            'key'     => $settings['key'] ?: config('services.razorpay.key', env('RAZORPAY_KEY_ID', '')),
            'secret'  => $settings['secret'] ?: config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET', '')),
        ];
    }

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

        return UserSubscription::where('user_id', $userId)->latest('id')->first();
    }

    private function upsertPendingSubscription(
        int $userId,
        Plan $plan,
        string $currency,
        ?string $razorpayOrderId,
        bool $isUpgrade = false,
        ?array $quote = null
    ): UserSubscription {
        $checkout = app(PlanCheckoutService::class);
        $subscription = $this->currentSubscription($userId);
        $quote = $quote ?? $checkout->quote($plan, $isUpgrade ? $subscription : null);
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
            'subscription_id' => 'SUB-'.mt_rand(10000000, 99999999),
        ]));
    }

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

        return UserSubscription::create([
            'user_id'           => $userId,
            'subscription_id'   => 'SUB-'.mt_rand(10000000, 99999999),
            'plan_id'           => $plan->id,
            'amount_paid'       => (float) $quote['payable'],
            'currency'          => $quote['currency'] ?? 'INR',
            'payment_status'    => 'paid',
            'payment_reference' => $paymentReference,
            'status'            => 'active',
            'starts_at'         => $startsAt,
            'expires_at'        => $startsAt->copy()->addDays($plan->billingDays()),
            'meta'              => $checkout->storeSnapshot($placeholder, $plan, $quote),
        ]);
    }

    private function expireOtherSubscriptions(int $userId, int $keepId): void
    {
        UserSubscription::where('user_id', $userId)
            ->where('id', '!=', $keepId)
            ->whereIn('status', ['active', 'pending'])
            ->update(['status' => 'expired']);
    }
}
