<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanInvoice;
use App\Models\UserSubscription;
use App\Services\PlanInvoiceService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = UserSubscription::with(['user', 'plan', 'invoice'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('subscription_id', 'like', "%{$q}%")
                    ->orWhere('razorpay_payment_id', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
            });
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        $plans = Plan::orderBy('name')->get(['id', 'name', 'plan_name']);

        return view('admin.subscriptions.index', compact('subscriptions', 'plans'));
    }

    public function show(UserSubscription $subscription)
    {
        $subscription->load(['user', 'plan', 'invoice']);

        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function generateInvoice(UserSubscription $subscription)
    {
        if ($subscription->payment_status !== 'paid') {
            return back()->with('error', 'Invoice can only be generated for paid subscriptions.');
        }

        $invoice = app(PlanInvoiceService::class)->ensureForSubscription($subscription, 'admin');

        return redirect()->route('admin.subscriptions.show', $subscription)
            ->with('success', 'Invoice generated: '.$invoice->invoice_number);
    }
}
