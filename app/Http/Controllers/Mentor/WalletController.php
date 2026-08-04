<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class WalletController extends Controller
{
    private const PLATFORM_FEE_RATE = 0.20;

    public function index()
    {
        $user = auth()->user();

        // Settle any completed+paid sessions that haven't been credited yet.
        ConsultationSession::where('mentor_id', $user->id)
            ->where('status', ConsultationSession::STATUS_COMPLETED)
            ->where('payment_status', 'paid')
            ->where('amount', '>', 0)
            ->orderBy('id')
            ->each(fn (ConsultationSession $session) => $session->settleMentorPayout());

        $earningsQuery = WalletTransaction::where('user_id', $user->id)
            ->whereIn('type', ['credit', 'transfer_in', 'refund'])
            ->where('status', 'completed');

        $transactions = (clone $earningsQuery)
            ->with(['transactionable' => function ($morphTo) {
                $morphTo->morphWith([
                    ConsultationSession::class => ['mentee:id,name'],
                ]);
            }])
            ->latest()
            ->paginate(20);

        $thisMonthQuery = (clone $earningsQuery)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $pendingHold = (float) WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->sum('amount');

        $walletBalance = (float) ($user->fresh()->wallet_balance ?? 0);

        $stats = [
            'balance'             => $walletBalance,
            'pending_hold'        => $pendingHold,
            'available'           => max(0, $walletBalance - $pendingHold),
            'total_earnings'      => (float) (clone $earningsQuery)->sum('amount'),
            'this_month_earnings' => (float) (clone $thisMonthQuery)->sum('amount'),
            'this_month_sessions' => (int) (clone $thisMonthQuery)
                ->where('transactionable_type', ConsultationSession::class)
                ->count(),
        ];

        $withdrawals = WithdrawalRequest::where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'withdrawals_page');

        $platformFeeRate = self::PLATFORM_FEE_RATE;

        return view('frontend.mentors.wallet', compact('transactions', 'stats', 'platformFeeRate', 'withdrawals'));
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount'       => 'required|numeric|min:500',
            'bank_details' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        $pendingHold = (float) WithdrawalRequest::where('user_id', $user->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->sum('amount');
        $available = max(0, (float) $user->wallet_balance - $pendingHold);

        if ($available < (float) $data['amount']) {
            $message = 'Insufficient available balance. ₹'.number_format($pendingHold, 0).' is already in pending withdrawal requests.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['amount' => $message])->withInput();
        }

        if (
            Schema::hasColumn('users', 'bank_details')
            && (empty($user->bank_details) || $user->bank_details !== $data['bank_details'])
        ) {
            $user->forceFill(['bank_details' => $data['bank_details']])->save();
        }

        WithdrawalRequest::create([
            'user_id'      => $user->id,
            'amount'       => $data['amount'],
            'bank_details' => $data['bank_details'],
            'status'       => WithdrawalRequest::STATUS_PENDING,
        ]);

        $message = 'Withdrawal request submitted. Funds will be transferred within 2 business days after admin approval.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }
}
