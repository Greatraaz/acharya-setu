<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\PlanInvoice;
use App\Models\UserSubscription;
use App\Services\PlanInvoicePdfService;
use App\Services\PlanInvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(PlanInvoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        return view('frontend.mentee.invoice', [
            'invoice'     => $invoice->load(['user', 'plan', 'subscription']),
            'print'       => false,
            'downloadUrl' => route('mentee.invoices.download', $invoice),
        ]);
    }

    public function print(PlanInvoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        return view('frontend.mentee.invoice', [
            'invoice'     => $invoice->load(['user', 'plan', 'subscription']),
            'print'       => true,
            'downloadUrl' => route('mentee.invoices.download', $invoice),
        ]);
    }

    public function download(PlanInvoice $invoice)
    {
        $this->authorizeInvoice($invoice);

        return app(PlanInvoicePdfService::class)->download($invoice);
    }

    public function generate(Request $request, $subscription)
    {
        $sub = UserSubscription::where('user_id', auth()->id())
            ->where(function ($q) use ($subscription) {
                $q->where('id', $subscription)
                    ->orWhere('subscription_id', $subscription);
            })
            ->firstOrFail();

        if ($sub->payment_status !== 'paid') {
            return back()->with('error', 'Invoice is available only after successful payment.');
        }

        $invoice = app(PlanInvoiceService::class)->ensureForSubscription($sub, 'user');

        return redirect()->route('mentee.invoices.show', $invoice)
            ->with('success', 'Invoice ready.');
    }

    private function authorizeInvoice(PlanInvoice $invoice): void
    {
        if ((int) $invoice->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
