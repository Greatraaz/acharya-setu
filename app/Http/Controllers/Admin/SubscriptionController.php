<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanInvoice;
use App\Models\SubscriptionCancelRequest;
use App\Models\UserSubscription;
use App\Services\CounsellingBenefitService;
use App\Services\PlanInvoiceService;
use App\Services\SubscriptionCancelService;
use Illuminate\Http\Request;
use InvalidArgumentException;

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
        $plans = Plan::orderBy('name')->get(['id', 'name']);

        return view('admin.subscriptions.index', compact('subscriptions', 'plans'));
    }

    public function show(UserSubscription $subscription)
    {
        $subscription->load(['user', 'plan', 'invoice']);
        $pendingCancel = $subscription->cancelRequests()
            ->where('status', SubscriptionCancelRequest::STATUS_PENDING)
            ->latest('id')
            ->first();
        $counselling = $subscription->user
            ? app(CounsellingBenefitService::class)->snapshot($subscription->user)
            : null;

        return view('admin.subscriptions.show', compact('subscription', 'counselling', 'pendingCancel'));
    }

    public function reconcileCounselling(UserSubscription $subscription)
    {
        $user = $subscription->user;
        if (! $user) {
            return back()->with('error', 'This subscription has no mentee to reconcile.');
        }

        $message = app(CounsellingBenefitService::class)->reconcile($user);

        return redirect()->route('admin.subscriptions.show', $subscription)->with('success', $message);
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

    public function cancelIndex(Request $request)
    {
        app(SubscriptionCancelService::class)->autoApproveDue();

        $query = SubscriptionCancelRequest::with(['user', 'subscription.plan'])
            ->latest('id');

        $status = $request->input('status', SubscriptionCancelRequest::STATUS_PENDING);
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->whereHas('user', function ($userQuery) use ($q) {
                    $userQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                })->orWhereHas('subscription', fn ($s) => $s->where('subscription_id', 'like', "%{$q}%"));
            });
        }

        $requests = $query->paginate(20)->withQueryString();
        $stats = [
            'pending'  => SubscriptionCancelRequest::where('status', SubscriptionCancelRequest::STATUS_PENDING)->count(),
            'approved' => SubscriptionCancelRequest::where('status', SubscriptionCancelRequest::STATUS_APPROVED)->count(),
            'rejected' => SubscriptionCancelRequest::where('status', SubscriptionCancelRequest::STATUS_REJECTED)->count(),
        ];

        return view('admin.subscriptions.cancels', compact('requests', 'stats'));
    }

    public function approveCancel(SubscriptionCancelRequest $cancel, SubscriptionCancelService $cancels)
    {
        try {
            $cancels->approve($cancel, auth()->user());
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cancellation approved. Refund has been processed and the yearly plan is cancelled.');
    }

    public function rejectCancel(Request $request, SubscriptionCancelRequest $cancel, SubscriptionCancelService $cancels)
    {
        $request->validate(['admin_note' => 'nullable|string|max:1000']);

        try {
            $cancels->reject($cancel, auth()->user(), $request->input('admin_note'));
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cancellation request declined. The mentee subscription stays active.');
    }
}
