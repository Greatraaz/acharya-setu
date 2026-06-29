<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{PremiumPlan, WalletTransaction, Notification};
use Illuminate\Http\{Request, JsonResponse};

class PlansController extends Controller
{
   
    public function index(): JsonResponse
    {
        return response()->json(['plans' => PremiumPlan::where('is_active', true)->get()]);
    }

    
    public function subscribe(Request $request): JsonResponse
    {
        $d    = $request->validate(['plan_slug' => 'required|string', 'razorpay_payment_id' => 'nullable|string']);
        $plan = PremiumPlan::where('slug', $d['plan_slug'])->firstOrFail();
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
        Notification::create([
            'user_id' => $request->user()->id,
            'type'    => 'subscription',
            'title'   => 'Plan Upgraded!',
            'body'    => "Welcome to {$plan->name}!",
        ]);
        return response()->json(['message' => "Subscribed to {$plan->name}", 'plan' => $plan]);
    }
}
