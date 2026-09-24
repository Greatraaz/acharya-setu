<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CareerServiceInvoice;
use App\Models\CareerServiceRequest;
use App\Services\CareerServiceInvoicePdfService;
use App\Services\CareerServiceInvoiceService;

class CareerServiceInvoiceController extends Controller
{
    public function download(CareerServiceInvoice $invoice)
    {
        return app(CareerServiceInvoicePdfService::class)->download($invoice);
    }

    public function generate(CareerServiceRequest $careerService)
    {
        $invoice = app(CareerServiceInvoiceService::class)->ensureForRequest($careerService, 'admin');

        if (! $invoice) {
            return back()->with('error', 'Invoice can only be generated for paid or plan-included requests.');
        }

        return redirect()->route('admin.career-service-invoices.download', $invoice)
            ->with('success', 'Invoice ready: '.$invoice->invoice_number);
    }
}
