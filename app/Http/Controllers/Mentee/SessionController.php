<?php
// ── Mentee SessionController ──────────────────────────────────
namespace App\Http\Controllers\Mentee;
use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $query = ConsultationSession::where('mentee_id', auth()->id())
            ->with('mentor')->latest('scheduled_at');

        if ($status = $request->status) {
            if ($status === 'upcoming') {
                $query->whereIn('status',['pending','confirmed'])->where('scheduled_at','>',now());
            } else {
                $query->where('status', $status);
            }
        }

        $sessions = $query->paginate(15);
        return view('frontend.mentee.sessions', compact('sessions'));
    }

    public function show(int $id)
    {
        $session = ConsultationSession::where('mentee_id', auth()->id())
            ->with(['mentor','notes' => fn($q) => $q->where('is_shared',true)])
            ->findOrFail($id);
        return view('frontend.mentee.session-detail', compact('session'));
    }

    public function cancel(int $id, Request $request)
    {
        $session = ConsultationSession::where('mentee_id', auth()->id())
            ->whereIn('status',['pending','confirmed'])
            ->where('scheduled_at', '>', now()->addHours(2))  // free cancel window
            ->findOrFail($id);

        $session->update(['status'=>'cancelled','cancelled_by'=>auth()->id(),'cancelled_at'=>now(),'cancellation_reason'=>$request->reason]);

        // Refund wallet
        $mentee = auth()->user();
        $balanceBefore = $mentee->wallet_balance;
        $mentee->increment('wallet_balance', $session->amount);
        \App\Models\WalletTransaction::create([
            'user_id'        => $mentee->id,
            'type'           => 'refund',
            'amount'         => $session->amount,
            'balance_before' => $balanceBefore,
            'balance_after'  => $mentee->fresh()->wallet_balance,
            'description'    => 'Refund for cancelled session ' . $session->booking_ref,
            'reference'      => 'REF-' . $session->booking_ref,
            'status'         => 'completed',
        ]);

        if ($request->ajax()) return response()->json(['message' => 'Session cancelled and ₹'.number_format($session->amount,0).' refunded.']);
        return back()->with('success', 'Session cancelled. Refund credited to your wallet.');
    }
}