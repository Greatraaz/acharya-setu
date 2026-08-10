<?php

namespace App\Services;

use App\Models\SessionInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class SessionInvoicePdfService
{
    public function filename(SessionInvoice $invoice): string
    {
        $safe = preg_replace('/[^A-Za-z0-9\-_]/', '-', $invoice->invoice_number) ?: 'session-invoice';

        return $safe.'.pdf';
    }

    public function download(SessionInvoice $invoice): Response
    {
        $invoice->loadMissing(['user', 'mentor', 'session']);

        return Pdf::loadView('invoices.session-pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4')->download($this->filename($invoice));
    }
}
