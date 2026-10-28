<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\ActivityLogger;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalletTransactionController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    public function index(Request $request)
    {
        $transactions = $this->walletService->allTransactions(
            $request->only(['type', 'search', 'from_date', 'to_date'])
        );

        return view('admin.wallet.index', compact('transactions'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['type', 'search', 'from_date', 'to_date']);
        $headers = [
            'ID', 'Date', 'User', 'Email', 'Role', 'Type', 'Reference',
            'Description', 'Amount', 'Balance Before', 'Balance After',
            'Performed By', 'Status',
        ];

        return $this->streamFilteredExport($request, 'wallet-transactions', $headers, function ($out, $isExcel) use ($filters) {
            $this->walletService->filteredQuery($filters)
                ->with(['user:id,name,email,role', 'performedByAdmin:id,name'])
                ->reorder()
                ->chunkById(200, function ($rows) use ($out, $isExcel) {
                    foreach ($rows as $txn) {
                        $signedAmount = ($txn->is_debit ? '-' : '').number_format((float) $txn->amount, 2, '.', '');
                        $this->writeExportRow($out, [
                            $txn->id,
                            optional($txn->created_at)->format('Y-m-d H:i:s'),
                            $txn->user?->name ?: '',
                            $txn->user?->email ?: '',
                            $txn->user?->role ? ucfirst($txn->user->role) : '',
                            $txn->type_label,
                            $txn->reference ?: '',
                            $txn->description ?: '',
                            $signedAmount,
                            number_format((float) $txn->balance_before, 2, '.', ''),
                            number_format((float) $txn->balance_after, 2, '.', ''),
                            $txn->performedByAdmin?->name ?? 'System',
                            ucfirst((string) $txn->status),
                        ], $isExcel);
                    }
                });
        });
    }

    public function showUser(User $user)
    {
        $transactions = $user->walletTransactions()->with('performedByAdmin')->paginate(20);
        $summary      = $user->walletSummary();

        return view('admin.wallet.show', compact('user', 'transactions', 'summary'));
    }

    /**
     * Dedicated admin flow: credit funds into a mentee wallet.
     */
    public function addFund(Request $request)
    {
        $preselectedMentee = null;
        if ($request->filled('mentee_id')) {
            $preselectedMentee = User::query()
                ->where('role', 'mentee')
                ->select('id', 'name', 'email', 'wallet_balance', 'phone', 'college')
                ->find($request->integer('mentee_id'));
        }

        $recentFunds = WalletTransaction::query()
            ->with(['user:id,name,email', 'performedByAdmin:id,name'])
            ->where('type', 'credit')
            ->whereNotNull('performed_by')
            ->where(function ($q) {
                $q->where('reference', 'like', 'ADMIN-FUND-%')
                    ->orWhere('meta->source', 'admin_add_fund');
            })
            ->whereHas('user', fn ($q) => $q->where('role', 'mentee'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalAddedToday = (float) WalletTransaction::query()
            ->where('type', 'credit')
            ->whereNotNull('performed_by')
            ->whereDate('created_at', today())
            ->where(function ($q) {
                $q->where('reference', 'like', 'ADMIN-FUND-%')
                    ->orWhere('meta->source', 'admin_add_fund');
            })
            ->whereHas('user', fn ($q) => $q->where('role', 'mentee'))
            ->sum('amount');

        return view('admin.wallet.add-fund', compact(
            'preselectedMentee',
            'recentFunds',
            'totalAddedToday'
        ));
    }

    public function storeAddFund(Request $request)
    {
        $data = $request->validate([
            'mentee_id'   => 'required|integer|exists:users,id',
            'amount'      => 'required|numeric|min:0.01|max:1000000',
            'description' => 'required|string|max:255',
        ]);

        $mentee = User::query()
            ->where('id', $data['mentee_id'])
            ->where('role', 'mentee')
            ->firstOrFail();

        try {
            $amount = round((float) $data['amount'], 2);
            $txn = $this->walletService->credit(
                $mentee,
                $amount,
                $data['description'],
                [
                    'reference'    => 'ADMIN-FUND-' . strtoupper(Str::random(10)),
                    'performed_by' => Auth::id(),
                    'meta'         => [
                        'source' => 'admin_add_fund',
                        'reason' => $data['description'],
                    ],
                ]
            );

            ActivityLogger::record(
                'wallet_add_fund',
                Auth::user()->name . " added ₹{$amount} to mentee wallet: {$mentee->name}",
                'payments',
                'success'
            );

            return redirect()
                ->route('admin.wallet.add-fund', ['mentee_id' => $mentee->id])
                ->with('success', "₹" . number_format($amount, 2) . " added to {$mentee->name}'s wallet. New balance: ₹" . number_format((float) $txn->balance_after, 2) . '.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function users(Request $request)
    {
        $type = $request->query('type', 'customer');
        $search = trim((string) $request->query('search', ''));

        $query = User::query()->select('id', 'name', 'email', 'wallet_balance', 'role', 'phone', 'college');

        if ($type === 'admin') {
            $query->where('role', 'admin');
        } elseif ($type === 'mentee') {
            $query->where('role', 'mentee');
        } else {
            // "customer" in the UI = mentees + mentors
            $query->whereIn('role', ['mentee', 'mentor']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('name')->limit(50)->get()
        );
    }

    public function adjust(Request $request, string $type, int $id)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'action'      => 'required|in:credit,debit',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ], [
            'amount.min'     => 'Amount must be greater than zero. A negative adjustment is not allowed.',
            'amount.numeric' => 'Enter a valid amount. A negative adjustment is not allowed.',
        ]);

        $user = $this->resolveWalletUser($type, $id);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            ActivityLogger::record(
                'wallet_adjustment_rejected',
                "Rejected wallet adjustment for {$user->name}. {$message}",
                'wallet',
                'warning'
            );

            return back()->withInput()->withErrors($validator)->with('error', $message);
        }

        $amount = round((float) $request->amount, 2);

        if ($request->action === 'debit' && $amount > (float) $user->wallet_balance) {
            $message = 'This debit would make the wallet negative. Available balance is ₹'
                .number_format((float) $user->wallet_balance, 2)
                .'. No ledger entry was created.';
            ActivityLogger::record(
                'wallet_adjustment_rejected',
                "Rejected debit of ₹{$amount} for {$user->name} ({$user->email}). {$message}",
                'wallet',
                'warning'
            );

            return back()->withInput()->with('error', $message);
        }

        try {
            $this->walletService->{$request->action}(
                $user,
                $amount,
                $request->description,
                ['performed_by' => Auth::id()]
            );

            ActivityLogger::record(
                'wallet_adjusted',
                ucfirst($request->action)." ₹{$amount} on {$user->name}'s wallet. {$request->description}",
                'wallet',
                'info'
            );

            return back()->with('success', 'Wallet adjusted successfully.');
        } catch (\Throwable $e) {
            ActivityLogger::record(
                'wallet_adjustment_rejected',
                "Rejected {$request->action} of ₹{$amount} for {$user->name}. {$e->getMessage()}",
                'wallet',
                'warning'
            );

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'sender_type'   => 'required|in:customer,admin',
            'sender_id'     => 'required|integer',
            'receiver_type' => 'required|in:customer,admin',
            'receiver_id'   => 'required|integer',
            'amount'        => 'required|numeric|min:0.01',
            'note'          => 'nullable|string|max:255',
        ]);

        $sender   = $this->resolveWalletUser($request->sender_type, $request->sender_id);
        $receiver = $this->resolveWalletUser($request->receiver_type, $request->receiver_id);

        try {
            $this->walletService->transfer(
                $sender,
                $receiver,
                (float) $request->amount,
                $request->note,
                ['performed_by' => Auth::id()]
            );

            return back()->with('success', "₹{$request->amount} transferred successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function resolveWalletUser(string $type, int $id): User
    {
        $query = User::query()->where('id', $id);

        if ($type === 'admin') {
            $query->where('role', 'admin');
        } else {
            $query->whereIn('role', ['mentee', 'mentor']);
        }

        return $query->firstOrFail();
    }

    /**
     * @param  callable(resource, bool): void  $writeRows
     */
    private function streamFilteredExport(Request $request, string $basename, array $headers, callable $writeRows): StreamedResponse
    {
        $isExcel = $request->input('format') === 'excel';
        $filename = $basename.'-'.now()->format('Ymd-His').($isExcel ? '.xls' : '.csv');
        $contentType = $isExcel
            ? 'application/vnd.ms-excel; charset=UTF-8'
            : 'text/csv; charset=UTF-8';

        return response()->streamDownload(function () use ($headers, $writeRows, $isExcel) {
            $out = fopen('php://output', 'w');

            if ($isExcel) {
                fwrite($out, '<?xml version="1.0" encoding="UTF-8"?>'."\n");
                fwrite($out, '<?mso-application progid="Excel.Sheet"?>'."\n");
                fwrite($out, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Export"><Table>'."\n");
                $this->writeExportRow($out, $headers, true);
                $writeRows($out, true);
                fwrite($out, '</Table></Worksheet></Workbook>');
            } else {
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, $headers);
                $writeRows($out, false);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => $contentType,
        ]);
    }

    /**
     * @param  resource  $out
     * @param  list<mixed>  $values
     */
    private function writeExportRow($out, array $values, bool $isExcel): void
    {
        if (! $isExcel) {
            fputcsv($out, $values);

            return;
        }

        fwrite($out, '<Row>');
        foreach ($values as $value) {
            $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            fwrite($out, '<Cell><Data ss:Type="String">'.$text.'</Data></Cell>');
        }
        fwrite($out, "</Row>\n");
    }
}
