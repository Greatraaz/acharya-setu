<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanInvoice;
use App\Models\UserSubscription;
use App\Services\PlanInvoicePdfService;
use App\Services\PlanInvoiceService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class SubscriptionInvoiceController extends Controller
{
    public function subscriptions(Request $request): JsonResponse
    {
        $query = UserSubscription::with(['user:id,name,email,phone', 'plan:id,name,plan_name', 'invoice'])
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
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        $page = $query->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Subscriptions fetched successfully.',
            'data'       => collect($page->items())->map(fn (UserSubscription $sub) => [
                'id'              => $sub->id,
                'subscription_id' => $sub->subscription_id,
                'status'          => $sub->status,
                'payment_status'  => $sub->payment_status,
                'amount_paid'     => $sub->amount_paid,
                'currency'        => $sub->currency,
                'starts_at'       => $sub->starts_at?->toDateTimeString(),
                'expires_at'      => $sub->expires_at?->toDateTimeString(),
                'user'            => $sub->user ? [
                    'id'    => $sub->user->id,
                    'name'  => $sub->user->name,
                    'email' => $sub->user->email,
                    'phone' => $sub->user->phone,
                ] : null,
                'plan'            => $sub->plan ? [
                    'id'   => $sub->plan->id,
                    'name' => $sub->plan->name ?? $sub->plan->plan_name,
                ] : null,
                'invoice'         => $sub->invoice ? [
                    'id'             => $sub->invoice->id,
                    'invoice_number' => $sub->invoice->invoice_number,
                    'total_amount'   => $sub->invoice->total_amount,
                ] : null,
            ])->values(),
            'meta'       => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    public function showSubscription(int $id): JsonResponse
    {
        $sub = UserSubscription::with(['user', 'plan', 'invoice'])->findOrFail($id);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'data'       => [
                'id'              => $sub->id,
                'subscription_id' => $sub->subscription_id,
                'status'          => $sub->status,
                'payment_status'  => $sub->payment_status,
                'amount_paid'     => $sub->amount_paid,
                'currency'        => $sub->currency,
                'payment_reference'=> $sub->payment_reference,
                'razorpay_order_id'=> $sub->razorpay_order_id,
                'razorpay_payment_id'=> $sub->razorpay_payment_id,
                'starts_at'       => $sub->starts_at?->toDateTimeString(),
                'expires_at'      => $sub->expires_at?->toDateTimeString(),
                'user'            => $sub->user,
                'plan'            => $sub->plan?->toPublicArray(),
                'invoice'         => $sub->invoice?->toPublicArray(),
            ],
        ]);
    }

    public function generateInvoice(int $id): JsonResponse
    {
        $sub = UserSubscription::findOrFail($id);

        if ($sub->payment_status !== 'paid') {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'Invoice can only be generated for paid subscriptions.',
            ], 422);
        }

        $invoice = app(PlanInvoiceService::class)->ensureForSubscription($sub, 'admin');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Invoice generated successfully.',
            'data'       => $invoice->toPublicArray(),
        ]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $query = PlanInvoice::with(['user:id,name,email', 'plan:id,name,plan_name'])
            ->latest('invoice_date')
            ->latest('id');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('billing_email', 'like', "%{$q}%")
                    ->orWhere('plan_name', 'like', "%{$q}%");
            });
        }

        $page = $query->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Invoices fetched successfully.',
            'data'       => collect($page->items())->map->toPublicArray()->values(),
            'meta'       => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    public function showInvoice(int $id): JsonResponse
    {
        $invoice = PlanInvoice::findOrFail($id);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Invoice fetched successfully.',
            'data'       => $invoice->toPublicArray(),
        ]);
    }

    public function downloadInvoice(int $id): Response
    {
        $invoice = PlanInvoice::findOrFail($id);

        return app(PlanInvoicePdfService::class)->download($invoice);
    }

    public function sessionInvoices(Request $request): JsonResponse
    {
        $query = \App\Models\SessionInvoice::with(['user:id,name,email', 'mentor:id,name'])
            ->latest('invoice_date')
            ->latest('id');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('billing_email', 'like', "%{$q}%")
                    ->orWhere('booking_ref', 'like', "%{$q}%");
            });
        }

        $page = $query->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'data'       => collect($page->items())->map->toPublicArray()->values(),
            'meta'       => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
        ]);
    }

    public function downloadSessionInvoice(int $id): Response
    {
        $invoice = \App\Models\SessionInvoice::findOrFail($id);

        return app(\App\Services\SessionInvoicePdfService::class)->download($invoice);
    }
}
