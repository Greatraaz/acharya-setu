<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionInvoice;
use App\Services\SessionInvoicePdfService;
use App\Services\SessionInvoiceService;
use App\Models\ConsultationSession;

class SessionInvoiceController extends Controller
{
    public function download(SessionInvoice $invoice)
    {
        return app(SessionInvoicePdfService::class)->download($invoice);
    }

    public function generate(ConsultationSession $session)
    {
        $invoice = app(SessionInvoiceService::class)->ensureForSession($session, 'admin');

        if (! $invoice) {
            return back()->with('error', 'Invoice can only be generated for paid/waived sessions.');
        }

        return redirect()->route('admin.session-invoices.download', $invoice)
            ->with('success', 'Invoice ready: '.$invoice->invoice_number);
    }
}
