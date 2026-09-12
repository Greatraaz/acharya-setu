<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanInvoice;
use App\Models\SessionInvoice;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(Request $request)
    {
        $tab = $request->input('tab', 'wallet');
        if (! in_array($tab, ['wallet', 'sessions', 'plans'], true)) {
            $tab = 'wallet';
        }

        $filters = $request->only([
            'type', 'status', 'role', 'category', 'search',
            'from_date', 'to_date', 'min_amount', 'max_amount', 'user_id',
            'payment_method',
        ]);

        $walletTransactions = null;
        $walletSummary = null;
        $sessionInvoices = null;
        $planInvoices = null;
        $sessionSummary = null;
        $planSummary = null;

        if ($tab === 'wallet') {
            $walletTransactions = $this->walletService->allTransactions($filters, 25);
            $walletSummary = $this->walletService->transactionSummary($filters);
        }

        if ($tab === 'sessions') {
            [$sessionInvoices, $sessionSummary] = $this->sessionInvoiceListing($request);
        }

        if ($tab === 'plans') {
            [$planInvoices, $planSummary] = $this->planInvoiceListing($request);
        }

        return view('admin.transactions.index', compact(
            'tab',
            'filters',
            'walletTransactions',
            'walletSummary',
            'sessionInvoices',
            'sessionSummary',
            'planInvoices',
            'planSummary'
        ));
    }

    public function show(WalletTransaction $transaction)
    {
        $transaction->load(['user', 'performedByAdmin', 'transactionable', 'transferPair.user']);

        return view('admin.transactions.show', compact('transaction'));
    }

    public function export(Request $request): StreamedResponse
    {
        $tab = $request->input('tab', 'wallet');
        $filters = $request->only([
            'type', 'status', 'role', 'category', 'search',
            'from_date', 'to_date', 'min_amount', 'max_amount', 'user_id',
            'payment_method',
        ]);

        $filename = 'transactions-'.$tab.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($tab, $filters, $request) {
            $out = fopen('php://output', 'w');

            if ($tab === 'sessions') {
                fputcsv($out, ['Invoice #', 'Date', 'Mentee', 'Mentor', 'Method', 'Wallet', 'Razorpay', 'Total', 'Reference', 'Status']);
                $this->sessionInvoiceQuery($request)->orderByDesc('id')->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $inv) {
                        fputcsv($out, [
                            $inv->invoice_number,
                            optional($inv->invoice_date)->format('Y-m-d'),
                            $inv->user?->name,
                            $inv->mentor?->name,
                            $inv->payment_method,
                            $inv->wallet_amount,
                            $inv->razorpay_amount,
                            $inv->total_amount,
                            $inv->payment_reference,
                            $inv->status,
                        ]);
                    }
                });
            } elseif ($tab === 'plans') {
                fputcsv($out, ['Invoice #', 'Date', 'User', 'Plan', 'Total', 'Reference', 'Status']);
                $this->planInvoiceQuery($request)->orderByDesc('id')->chunk(200, function ($rows) use ($out) {
                    foreach ($rows as $inv) {
                        fputcsv($out, [
                            $inv->invoice_number,
                            optional($inv->invoice_date)->format('Y-m-d'),
                            $inv->user?->name,
                            $inv->plan_name,
                            $inv->total_amount,
                            $inv->payment_reference,
                            $inv->status,
                        ]);
                    }
                });
            } else {
                fputcsv($out, [
                    'ID', 'Date', 'User', 'Role', 'Type', 'Category', 'Reference',
                    'Description', 'Amount', 'Balance Before', 'Balance After',
                    'Performed By', 'Status',
                ]);
                $this->walletService->filteredQuery($filters)
                    ->with(['user', 'performedByAdmin'])
                    ->orderByDesc('id')
                    ->chunk(200, function ($rows) use ($out) {
                        foreach ($rows as $txn) {
                            fputcsv($out, [
                                $txn->id,
                                $txn->created_at?->format('Y-m-d H:i:s'),
                                $txn->user?->name,
                                $txn->user?->role,
                                $txn->type,
                                $txn->category,
                                $txn->reference,
                                $txn->description,
                                $txn->amount,
                                $txn->balance_before,
                                $txn->balance_after,
                                $txn->performedByAdmin?->name ?? 'System',
                                $txn->status,
                            ]);
                        }
                    });
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function sessionInvoiceListing(Request $request): array
    {
        if (! Schema::hasTable('session_invoices')) {
            return [collect(), ['count' => 0, 'total' => 0, 'wallet' => 0, 'razorpay' => 0]];
        }

        $query = $this->sessionInvoiceQuery($request);
        $summaryRow = (clone $query)
            ->selectRaw('COUNT(*) as c, COALESCE(SUM(total_amount),0) as total, COALESCE(SUM(wallet_amount),0) as wallet, COALESCE(SUM(razorpay_amount),0) as razorpay')
            ->first();

        $summary = [
            'count'    => (int) ($summaryRow->c ?? 0),
            'total'    => (float) ($summaryRow->total ?? 0),
            'wallet'   => (float) ($summaryRow->wallet ?? 0),
            'razorpay' => (float) ($summaryRow->razorpay ?? 0),
        ];

        $invoices = $query->with(['user:id,name,email', 'mentor:id,name,email'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return [$invoices, $summary];
    }

    private function sessionInvoiceQuery(Request $request)
    {
        $query = SessionInvoice::query();

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('payment_reference', 'like', "%{$term}%")
                    ->orWhere('booking_ref', 'like', "%{$term}%")
                    ->orWhere('billing_name', 'like', "%{$term}%")
                    ->orWhere('billing_email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('to_date'));
        }

        return $query;
    }

    private function planInvoiceListing(Request $request): array
    {
        if (! Schema::hasTable('plan_invoices')) {
            return [collect(), ['count' => 0, 'total' => 0]];
        }

        $query = $this->planInvoiceQuery($request);
        $summaryRow = (clone $query)
            ->selectRaw('COUNT(*) as c, COALESCE(SUM(total_amount),0) as total')
            ->first();

        $summary = [
            'count' => (int) ($summaryRow->c ?? 0),
            'total' => (float) ($summaryRow->total ?? 0),
        ];

        $invoices = $query->with(['user:id,name,email'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return [$invoices, $summary];
    }

    private function planInvoiceQuery(Request $request)
    {
        $query = PlanInvoice::query();

        if ($request->filled('search')) {
            $term = trim((string) $request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('payment_reference', 'like', "%{$term}%")
                    ->orWhere('plan_name', 'like', "%{$term}%")
                    ->orWhere('billing_name', 'like', "%{$term}%")
                    ->orWhere('billing_email', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('to_date'));
        }

        return $query;
    }
}
