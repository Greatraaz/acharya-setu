<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletTransactionController extends Controller
{
    public function __construct(private WalletService $walletService) {}

    // Global transaction log
    public function index(Request $request)
    {
        $transactions = $this->walletService->allTransactions($request->only(['type', 'search', 'from_date', 'to_date']));

        return view('admin.wallet.index', compact('transactions'));
    }

    // Customer wallet detail
    public function showUser(User $user)
    {
        $transactions = $user->walletTransactions()->with('performedByAdmin')->paginate(20);
        $summary      = $user->walletSummary();

        return view('admin.wallet.show', compact('user', 'transactions', 'summary'));
    }

    // Manually credit or debit any wallet
    public function adjust(Request $request, string $type, int $id)
    {
        $request->validate([
            'action'      => 'required|in:credit,debit',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $user = $type === 'admin' ? Admin::findOrFail($id) : Customer::findOrFail($id);

        try {
            $this->walletService->{$request->action}(
                $user,
                (float) $request->amount,
                $request->description,
                ['performed_by' => Auth::guard('admin')->id()]
            );

            return back()->with('success', 'Wallet adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // Transfer between any two users
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

        $sender   = $request->sender_type === 'admin'
                    ? Admin::findOrFail($request->sender_id)
                    : Customer::findOrFail($request->sender_id);

        $receiver = $request->receiver_type === 'admin'
                    ? Admin::findOrFail($request->receiver_id)
                    : Customer::findOrFail($request->receiver_id);

        try {
            $this->walletService->transfer(
                $sender, $receiver,
                (float) $request->amount,
                $request->note,
                ['performed_by' => Auth::guard('admin')->id()]
            );

            return back()->with('success', "₹{$request->amount} transferred successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
