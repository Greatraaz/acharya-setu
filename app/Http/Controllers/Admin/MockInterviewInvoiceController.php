<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MockInterviewInvoice;
use App\Models\MockInterviewRequest;
use App\Services\MockInterviewInvoicePdfService;
use App\Services\MockInterviewInvoiceService;

class MockInterviewInvoiceController extends Controller
{
    public function download(MockInterviewInvoice $invoice)
    {
        return app(MockInterviewInvoicePdfService::class)->download($invoice);
    }

    public function generate(MockInterviewRequest $mockInterview)
    {
        $invoice = app(MockInterviewInvoiceService::class)->ensureForRequest($mockInterview, 'admin');

        if (! $invoice) {
            return back()->with('error', 'Invoice can only be generated for paid or plan-covered bookings.');
        }

        return redirect()->route('admin.mock-interview-invoices.download', $invoice)
            ->with('success', 'Invoice ready: '.$invoice->invoice_number);
    }
}
