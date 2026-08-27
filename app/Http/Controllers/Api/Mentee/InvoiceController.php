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
        $data = $request->validate([
            'search'    => 'nullable|string|max:100',
            'status'    => 'nullable|string|max:50',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'per_page'  => 'nullable|integer|min:1|max:100',
        ]);

        $search  = trim((string) ($data['search'] ?? ''));
        $perPage = $data['per_page'] ?? 20;

        $paginator = PlanInvoice::with(['plan:id,name,plan_name'])
            ->where('user_id', $request->user()->id)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', '%'.$search.'%')
                        ->orWhereHas('plan', function ($p) use ($search) {
                            $p->where('name', 'like', '%'.$search.'%')
                                ->orWhere('plan_name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when(! empty($data['status']), fn ($q) => $q->where('status', $data['status']))
            ->when(! empty($data['date_from']), fn ($q) => $q->whereDate('invoice_date', '>=', $data['date_from']))
            ->when(! empty($data['date_to']), fn ($q) => $q->whereDate('invoice_date', '<=', $data['date_to']))
            ->latest('invoice_date')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $invoices = collect($paginator->items())
            ->map->toPublicArray()
            ->values();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Invoices fetched successfully.',
            'data'       => $invoices,
            'meta'       => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'filters'    => [
                'search'    => $search !== '' ? $search : null,
                'status'    => $data['status'] ?? null,
                'date_from' => $data['date_from'] ?? null,
                'date_to'   => $data['date_to'] ?? null,
            ],
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
