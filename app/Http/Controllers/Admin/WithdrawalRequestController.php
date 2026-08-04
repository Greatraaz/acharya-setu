<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['user:id,name,email,role,wallet_balance', 'processedBy:id,name'])
            ->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->query('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('bank_details', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        $stats = [
            'pending'  => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->count(),
            'paid'     => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PAID)->count(),
            'rejected' => WithdrawalRequest::where('status', WithdrawalRequest::STATUS_REJECTED)->count(),
            'pending_amount' => (float) WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->sum('amount'),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        $data = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($withdrawal, $data) {
                $withdrawal = WithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);

                if (! $withdrawal->isPending()) {
                    throw new \RuntimeException('This withdrawal request has already been processed.');
                }

                /** @var User $user */
                $user = User::query()->lockForUpdate()->findOrFail($withdrawal->user_id);

                if ((float) $user->wallet_balance < (float) $withdrawal->amount) {
                    throw new \RuntimeException(
                        "Insufficient mentor balance (₹{$user->wallet_balance}). Cannot approve ₹{$withdrawal->amount}."
                    );
                }

                $txn = $user->debitWallet(
                    (float) $withdrawal->amount,
                    'Withdrawal payout',
                    [
                        'reference'    => 'WD-'.$withdrawal->id,
                        'performed_by' => auth()->id(),
                        'meta'         => [
                            'source'                 => 'withdrawal_payout',
                            'withdrawal_request_id'  => $withdrawal->id,
                            'bank_details'           => $withdrawal->bank_details,
                        ],
                    ]
                );

                $withdrawal->update([
                    'status'                => WithdrawalRequest::STATUS_PAID,
                    'admin_note'            => $data['admin_note'] ?? $withdrawal->admin_note,
                    'processed_by'          => auth()->id(),
                    'processed_at'          => now(),
                    'wallet_transaction_id' => $txn->id,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Withdrawal approved and wallet debited.');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $data = $request->validate([
            'admin_note' => 'required|string|min:5|max:500',
        ]);

        try {
            DB::transaction(function () use ($withdrawal, $data) {
                $withdrawal = WithdrawalRequest::query()->lockForUpdate()->findOrFail($withdrawal->id);

                if (! $withdrawal->isPending()) {
                    throw new \RuntimeException('This withdrawal request has already been processed.');
                }

                $withdrawal->update([
                    'status'       => WithdrawalRequest::STATUS_REJECTED,
                    'admin_note'   => $data['admin_note'],
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Withdrawal request rejected.');
    }
}
