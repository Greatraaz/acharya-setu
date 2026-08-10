<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\SessionInvoice;
use App\Services\SessionInvoicePdfService;

class SessionInvoiceController extends Controller
{
    public function download(SessionInvoice $invoice)
    {
        if ((int) $invoice->user_id !== (int) auth()->id()) {
            abort(403);
        }

        return app(SessionInvoicePdfService::class)->download($invoice);
    }
}
