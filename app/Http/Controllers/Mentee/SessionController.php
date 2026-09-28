<?php
namespace App\Http\Controllers\Mentee;
use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Services\SessionCancellationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class SessionController extends Controller
{
    public function __construct(
        private readonly SessionCancellationService $cancellations,
    ) {}

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
            ->findOrFail($id);

        try {
            $result = $this->cancellations->cancel(
                $session,
                auth()->user(),
                SessionCancellationService::ROLE_MENTEE,
                $request->input('reason')
            );
        } catch (InvalidArgumentException $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message'         => $result['message'],
                'policy'          => $result['policy'],
                'refund_percent'  => $result['refund_percent'],
                'wallet_refunded' => $result['wallet_refunded'],
                'razorpay_refunded' => $result['razorpay_refunded'],
                'coupon_restored' => $result['coupon_restored'],
                'plan_benefit_restored' => $result['plan_benefit_restored'],
            ]);
        }

        return back()->with('success', $result['message']);
    }
}
