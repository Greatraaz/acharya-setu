<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AppSetting, ConsultationSession, SessionNote, User};
use App\Helpers\Agora\RtcTokenBuilder;
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Support\Str;

class SessionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $u        = $request->user();
        $f        = $u->role === 'mentor' ? 'mentor_id' : 'mentee_id';
        $sessions = ConsultationSession::where($f, $u->id)
            ->with([
                'mentor:id,name,avatar_url,gender',
                'mentee:id,name,avatar_url',
                'notes' => fn ($q) => $q->with('author:id,name,role,avatar_url')->latest(),
            ])
            ->orderByDesc('scheduled_at')
            ->get()
            ->map(fn ($s) => [
                'id'             => $s->id,
                'mentorId'       => $s->mentor_id,
                'mentorName'     => $s->mentor?->name,
                'mentorAvatar'   => $s->mentor?->avatar_url,
                'mentorGender'   => $s->mentor?->gender,
                'menteeId'       => $s->mentee_id,
                'menteeName'     => $s->mentee?->name,
                'menteeAvatar'   => $s->mentee?->avatar_url,
                'date'           => $s->scheduled_at?->format('d M Y'),
                'time'           => $s->scheduled_at?->format('h:i A'),
                'duration'       => $s->duration_minutes,
                'status'         => $s->status,
                'topic'          => $s->title,
                'agenda'         => $s->agenda,
                'notes'          => $s->notes->map(fn (SessionNote $note) => $this->formatNote($note))->values(),
                'meetingLink'    => $s->meeting_link,
                'channel'        => $s->meeting_channel,
                'amountPaid'     => (float) $s->amount,
                'paymentStatus'  => $s->payment_status,
                'bookingRef'     => $s->booking_ref,
            ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'sessions'   => $sessions,
        ]);
    }

    /**
     * Book a session (mentee). Creates a Razorpay order when amount > 0.
     * POST /api/v1/mentee/sessions
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mentor_id' => 'required|exists:users,id',
            'date'      => 'required|date|after_or_equal:today',
            'time'      => 'required|string',
            'duration'  => 'required|integer|in:30,60,90',
            'title'     => 'nullable|string|max:255',
        ]);

        $mentee = $request->user();
        $mentor = User::where('id', $data['mentor_id'])
            ->where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->first();

        if (! $mentor) {
            return response()->json([
                'status'  => false,
                'message' => 'Mentor not found.',
            ], 404);
        }

        $amount = round((float) ($mentor->rate_per_minute ?? 0) * (int) $data['duration'], 2);
        $scheduledAt = Carbon::parse($data['date'] . ' ' . $data['time'], 'Asia/Kolkata');

        $alreadyBooked = ConsultationSession::where('mentor_id', $mentor->id)
            ->where('scheduled_at', $scheduledAt)
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($alreadyBooked) {
            return response()->json([
                'status'  => false,
                'message' => 'This mentor already has an appointment at the selected date and time.',
            ], 422);
        }

        $channel = Str::random(10);
        $bookingRef = 'AS-' . mt_rand(10000000, 99999999);
        $currency = 'INR';

        // Free / zero-amount sessions — confirm immediately
        if ($amount <= 0) {
            $session = ConsultationSession::create([
                'mentor_id'        => $mentor->id,
                'mentee_id'        => $mentee->id,
                'scheduled_at'     => $scheduledAt,
                'duration_minutes' => $data['duration'],
                'timezone'         => 'Asia/Kolkata',
                'title'            => $data['title'] ?? 'Mentorship Session',
                'status'           => ConsultationSession::STATUS_UPCOMING,
                'amount'           => 0,
                'currency'         => $currency,
                'payment_status'   => 'waived',
                'booking_ref'      => $bookingRef,
                'meeting_channel'  => $channel,
                'meeting_link'     => url('as/' . $channel),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Session booked successfully.',
                'data'    => $this->sessionPaymentPayload($session, null, null),
            ], 201);
        }

        // Paid sessions require Razorpay
        $creds = $this->razorpayCredentials();
        if (empty($creds['key']) || empty($creds['secret'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment gateway is not configured.',
            ], 503);
        }

        if (! ($creds['enabled'] ?? true)) {
            return response()->json([
                'status'  => false,
                'message' => 'Razorpay is disabled in admin settings.',
            ], 503);
        }

        $amountInPaise = (int) round($amount * 100);
        if ($amountInPaise < 100) {
            return response()->json([
                'status'  => false,
                'message' => 'Session amount must be at least 1 INR for online payment.',
            ], 422);
        }

        $receipt = 'ses_' . $mentee->id . '_' . $mentor->id . '_' . time();

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
                        'duration'    => (string) $data['duration'],
                    ],
                ]);

            if (! $response->successful()) {
                $razorpayError = $response->json('error.description')
                    ?? $response->json('error.reason')
                    ?? $response->body();

                Log::error('Razorpay order create failed for session booking.', [
                    'mentee_id' => $mentee->id,
                    'mentor_id' => $mentor->id,
                    'status'    => $response->status(),
                    'body'      => $response->body(),
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Unable to initiate payment right now.',
                    'error'   => config('app.debug') ? $razorpayError : null,
                ], 502);
            }

            $order = $response->json();
        } catch (\Throwable $e) {
            Log::error('Razorpay order create exception for session booking.', [
                'mentee_id' => $mentee->id,
                'mentor_id' => $mentor->id,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Unable to initiate payment right now.',
            ], 502);
        }

        $session = ConsultationSession::create([
            'mentor_id'           => $mentor->id,
            'mentee_id'           => $mentee->id,
            'scheduled_at'        => $scheduledAt,
            'duration_minutes'    => $data['duration'],
            'timezone'            => 'Asia/Kolkata',
            'title'               => $data['title'] ?? 'Mentorship Session',
            'status'              => ConsultationSession::STATUS_PENDING,
            'amount'              => $amount,
            'currency'            => $currency,
            'payment_status'      => 'pending',
            'razorpay_order_id'   => $order['id'] ?? null,
            'booking_ref'         => $bookingRef,
            'meeting_channel'     => $channel,
            'meeting_link'        => url('as/' . $channel),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Payment order created.',
            'data'    => $this->sessionPaymentPayload($session, $creds['key'], $amountInPaise),
        ], 201);
    }

    /**
     * Verify Razorpay payment and confirm the session.
     * POST /api/v1/mentee/sessions/verify
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $creds = $this->razorpayCredentials();
        if (empty($creds['secret'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment gateway is not configured.',
            ], 503);
        }

        $expectedSig = hash_hmac(
            'sha256',
            $data['razorpay_order_id'] . '|' . $data['razorpay_payment_id'],
            $creds['secret']
        );

        if (! hash_equals($expectedSig, $data['razorpay_signature'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment signature verification failed.',
            ], 422);
        }

        $session = ConsultationSession::where('mentee_id', $request->user()->id)
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->latest('id')
            ->first();

        if (! $session) {
            return response()->json([
                'status'  => false,
                'message' => 'Pending session not found for this payment order.',
            ], 404);
        }

        if (
            $session->payment_status === 'paid'
            && $session->razorpay_payment_id === $data['razorpay_payment_id']
        ) {
            return response()->json([
                'status'  => true,
                'message' => 'Session already confirmed.',
                'data'    => $this->sessionPaymentPayload($session, null, null),
            ]);
        }

        $session->update([
            'payment_status'      => 'paid',
            'payment_reference'   => $data['razorpay_payment_id'],
            'razorpay_payment_id' => $data['razorpay_payment_id'],
            'status'              => ConsultationSession::STATUS_UPCOMING,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Payment verified and session booked successfully.',
            'data'    => $this->sessionPaymentPayload($session->fresh(), null, null),
        ]);
    }

    /**
     * List all notes on a session (mentor + mentee).
     * GET /api/v1/{mentor|mentee}/sessions/{id}/notes
     */
    public function notes(Request $request, int $id): JsonResponse
    {
        $session = $this->findOwnedSession($request, $id);

        $notes = $session->notes()
            ->with('author:id,name,role,avatar_url')
            ->latest()
            ->get()
            ->map(fn (SessionNote $note) => $this->formatNote($note));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'session_id' => $session->id,
            'count'      => $notes->count(),
            'notes'      => $notes,
        ]);
    }

    /**
     * Add a plain-text note (may include URLs in content).
     * POST /api/v1/{mentor|mentee}/sessions/{id}/notes
     * Body: { "content": "Discussed roadmap https://example.com" }
     */
    public function addNote(Request $request, int $id): JsonResponse
    {
        $session = $this->findOwnedSession($request, $id);

        $data = $request->validate([
            'content' => 'required|string|max:65535',
        ]);

        $note = $session->notes()->create([
            'author_id'    => $request->user()->id,
            'type'         => 'note',
            'content'      => $data['content'],
            'resource_url' => null,
            'is_shared'    => true,
        ]);

        $note->load('author:id,name,role,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Note added.',
            'note'       => $this->formatNote($note),
        ], 201);
    }

    /**
     * Update own session note text.
     * PATCH /api/v1/{mentor|mentee}/sessions/{id}/notes/{noteId}
     */
    public function updateNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $session = $this->findOwnedSession($request, $id);

        $note = $session->notes()
            ->where('id', $noteId)
            ->where('author_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'content' => 'required|string|max:65535',
        ]);

        $note->update(['content' => $data['content']]);
        $note->load('author:id,name,role,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Note updated.',
            'note'       => $this->formatNote($note),
        ]);
    }

    /**
     * Delete own session note.
     * DELETE /api/v1/{mentor|mentee}/sessions/{id}/notes/{noteId}
     */
    public function destroyNote(Request $request, int $id, int $noteId): JsonResponse
    {
        $session = $this->findOwnedSession($request, $id);

        $note = $session->notes()
            ->where('id', $noteId)
            ->where('author_id', $request->user()->id)
            ->firstOrFail();

        $note->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Note deleted.',
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $s = $this->findOwnedSession($request, $id);
            $d = $request->validate([
                'status' => 'sometimes|in:upcoming,completed,cancelled,pending',
                'notes'  => 'nullable|string',
            ]);
            $s->update($d);
            if (($d['status'] ?? null) === 'completed') {
                User::where('id', $s->mentor_id)->increment('total_sessions');
            }

            return response()->json([
                'status'     => true,
                'statuscode' => 200,
                'session'    => $s,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Session not found',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => 400,
                'message'    => $e->getMessage(),
            ], 400);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $session = ConsultationSession::findOrFail($id);
            $session->update(['status' => 'cancelled']);

            return response()->json([
                'status'     => true,
                'statuscode' => 200,
                'message'    => 'Cancelled',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Session not found',
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => 400,
                'message'    => $e->getMessage(),
            ], 400);
        }
    }

    public function getAgoraToken(Request $request, $channel): JsonResponse
    {
        $appId = 'fb46198605914a2ca0397347552f0d97';
        $appCertificate = '95715ebc2a8c4a9aaf4f31a505c81776';
        $uid = 0;
        $expirationTimeInSeconds = 18000;
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expirationTimeInSeconds;

        $newToken = RtcTokenBuilder::buildTokenWithUid(
            $appId,
            $appCertificate,
            $channel,
            $uid,
            RtcTokenBuilder::RolePublisher,
            $privilegeExpiredTs
        );

        return response()->json([
            'status' => true,
            'token'  => $newToken,
        ]);
    }

    private function findOwnedSession(Request $request, int $id): ConsultationSession
    {
        $user = $request->user();
        $field = $user->role === 'mentor' ? 'mentor_id' : 'mentee_id';

        return ConsultationSession::where('id', $id)
            ->where($field, $user->id)
            ->firstOrFail();
    }

    private function formatNote(SessionNote $note): array
    {
        return [
            'id'           => $note->id,
            'session_id'   => $note->session_id,
            'author_id'    => $note->author_id,
            'type'         => $note->type,
            'content'      => $note->content,
            'resource_url' => $note->resource_url,
            'is_shared'    => (bool) $note->is_shared,
            'created_at'   => $note->created_at,
            'updated_at'   => $note->updated_at,
            'author'       => $note->relationLoaded('author') && $note->author ? [
                'id'         => $note->author->id,
                'name'       => $note->author->name,
                'role'       => $note->author->role,
                'avatar_url' => $note->author->avatar_url,
            ] : null,
        ];
    }

    private function razorpayCredentials(): array
    {
        $settings = AppSetting::razorpay();

        return [
            'enabled' => $settings['enabled'] ?? true,
            'key'     => $settings['key'] ?: env('RAZORPAY_KEY_ID', ''),
            'secret'  => $settings['secret'] ?: env('RAZORPAY_KEY_SECRET', ''),
        ];
    }

    private function sessionPaymentPayload(
        ConsultationSession $session,
        ?string $razorpayKey,
        ?int $amountPaise
    ): array {
        $payload = [
            'session_id'         => $session->id,
            'booking_ref'        => $session->booking_ref,
            'mentor_id'          => $session->mentor_id,
            'mentee_id'          => $session->mentee_id,
            'scheduled_at'       => $session->scheduled_at?->toDateTimeString(),
            'duration'           => $session->duration_minutes,
            'amount'             => (float) $session->amount,
            'currency'           => $session->currency,
            'payment_status'     => $session->payment_status,
            'status'             => $session->status,
            'meeting_link'       => $session->meeting_link,
            'razorpay_order_id'  => $session->razorpay_order_id,
            'razorpay_payment_id'=> $session->razorpay_payment_id,
        ];

        if ($razorpayKey !== null) {
            $payload['razorpay_key'] = $razorpayKey;
            $payload['amount_paise'] = $amountPaise;
        }

        return $payload;
    }
}
