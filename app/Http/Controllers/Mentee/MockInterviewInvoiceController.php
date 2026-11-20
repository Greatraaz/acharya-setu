<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\MockInterviewInvoice;
use App\Services\MockInterviewInvoicePdfService;

class MockInterviewInvoiceController extends Controller
{
    public function download(MockInterviewInvoice $invoice)
    {
        if ((int) $invoice->user_id !== (int) auth()->id()) {
            abort(403);
        }

        return app(MockInterviewInvoicePdfService::class)->download($invoice);
    }
}
