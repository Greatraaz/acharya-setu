<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Book a session — deduct from mentee wallet.
     * POST /mentee/sessions  (also reachable via /sessions for AJAX from search/profile)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'mentor_id'  => 'required|exists:users,id',
            'date'       => 'required|date|after_or_equal:today',
            'time'       => 'required|string',
            'duration'   => 'required|integer|in:30,60,90',
            'title'     => 'nullable|string|max:255',
        ]);

        $mentee  = auth()->user();
        $mentor  = User::where('role','mentor')->where('mentor_status','approved')->findOrFail($data['mentor_id']);

        $amount  = $mentor->rate_per_minute * $data['duration'];

        // Wallet check
        /*if ($mentee->wallet_balance < $amount) {
            $msg = "Insufficient wallet balance. Please add ₹" . number_format($amount - $mentee->wallet_balance, 0) . " more.";
            if ($request->ajax()) return response()->json(['message' => $msg], 422);
            return back()->with('error', $msg);
        }*/

        $scheduledAt = Carbon::parse($data['date'] . ' ' . $data['time'], 'Asia/Kolkata');

        $alreadyBooked = ConsultationSession::where('mentor_id', $mentor->id)
            ->where('scheduled_at', $scheduledAt)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->exists();

        if ($alreadyBooked) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'This mentor already has an appointment at the selected date and time.'
                ], 422);
            }

            return back()->withErrors([
                'time' => 'This mentor already has an appointment at the selected date and time.'
            ])->withInput();
        }

        $channel = Str::random(10);
        // Create session
        $session = ConsultationSession::create([
            'mentor_id'        => $mentor->id,
            'mentee_id'        => $mentee->id,
            'scheduled_at'     => $scheduledAt,
            'duration_minutes' => $data['duration'],
            'timezone'         => 'Asia/Kolkata',
            'title'            => $data['title'] ?? 'Mentorship Session',
            'status'           => 'upcoming',
            'amount'           => $amount,
            'currency'         => 'INR',
            'booking_id'       => 'AS-' . rand(8),
            'channel'          => $channel,
            'meeting_link'     => url('as/' . $channel)
        ]);

        // Deduct from mentee wallet
        /*$balanceBefore = $mentee->wallet_balance;
        $mentee->decrement('wallet_balance', $amount);
        WalletTransaction::create([
            'user_id'              => $mentee->id,
            'type'                 => 'debit',
            'amount'               => $amount,
            'balance_before'       => $balanceBefore,
            'balance_after'        => $mentee->fresh()->wallet_balance,
            'description'          => "Session booked with {$mentor->name}",
            'reference'            => $session->booking_id,
            'status'               => 'completed',
            'transactionable_type' => ConsultationSession::class,
            'transactionable_id'   => $session->id,
        ]);*/

        // TODO: send email confirmation to mentee + notification to mentor

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message'     => 'Session booked successfully!',
                'redirect'    => route('mentee.sessions'),
                'booking_id' => $session->booking_id,
                'amount'      => $amount,
            ]);
        }

        return redirect()->route('mentee.sessions')->with('success', 'Session booked! Ref: ' . $session->booking_id);
    }

    public function reviewForm(int $id)
    {
        $session = ConsultationSession::where('mentee_id', auth()->id())->where('status','completed')->findOrFail($id);
        return view('mentee.session-review', compact('session'));
    }

    public function submitReview(int $id, Request $request)
    {
        $data = $request->validate([
            'overall_rating'        => 'required|integer|between:1,5',
            'communication_rating'  => 'nullable|integer|between:1,5',
            'knowledge_rating'      => 'nullable|integer|between:1,5',
            'punctuality_rating'    => 'nullable|integer|between:1,5',
            'helpfulness_rating'    => 'nullable|integer|between:1,5',
            'review_text'           => 'nullable|string|max:1000',
            'would_recommend'       => 'boolean',
        ]);

        $session = ConsultationSession::where('mentee_id', auth()->id())->where('status','completed')->findOrFail($id);

        $session->reviews()->create(array_merge($data, [
            'reviewer_id'   => auth()->id(),
            'reviewee_id'   => $session->mentor_id,
            'reviewer_role' => 'mentee',
            'is_public'     => true,
            'submitted_at'  => now(),
        ]));

        // Update mentor's aggregate rating
        $avg = $session->mentor->reviewsReceived()->avg('overall_rating');
        $session->mentor->update(['rating' => round($avg, 2)]);

        if ($request->ajax()) return response()->json(['message' => 'Review submitted. Thank you!']);
        return redirect()->route('mentee.sessions')->with('success', 'Review submitted!');
    }
}