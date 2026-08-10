<?php

namespace App\Services;

use App\Models\PlanInvoice;
use Illuminate\Http\Response as IlluminateResponse;
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

        if ($pdf = $this->makePdf($invoice)) {
            return $pdf->download($this->filename($invoice));
        }

        return $this->htmlFallback($invoice);
    }

    public function stream(PlanInvoice $invoice): Response
    {
        $invoice->loadMissing(['user', 'plan', 'subscription']);

        if ($pdf = $this->makePdf($invoice)) {
            return $pdf->stream($this->filename($invoice));
        }

        return $this->htmlFallback($invoice);
    }

    public function raw(PlanInvoice $invoice): string
    {
        $invoice->loadMissing(['user', 'plan', 'subscription']);

        if ($pdf = $this->makePdf($invoice)) {
            return $pdf->output();
        }

        return view('invoices.print', ['invoice' => $invoice])->render();
    }

    private function makePdf(PlanInvoice $invoice): mixed
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return null;
        }

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4');
    }

    private function htmlFallback(PlanInvoice $invoice): IlluminateResponse
    {
        return response()
            ->view('invoices.print', ['invoice' => $invoice])
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
