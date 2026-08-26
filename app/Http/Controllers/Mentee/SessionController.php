<?php
namespace App\Http\Controllers\Mentee;
use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        ConsultationSession::expireMissedSessions(null, auth()->id());

        $query = ConsultationSession::where('mentee_id', auth()->id())
            ->with(['mentor', 'sessionInvoice'])->latest('scheduled_at');

        $status = $request->input('status');
        if ($status && $status !== 'all' && array_key_exists($status, ConsultationSession::STATUSES)) {
            $query->where('status', $status);
        }

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('booking_ref', 'like', '%'.$search.'%')
                    ->orWhereHas('mentor', fn ($m) => $m->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->input('date'));
        }

        $sessions = $query->paginate(15)->withQueryString();

        return view('frontend.mentee.sessions', compact('sessions', 'search'));
    }

    public function show(int $id)
    {
        ConsultationSession::expireMissedSessions(null, auth()->id());

        $session = ConsultationSession::where('mentee_id', auth()->id())
            ->with(['mentor','sessionInvoice','notes'])
            ->findOrFail($id);
        return view('frontend.mentee.session-detail', compact('session'));
    }

    public function cancel(int $id, Request $request)
    {
        $session = ConsultationSession::where('mentee_id', auth()->id())
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->where('scheduled_at', '>', now()->addHours(2))
            ->findOrFail($id);

        $session->cancel(auth()->id(), $request->reason ?? 'Cancelled by mentee');

        $mentee = auth()->user();
        $balanceBefore = $mentee->wallet_balance;
        if ((float) $session->amount > 0 && $session->payment_status === 'paid') {
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
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Session cancelled'.((float) $session->amount > 0 ? ' and ₹'.number_format($session->amount,0).' refunded.' : '.')]);
        }
        return back()->with('success', 'Session cancelled.');
    }
}
