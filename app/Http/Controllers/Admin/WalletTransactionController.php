<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function showUser(User $user)
    {
        $transactions = $user->walletTransactions()->with('performedByAdmin')->paginate(20);
        $summary      = $user->walletSummary();

        return view('admin.wallet.show', compact('user', 'transactions', 'summary'));
    }

    public function users(Request $request)
    {
        $type = $request->query('type', 'customer');

        $query = User::query()->select('id', 'name', 'email', 'wallet_balance', 'role');

        if ($type === 'admin') {
            $query->where('role', 'admin');
        } else {
            // "customer" in the UI = mentees + mentors
            $query->whereIn('role', ['mentee', 'mentor']);
        }

        return response()->json(
            $query->orderBy('name')->get()
        );
    }

    public function adjust(Request $request, string $type, int $id)
    {
        $request->validate([
            'action'      => 'required|in:credit,debit',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $user = $this->resolveWalletUser($type, $id);

        try {
            $this->walletService->{$request->action}(
                $user,
                (float) $request->amount,
                $request->description,
                ['performed_by' => Auth::id()]
            );

            return back()->with('success', 'Wallet adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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
}
