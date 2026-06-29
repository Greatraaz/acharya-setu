<?php
// ── WalletController ──────────────────────────────────────────
namespace App\Http\Controllers\Mentor;
use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $transactions = WalletTransaction::where('user_id', $user->id)
            ->latest()->paginate(20);
        $stats = [
            'balance'       => $user->wallet_balance,
            'total_earned'  => WalletTransaction::where('user_id',$user->id)->where('type','credit')->sum('amount'),
            'this_month'    => WalletTransaction::where('user_id',$user->id)->where('type','credit')->whereMonth('created_at',now()->month)->sum('amount'),
        ];
        return view('frontend.mentor.wallet', compact('transactions','stats'));
    }

    public function withdraw(Request $request)
    {
        $request->validate(['amount'=>'required|numeric|min:100']);
        $user = auth()->user();
        if ($user->wallet_balance < $request->amount) {
            return response()->json(['message'=>'Insufficient balance.'],422);
        }
        // TODO: initiate bank transfer / Razorpay payout
        return response()->json(['message'=>'Withdrawal request submitted. Funds will be transferred within 2 business days.']);
    }
}