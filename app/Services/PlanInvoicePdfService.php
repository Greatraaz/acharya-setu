<?php

namespace App\Services;

use App\Models\PlanInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PlanInvoicePdfService
{
    public function filename(PlanInvoice $invoice): string
    {
        $safe = preg_replace('/[^A-Za-z0-9\-_]/', '-', $invoice->invoice_number) ?: 'invoice';

        return $safe.'.pdf';
    }

    public function download(PlanInvoice $invoice): Response
    {
        $invoice->loadMissing(['user', 'plan', 'subscription']);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        return $pdf->download($this->filename($invoice));
    }

    public function stream(PlanInvoice $invoice): Response
    {
        $invoice->loadMissing(['user', 'plan', 'subscription']);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        return $pdf->stream($this->filename($invoice));
    }

    public function raw(PlanInvoice $invoice): string
    {
        $invoice->loadMissing(['user', 'plan', 'subscription']);

        return Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4')->output();
    }
}
