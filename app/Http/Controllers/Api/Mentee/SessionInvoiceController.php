<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\SessionInvoice;
use App\Services\SessionInvoicePdfService;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\Response;

class SessionInvoiceController extends Controller
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

        $paginator = SessionInvoice::where('user_id', $request->user()->id)
            ->when($search !== '', fn ($q) => $q->where('invoice_number', 'like', '%'.$search.'%'))
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
            'message'    => 'Session invoices fetched successfully.',
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
        $record = SessionInvoice::where('user_id', $request->user()->id)->findOrFail($invoice);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'data'       => $record->toPublicArray(),
        ]);
    }

    public function download(Request $request, int $invoice): Response
    {
        $record = SessionInvoice::where('user_id', $request->user()->id)->findOrFail($invoice);

        return app(SessionInvoicePdfService::class)->download($record);
    }
}
