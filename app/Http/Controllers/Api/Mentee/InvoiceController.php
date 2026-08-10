<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\PlanInvoice;
use App\Models\UserSubscription;
use App\Services\PlanInvoicePdfService;
use App\Services\PlanInvoiceService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $invoices = PlanInvoice::with(['plan:id,name,plan_name'])
            ->where('user_id', $request->user()->id)
            ->latest('invoice_date')
            ->latest('id')
            ->get()
            ->map->toPublicArray()
            ->values();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Invoices fetched successfully.',
            'data'       => $invoices,
            'total'      => $invoices->count(),
        ]);
    }

    public function show(Request $request, int $invoice): JsonResponse
    {
        $record = PlanInvoice::where('user_id', $request->user()->id)
            ->findOrFail($invoice);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Invoice fetched successfully.',
            'data'       => $record->toPublicArray(),
        ]);
    }

    /**
     * Generate (or return existing) invoice for a paid subscription.
     * Accepts numeric id or subscription_id code (SUB-...).
     */
    public function generate(Request $request, string $subscription): JsonResponse
    {
        $sub = UserSubscription::where('user_id', $request->user()->id)
            ->where(function ($q) use ($subscription) {
                $q->where('subscription_id', $subscription);
                if (ctype_digit($subscription)) {
                    $q->orWhere('id', (int) $subscription);
                }
            })
            ->first();

        if (! $sub) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Subscription not found.',
            ], 404);
        }

        if ($sub->payment_status !== 'paid') {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'Invoice can only be generated for paid subscriptions.',
            ], 422);
        }

        $invoice = app(PlanInvoiceService::class)->ensureForSubscription($sub, 'user');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Invoice generated successfully.',
            'data'       => $invoice->toPublicArray(),
        ]);
    }

    public function download(Request $request, int $invoice): Response
    {
        $record = PlanInvoice::where('user_id', $request->user()->id)
            ->findOrFail($invoice);

        return app(PlanInvoicePdfService::class)->download($record);
    }
}
