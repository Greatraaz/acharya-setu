<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Plans, WalletTransaction};
use Illuminate\Http\{Request, JsonResponse};

class PlansController extends Controller
{
   
    public function index(): JsonResponse
    {
        return response()->json(['plans' => Plans::where('is_active', true)->get()]);
    }

    
    public function subscribe(Request $request): JsonResponse
    {
        $d    = $request->validate(['plan_slug' => 'required|string', 'razorpay_payment_id' => 'nullable|string']);
        $plan = Plans::where('slug', $d['plan_slug'])->firstOrFail();
        $request->user()->update(['subscription_plan' => $plan->slug]);
        if ($plan->price_monthly > 0) {
            WalletTransaction::create([
                'user_id'      => $request->user()->id,
                'amount'       => $plan->price_monthly,
                'type'         => 'debit',
                'description'  => "Subscription: {$plan->name}",
                'reference_id' => $d['razorpay_payment_id'] ?? null,
                'status'       => 'completed',
            ]);
        }
        app(\App\Services\NotificationService::class)->send(
            $request->user(),
            'subscription',
            'Plan Upgraded!',
            "Welcome to {$plan->name}!",
            ['plan_slug' => $plan->slug],
            app(\App\Services\NotificationService::class)->roleUrl($request->user(), 'plans')
        );
        return response()->json(['message' => "Subscribed to {$plan->name}", 'plan' => $plan]);
    }
}
