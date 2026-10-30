<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MentorEarningController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'activity');
        $sort = $request->query('sort', 'unpaid');
        $search = trim((string) $request->query('search', ''));

        $query = User::query()
            ->mentors()
            ->select('users.id', 'users.name', 'users.email', 'users.avatar_url', 'users.role', 'users.wallet_balance', 'users.mentor_status', 'users.rate_per_minute', 'users.bank_details')
            ->leftJoinSub($this->sessionEarningsSub(), 'earn', 'earn.user_id', '=', 'users.id')
            ->leftJoinSub($this->payoutSub(), 'payout', 'payout.user_id', '=', 'users.id')
            ->addSelect([
                DB::raw('COALESCE(earn.earned, 0) as earned'),
                DB::raw('COALESCE(earn.gross, 0) as gross'),
                DB::raw('COALESCE(earn.platform_fee, 0) as platform_fee'),
                DB::raw('COALESCE(earn.mentee_paid, 0) as mentee_paid'),
                DB::raw('COALESCE(earn.session_count, 0) as session_count'),
                DB::raw('COALESCE(payout.paid_out, 0) as paid_out'),
                DB::raw('COALESCE(payout.pending_out, 0) as pending_out'),
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        match ($filter) {
            'unpaid' => $query->where('users.wallet_balance', '>', 0),
            'pending' => $query->where('payout.pending_out', '>', 0),
            'paid' => $query->where('payout.paid_out', '>', 0),
            'all' => null,
            default => $query->where(function ($q) {
                $q->where('users.wallet_balance', '>', 0)
                    ->orWhere('earn.earned', '>', 0)
                    ->orWhere('payout.paid_out', '>', 0)
                    ->orWhere('payout.pending_out', '>', 0);
            }),
        };

        match ($sort) {
            'earned' => $query->orderByDesc('earned'),
            'paid' => $query->orderByDesc('paid_out'),
            'name' => $query->orderBy('users.name'),
            default => $query->orderByDesc('users.wallet_balance')->orderByDesc('earned'),
        };

        $mentors = $query->paginate(20)->withQueryString();
        $stats = $this->platformStats();

        return view('admin.earnings.index', compact('mentors', 'stats', 'filter', 'sort', 'search'));
    }

    public function show(User $mentor)
    {
        abort_unless($mentor->isMentor(), 404);

        $summary = $this->mentorSummary($mentor);

        $earnings = WalletTransaction::query()
            ->where('user_id', $mentor->id)
            ->where('status', 'completed')
            ->where($this->sessionEarningConstraint())
            ->with(['transactionable' => function ($morphTo) {
                $morphTo->morphWith([
                    ConsultationSession::class => ['mentee:id,name,email'],
                ]);
            }])
            ->latest()
            ->paginate(50, ['*'], 'earnings_page');

        $payouts = WithdrawalRequest::query()
            ->where('user_id', $mentor->id)
            ->with('processedBy:id,name')
            ->latest()
            ->paginate(50, ['*'], 'payouts_page');

        return view('admin.earnings.show', compact('mentor', 'summary', 'earnings', 'payouts'));
    }

    public function pay(Request $request, User $mentor)
    {
        abort_unless($mentor->isMentor(), 404);

        $data = $request->validate([
            'amount'       => 'required|numeric|min:1',
            'bank_details' => 'required|string|max:255',
            'payment_ref'  => 'nullable|string|max:80',
            'admin_note'   => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($mentor, $data) {
                $mentor = User::query()->lockForUpdate()->findOrFail($mentor->id);

                $pendingHold = (float) WithdrawalRequest::query()
                    ->where('user_id', $mentor->id)
                    ->where('status', WithdrawalRequest::STATUS_PENDING)
                    ->sum('amount');
                $available = max(0, (float) $mentor->wallet_balance - $pendingHold);
                $amount = round((float) $data['amount'], 2);

                if ($amount > $available) {
                    throw new \RuntimeException(
                        'Only ₹'.number_format($available, 2).' is available to pay'
                        .($pendingHold > 0 ? ' (₹'.number_format($pendingHold, 2).' held in pending requests).' : '.')
                    );
                }

                $note = trim(implode(' ', array_filter([
                    ! empty($data['payment_ref']) ? 'Ref: '.$data['payment_ref'].'.' : null,
                    $data['admin_note'] ?? null,
                ])));

                $withdrawal = WithdrawalRequest::create([
                    'user_id'      => $mentor->id,
                    'amount'       => $amount,
                    'bank_details' => $data['bank_details'],
                    'status'       => WithdrawalRequest::STATUS_PENDING,
                    'admin_note'   => $note !== '' ? $note : null,
                ]);

                $txn = $mentor->debitWallet(
                    $amount,
                    'Withdrawal payout',
                    [
                        'reference'    => 'WD-'.$withdrawal->id,
                        'performed_by' => auth()->id(),
                        'meta'         => [
                            'source'                => 'withdrawal_payout',
                            'withdrawal_request_id' => $withdrawal->id,
                            'bank_details'          => $data['bank_details'],
                            'payment_ref'           => $data['payment_ref'] ?? null,
                            'initiated_by'          => 'admin',
                        ],
                    ]
                );

                $withdrawal->update([
                    'status'                => WithdrawalRequest::STATUS_PAID,
                    'processed_by'          => auth()->id(),
                    'processed_at'          => now(),
                    'wallet_transaction_id' => $txn->id,
                ]);

                app(NotificationService::class)->send(
                    $mentor,
                    'withdrawal_paid',
                    'Payout sent',
                    '₹'.number_format($amount, 2).' has been paid to your account.',
                    ['withdrawal_request_id' => $withdrawal->id, 'amount' => $amount],
                    app(NotificationService::class)->roleUrl($mentor, 'wallet')
                );
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Paid ₹'.number_format((float) $data['amount'], 2).' to '.$mentor->name.'.');
    }

    private function sessionEarningsSub()
    {
        return WalletTransaction::query()
            ->select('user_id')
            ->selectRaw('SUM(amount) as earned')
            ->selectRaw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.gross_amount')) AS DECIMAL(12,2)), 0)) as gross")
            ->selectRaw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.platform_fee')) AS DECIMAL(12,2)), 0)) as platform_fee")
            ->selectRaw("SUM(COALESCE(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.mentee_paid')) AS DECIMAL(12,2)), 0)) as mentee_paid")
            ->selectRaw('COUNT(*) as session_count')
            ->where('status', 'completed')
            ->where($this->sessionEarningConstraint())
            ->groupBy('user_id');
    }

    private function payoutSub()
    {
        return WithdrawalRequest::query()
            ->select('user_id')
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_out")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_out")
            ->groupBy('user_id');
    }

    private function sessionEarningConstraint(): \Closure
    {
        return function ($q) {
            $q->whereIn('type', ['credit', 'transfer_in'])
                ->where(function ($inner) {
                    $inner->where('meta->source', 'session_mentor_payout')
                        ->orWhere('reference', 'like', 'SES-EARN-%')
                        ->orWhere('description', 'like', 'Session earning%');
                });
        };
    }

    private function platformStats(): array
    {
        $earn = WalletTransaction::query()
            ->where('status', 'completed')
            ->where($this->sessionEarningConstraint())
            ->selectRaw('COALESCE(SUM(amount), 0) as earned')
            ->selectRaw("COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.platform_fee')) AS DECIMAL(12,2))), 0) as platform_fee")
            ->selectRaw("COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.gross_amount')) AS DECIMAL(12,2))), 0) as gross")
            ->selectRaw('COUNT(*) as session_count')
            ->first();

        $payout = WithdrawalRequest::query()
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as paid_out")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_out")
            ->first();

        $unpaid = (float) User::query()->mentors()->sum('wallet_balance');

        return [
            'earned'        => (float) ($earn->earned ?? 0),
            'gross'         => (float) ($earn->gross ?? 0),
            'platform_fee'  => (float) ($earn->platform_fee ?? 0),
            'session_count' => (int) ($earn->session_count ?? 0),
            'paid_out'      => (float) ($payout->paid_out ?? 0),
            'pending_out'   => (float) ($payout->pending_out ?? 0),
            'unpaid'        => $unpaid,
        ];
    }

    private function mentorSummary(User $mentor): array
    {
        $earn = WalletTransaction::query()
            ->where('user_id', $mentor->id)
            ->where('status', 'completed')
            ->where($this->sessionEarningConstraint())
            ->selectRaw('COALESCE(SUM(amount), 0) as earned')
            ->selectRaw("COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.platform_fee')) AS DECIMAL(12,2))), 0) as platform_fee")
            ->selectRaw("COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.gross_amount')) AS DECIMAL(12,2))), 0) as gross")
            ->selectRaw("COALESCE(SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.mentee_paid')) AS DECIMAL(12,2))), 0) as mentee_paid")
            ->selectRaw('COUNT(*) as session_count')
            ->first();

        $paid = (float) WithdrawalRequest::query()
            ->where('user_id', $mentor->id)
            ->where('status', WithdrawalRequest::STATUS_PAID)
            ->sum('amount');
        $pending = (float) WithdrawalRequest::query()
            ->where('user_id', $mentor->id)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->sum('amount');

        $balance = (float) $mentor->wallet_balance;

        return [
            'earned'        => (float) ($earn->earned ?? 0),
            'gross'         => (float) ($earn->gross ?? 0),
            'platform_fee'  => (float) ($earn->platform_fee ?? 0),
            'mentee_paid'   => (float) ($earn->mentee_paid ?? 0),
            'session_count' => (int) ($earn->session_count ?? 0),
            'paid_out'      => $paid,
            'pending_out'   => $pending,
            'balance'       => $balance,
            'available'     => max(0, $balance - $pending),
        ];
    }
}
