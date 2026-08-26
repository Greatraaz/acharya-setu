<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Services\SessionBookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request, SessionBookingService $booking)
    {
        $data = $request->validate([
            'mentor_id'      => 'required|exists:users,id',
            'date'           => 'required|date|after_or_equal:today',
            'time'           => 'required|string',
            'duration'       => 'required|integer|in:'.implode(',', ConsultationSession::BOOKING_DURATIONS),
            'title'          => 'required|string|max:255',
            'agenda'         => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:wallet,razorpay,hybrid',
        ]);

        $result = $booking->book(auth()->user(), $data, 'web');
        $payload = $result['payload'];
        $wantsJson = $request->ajax() || $request->wantsJson();

        if (! $result['ok']) {
            if ($wantsJson) {
                return response()->json($payload, $result['http']);
            }
            if (! empty($payload['topup_url'])) {
                return redirect($payload['topup_url'])->with('error', $payload['message']);
            }

            return back()->with('error', $payload['message'])->withInput();
        }

        if (! empty($payload['requires_payment_choice']) || ! empty($payload['requires_payment'])) {
            return response()->json($payload, $result['http']);
        }

        if ($wantsJson) {
            return response()->json(array_merge($payload, [
                'redirect' => route('mentee.sessions'),
            ]), $result['http']);
        }

        return redirect()->route('mentee.sessions')
            ->with('success', ($payload['message'] ?? 'Booked').' Ref: '.($payload['session']['booking_ref'] ?? ''));
    }

    public function verifyPayment(Request $request, SessionBookingService $booking)
    {
        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $result = $booking->verify(auth()->user(), $data);
        $payload = $result['payload'];

        if (! $result['ok']) {
            return response()->json($payload, $result['http']);
        }

        return response()->json(array_merge($payload, [
            'redirect' => route('mentee.sessions'),
        ]), $result['http']);
    }

    public function reviewForm(int $id)
    {
        $session = ConsultationSession::where('mentee_id', auth()->id())
            ->where('status', 'completed')
            ->findOrFail($id);

        return view('mentee.session-review', compact('session'));
    }

    public function submitReview(int $id, Request $request)
    {
        $data = $request->validate([
            'overall_rating'       => 'required|integer|between:1,5',
            'communication_rating' => 'nullable|integer|between:1,5',
            'knowledge_rating'     => 'nullable|integer|between:1,5',
            'punctuality_rating'   => 'nullable|integer|between:1,5',
            'helpfulness_rating'   => 'nullable|integer|between:1,5',
            'review_text'          => 'nullable|string|max:1000',
            'would_recommend'      => 'boolean',
        ]);

        $session = ConsultationSession::where('mentee_id', auth()->id())
            ->where('status', 'completed')
            ->findOrFail($id);

        $session->reviews()->create(array_merge($data, [
            'reviewer_id'   => auth()->id(),
            'reviewee_id'   => $session->mentor_id,
            'reviewer_role' => 'mentee',
            'is_public'     => true,
            'submitted_at'  => now(),
        ]));

        $avg = $session->mentor->reviewsReceived()->avg('overall_rating');
        $session->mentor->update(['rating' => round($avg, 2)]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Review submitted. Thank you!']);
        }

        return redirect()->route('mentee.sessions')->with('success', 'Review submitted!');
    }
}
