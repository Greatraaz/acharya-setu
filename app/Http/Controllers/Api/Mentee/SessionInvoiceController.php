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
        $invoices = SessionInvoice::where('user_id', $request->user()->id)
            ->latest('invoice_date')
            ->latest('id')
            ->get()
            ->map->toPublicArray()
            ->values();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Session invoices fetched successfully.',
            'data'       => $invoices,
            'total'      => $invoices->count(),
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
