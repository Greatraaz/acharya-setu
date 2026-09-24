<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\CareerServiceInvoice;
use App\Services\CareerServiceInvoicePdfService;

class CareerServiceInvoiceController extends Controller
{
    public function download(CareerServiceInvoice $invoice)
    {
        if ((int) $invoice->user_id !== (int) auth()->id()) {
            abort(403);
        }

        return app(CareerServiceInvoicePdfService::class)->download($invoice);
    }
}
