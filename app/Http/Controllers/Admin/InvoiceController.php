<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanInvoice;
use App\Services\PlanInvoicePdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PlanInvoice::with(['user', 'plan', 'subscription'])
            ->latest('invoice_date')
            ->latest('id');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('billing_name', 'like', "%{$q}%")
                    ->orWhere('billing_email', 'like', "%{$q}%")
                    ->orWhere('plan_name', 'like', "%{$q}%");
            });
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(PlanInvoice $invoice)
    {
        $invoice->load(['user', 'plan', 'subscription']);

        return view('invoices.print', [
            'invoice'     => $invoice,
            'print'       => false,
            'backUrl'     => route('admin.subscriptions.show', $invoice->user_subscription_id),
            'printUrl'    => route('admin.invoices.print', $invoice),
            'downloadUrl' => route('admin.invoices.download', $invoice),
        ]);
    }

    public function print(PlanInvoice $invoice)
    {
        $invoice->load(['user', 'plan', 'subscription']);

        return view('invoices.print', [
            'invoice'     => $invoice,
            'print'       => true,
            'backUrl'     => route('admin.invoices.show', $invoice),
            'printUrl'    => route('admin.invoices.print', $invoice),
            'downloadUrl' => route('admin.invoices.download', $invoice),
        ]);
    }

    public function download(PlanInvoice $invoice)
    {
        return app(PlanInvoicePdfService::class)->download($invoice);
    }
}
