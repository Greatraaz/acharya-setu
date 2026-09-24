<?php

namespace App\Services;

use App\Models\CareerServiceInvoice;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Response;

class CareerServiceInvoicePdfService
{
    public function filename(CareerServiceInvoice $invoice): string
    {
        $safe = preg_replace('/[^A-Za-z0-9\-_]/', '-', $invoice->invoice_number) ?: 'career-invoice';

        return $safe.'.pdf';
    }

    public function download(CareerServiceInvoice $invoice): Response
    {
        $invoice->loadMissing(['user', 'request']);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.career-service-pdf', [
                'invoice' => $invoice,
            ])->setPaper('a4')->download($this->filename($invoice));
        }

        return $this->htmlFallback($invoice);
    }

    private function htmlFallback(CareerServiceInvoice $invoice): IlluminateResponse
    {
        return response()
            ->view('invoices.career-service-pdf', ['invoice' => $invoice])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
