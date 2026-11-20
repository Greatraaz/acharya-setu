<?php

namespace App\Services;

use App\Models\MockInterviewInvoice;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpFoundation\Response;

class MockInterviewInvoicePdfService
{
    public function filename(MockInterviewInvoice $invoice): string
    {
        $safe = preg_replace('/[^A-Za-z0-9\-_]/', '-', $invoice->invoice_number) ?: 'mock-invoice';

        return $safe.'.pdf';
    }

    public function download(MockInterviewInvoice $invoice): Response
    {
        $invoice->loadMissing(['user', 'request']);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.mock-interview-pdf', [
                'invoice' => $invoice,
            ])->setPaper('a4')->download($this->filename($invoice));
        }

        return response()
            ->view('invoices.mock-interview-pdf', ['invoice' => $invoice])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
