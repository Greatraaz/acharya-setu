<?php

namespace App\Services;

use App\Models\SessionInvoice;
use Illuminate\Http\Response as IlluminateResponse;
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

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.session-pdf', [
                'invoice' => $invoice,
            ])->setPaper('a4')->download($this->filename($invoice));
        }

        return $this->htmlFallback($invoice);
    }

    private function htmlFallback(SessionInvoice $invoice): IlluminateResponse
    {
        return response()
            ->view('invoices.session-pdf', ['invoice' => $invoice])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
