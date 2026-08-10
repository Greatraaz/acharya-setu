<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\ConsultationSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Book a session — wallet-first, Razorpay fallback (same as mobile app).
     * POST /mentee/sessions
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'mentor_id' => 'required|exists:users,id',
            'date'      => 'required|date|after_or_equal:today',
            'time'      => 'required|string',
            'duration'  => 'required|integer|in:30,60,90',
            'title'     => 'nullable|string|max:255',
            'agenda'    => 'nullable|string|max:1000',
        ]);

        $mentee = auth()->user();
        $mentor = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->findOrFail($data['mentor_id']);

        $amount = round((float) ($mentor->rate_per_minute ?? 0) * (int) $data['duration'], 2);
        $scheduledAt = Carbon::parse($data['date'].' '.$data['time'], 'Asia/Kolkata');
        $wantsJson = $request->ajax() || $request->wantsJson();
        $planAllowance = $mentee->planSessionAllowance();
        $coveredByPlan = $amount > 0 && ($planAllowance['covered'] ?? false);

        ConsultationSession::expireAbandonedUnpaidPayments();
        ConsultationSession::releaseOwnUnpaidHold($mentee->id, $mentor->id, $scheduledAt);

        $alreadyBooked = ConsultationSession::where('mentor_id', $mentor->id)
            ->where('scheduled_at', $scheduledAt)
            ->occupyingSlot()
            ->exists();

        if ($alreadyBooked) {
            $msg = 'This mentor already has an appointment at the selected date and time.';
            if ($wantsJson) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors(['time' => $msg])->withInput();
        }

        $channel = Str::random(10);
        $bookingRef = 'AS-'.mt_rand(10000000, 99999999);
        $currency = 'INR';
        $title = $data['title'] ?? ($data['agenda'] ? Str::limit($data['agenda'], 80) : 'Mentorship Session');

        // Free mentor rate OR included in active subscription plan
        if ($amount <= 0 || $coveredByPlan) {
            $session = ConsultationSession::create([
                'mentor_id'         => $mentor->id,
                'mentee_id'         => $mentee->id,
                'scheduled_at'      => $scheduledAt,
                'duration_minutes'  => $data['duration'],
                'timezone'          => 'Asia/Kolkata',
                'title'             => $title,
                'agenda'            => $data['agenda'] ?? null,
                'status'            => ConsultationSession::STATUS_UPCOMING,
                'amount'            => $coveredByPlan ? 0 : $amount,
                'currency'          => $currency,
                'payment_status'    => 'waived',
                'payment_reference' => $coveredByPlan
                    ? 'PLAN-'.($planAllowance['subscription_id'] ?? 'FREE')
                    : null,
                'booking_ref'       => $bookingRef,
                'meeting_channel'   => $channel,
                'meeting_link'      => url('as/'.$channel),
            ]);

            $msg = $coveredByPlan
                ? 'Session booked using your '.$planAllowance['plan_name'].' plan'
                    .(isset($planAllowance['remaining'])
                        ? ' ('.max(0, (int) $planAllowance['remaining'] - 1).' included sessions left this month).'
                        : ' (unlimited included sessions).')
                : 'Session booked successfully!';

            return $this->bookingSuccess($request, $session, $msg);
        }

        // Wallet-first
        if ($mentee->hasSufficientBalance($amount)) {
            try {
                $session = DB::transaction(function () use ($mentor, $mentee, $scheduledAt, $data, $amount, $currency, $bookingRef, $channel, $title) {
                    $session = ConsultationSession::create([
                        'mentor_id'         => $mentor->id,
                        'mentee_id'         => $mentee->id,
                        'scheduled_at'      => $scheduledAt,
                        'duration_minutes'  => $data['duration'],
                        'timezone'          => 'Asia/Kolkata',
                        'title'             => $title,
                        'agenda'            => $data['agenda'] ?? null,
                        'status'            => ConsultationSession::STATUS_UPCOMING,
                        'amount'            => $amount,
                        'currency'          => $currency,
                        'payment_status'    => 'paid',
                        'payment_reference' => 'WAL-'.$bookingRef,
                        'booking_ref'       => $bookingRef,
                        'meeting_channel'   => $channel,
                        'meeting_link'      => url('as/'.$channel),
                    ]);

                    $mentee->debitWallet(
                        $amount,
                        "Session booking {$bookingRef}",
                        [
                            'reference'            => 'WAL-'.$bookingRef,
                            'transactionable_type' => ConsultationSession::class,
                            'transactionable_id'   => $session->id,
                            'meta'                 => [
                                'booking_ref' => $bookingRef,
                                'mentor_id'   => $mentor->id,
                                'source'      => 'session_booking_wallet_web',
                            ],
                        ]
                    );

                    return $session;
                });
            } catch (\Throwable $e) {
                Log::error('Web wallet booking failed.', [
                    'mentee_id' => $mentee->id,
                    'mentor_id' => $mentor->id,
                    'error'     => $e->getMessage(),
                ]);

                $msg = 'Unable to complete wallet payment right now.';
                if ($wantsJson) {
                    return response()->json(['message' => $msg], 500);
                }

                return back()->with('error', $msg);
            }

            return $this->bookingSuccess(
                $request,
                $session,
                'Session booked! ₹'.number_format($amount, 0).' deducted from your wallet.'
            );
        }

        // Insufficient wallet → Razorpay
        $creds = $this->razorpayCredentials();
        $shortfall = max(0, $amount - (float) $mentee->wallet_balance);

        if (! ($creds['enabled'] ?? true)) {
            $msg = 'Online payment is disabled. Please add ₹'.number_format($shortfall, 0).' to your wallet to book.';
            if ($wantsJson) {
                return response()->json([
                    'message'         => $msg,
                    'wallet_balance'  => (float) $mentee->wallet_balance,
                    'required_amount' => $amount,
                    'topup_url'       => route('mentee.wallet'),
                ], 422);
            }

            return redirect()->route('mentee.wallet')->with('error', $msg);
        }

        if (empty($creds['key']) || empty($creds['secret'])) {
            $msg = 'Payment gateway is not configured correctly. Add ₹'.number_format($shortfall, 0).' to your wallet, or ask admin to re-save the Razorpay Key Secret.';
            if ($wantsJson) {
                return response()->json([
                    'message'         => $msg,
                    'wallet_balance'  => (float) $mentee->wallet_balance,
                    'required_amount' => $amount,
                    'topup_url'       => route('mentee.wallet'),
                ], 422);
            }

            return redirect()->route('mentee.wallet')->with('error', $msg);
        }

        $amountInPaise = (int) round($amount * 100);
        if ($amountInPaise < 100) {
            $msg = 'Session amount must be at least ₹1 for online payment.';
            if ($wantsJson) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $receipt = 'ses_'.$mentee->id.'_'.$mentor->id.'_'.time();

        try {
            $response = Http::withBasicAuth($creds['key'], $creds['secret'])
                ->acceptJson()
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountInPaise,
                    'currency' => $currency,
                    'receipt'  => Str::limit($receipt, 40, ''),
                    'notes'    => [
                        'mentee_id'   => (string) $mentee->id,
                        'mentor_id'   => (string) $mentor->id,
                        'booking_ref' => $bookingRef,
                        'source'      => 'web',
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Razorpay session order failed (web).', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);

                $msg = $response->status() === 401
                    ? 'Razorpay credentials are invalid. Please re-save Key Secret in Admin → Settings, or add money to your wallet.'
                    : 'Unable to initiate payment right now. Please try again or top up your wallet.';

                if ($wantsJson) {
                    return response()->json([
                        'message'         => $msg,
                        'topup_url'       => route('mentee.wallet'),
                        'wallet_balance'  => (float) $mentee->wallet_balance,
                        'required_amount' => $amount,
                    ], 502);
                }

                return back()->with('error', $msg);
            }

            $order = $response->json();
        } catch (\Throwable $e) {
            Log::error('Razorpay session order exception (web): '.$e->getMessage());
            $msg = 'Unable to initiate payment right now.';
            if ($wantsJson) {
                return response()->json(['message' => $msg], 502);
            }

            return back()->with('error', $msg);
        }

        $session = ConsultationSession::create([
            'mentor_id'         => $mentor->id,
            'mentee_id'         => $mentee->id,
            'scheduled_at'      => $scheduledAt,
            'duration_minutes'  => $data['duration'],
            'timezone'          => 'Asia/Kolkata',
            'title'             => $title,
            'agenda'            => $data['agenda'] ?? null,
            'status'            => ConsultationSession::STATUS_PENDING,
            'amount'            => $amount,
            'currency'          => $currency,
            'payment_status'    => 'pending',
            'razorpay_order_id' => $order['id'] ?? null,
            'booking_ref'       => $bookingRef,
            'meeting_channel'   => $channel,
            'meeting_link'      => url('as/'.$channel),
        ]);

        return response()->json([
            'message'           => 'Complete payment to confirm your booking.',
            'requires_payment'  => true,
            'session_id'        => $session->id,
            'booking_ref'       => $bookingRef,
            'order_id'          => $order['id'] ?? null,
            'amount'            => $amountInPaise,
            'amount_rupees'     => $amount,
            'currency'          => $currency,
            'key'               => $creds['key'],
            'name'              => 'Vedrix',
            'description'       => 'Session with '.$mentor->name,
            'prefill'           => [
                'name'    => $mentee->name,
                'email'   => $mentee->email,
                'contact' => $mentee->phone ?? '',
            ],
            'wallet_balance'    => (float) $mentee->wallet_balance,
        ]);
    }

    /**
     * Verify Razorpay payment and confirm the session.
     * POST /mentee/sessions/verify-payment
     */
    public function verifyPayment(Request $request)
    {
        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $creds = $this->razorpayCredentials();
        if (empty($creds['secret'])) {
            return response()->json(['message' => 'Payment gateway is not configured.'], 503);
        }

        $expectedSig = hash_hmac(
            'sha256',
            $data['razorpay_order_id'].'|'.$data['razorpay_payment_id'],
            $creds['secret']
        );

        if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
            return response()->json(['message' => 'Payment signature verification failed.'], 422);
        }

        $session = ConsultationSession::where('mentee_id', auth()->id())
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->latest('id')
            ->first();

        if (! $session) {
            return response()->json(['message' => 'Pending session not found for this payment.'], 404);
        }

        if (
            $session->payment_status === 'paid'
            && $session->razorpay_payment_id === $data['razorpay_payment_id']
        ) {
            return response()->json([
                'message'  => 'Session already confirmed.',
                'redirect' => route('mentee.sessions'),
            ]);
        }

        $session->update([
            'status'              => ConsultationSession::STATUS_UPCOMING,
            'payment_status'      => 'paid',
            'payment_reference'   => $data['razorpay_payment_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
        ]);

        return response()->json([
            'message'     => 'Payment successful! Your session is confirmed.',
            'redirect'    => route('mentee.sessions'),
            'booking_ref' => $session->booking_ref,
        ]);
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

    private function bookingSuccess(Request $request, ConsultationSession $session, string $message)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message'          => $message,
                'requires_payment' => false,
                'redirect'         => route('mentee.sessions'),
                'booking_ref'      => $session->booking_ref,
                'amount'           => (float) $session->amount,
            ]);
        }

        return redirect()->route('mentee.sessions')->with('success', $message.' Ref: '.$session->booking_ref);
    }

    private function razorpayCredentials(): array
    {
        $settings = AppSetting::razorpay();

        return [
            'enabled' => $settings['enabled'] ?? true,
            'key'     => $settings['key'] ?: config('services.razorpay.key', env('RAZORPAY_KEY_ID', '')),
            'secret'  => $settings['secret'] ?: config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET', '')),
        ];
    }
}
