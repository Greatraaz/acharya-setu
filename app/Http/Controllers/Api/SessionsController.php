<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{AppSetting, ConsultationSession, SessionNote, User};
use App\Services\SessionBookingService;
use Carbon\Carbon;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SessionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        ConsultationSession::expireMissedSessions(
            $u->role === 'mentor' ? $u->id : null,
            $u->role === 'mentee' ? $u->id : null
        );

        $f = $u->role === 'mentor' ? 'mentor_id' : 'mentee_id';
        $query = ConsultationSession::where($f, $u->id)
            ->with([
                'mentor:id,name,avatar_url,gender',
                'mentee:id,name,avatar_url',
                'sessionInvoice',
                'notes' => fn ($q) => $q->with('author:id,name,role,avatar_url')->latest(),
            ]);

        if ($request->filled('status') && array_key_exists($request->status, ConsultationSession::STATUSES)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }
        if ($search = trim((string) $request->input('q', ''))) {
            $query->where(function ($inner) use ($search, $u) {
                $inner->where('title', 'like', '%'.$search.'%')
                    ->orWhere('booking_ref', 'like', '%'.$search.'%');
                if ($u->role === 'mentee') {
                    $inner->orWhereHas('mentor', fn ($m) => $m->where('name', 'like', '%'.$search.'%'));
                } else {
                    $inner->orWhereHas('mentee', fn ($m) => $m->where('name', 'like', '%'.$search.'%'));
                }
            });
        }

        $sessions = $query->orderByDesc('scheduled_at')
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
                'scheduledAt'    => $s->scheduled_at?->toDateTimeString(),
                'duration'       => $s->duration_minutes,
                'status'         => $s->status,
                'topic'          => $s->title,
                'agenda'         => $s->agenda,
                'notes'          => $s->notes->map(fn (SessionNote $note) => $this->formatNote($note))->values(),
                'meetingLink'    => $s->meeting_link,
                'channel'        => $s->meeting_channel,
                'canJoinCall'    => $s->canJoinCall(),
                'amountPaid'     => (float) $s->amount,
                'paymentStatus'  => $s->payment_status,
                'paymentMethod'  => $s->payment_method,
                'paymentMethodLabel' => $s->paymentMethodLabel(),
                'walletAmount'   => (float) ($s->wallet_amount ?? 0),
                'razorpayAmount' => (float) ($s->razorpay_amount ?? 0),
                'invoice'        => $s->sessionInvoice ? [
                    'id'             => $s->sessionInvoice->id,
                    'invoice_number' => $s->sessionInvoice->invoice_number,
                ] : null,
                'bookingRef'     => $s->booking_ref,
            ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'sessions'   => $sessions,
            'statuses'   => ConsultationSession::STATUSES,
        ]);
    }

    /**
     * Book a session (mentee).
     * Wallet-first flow:
     * - If wallet balance is sufficient, deduct from wallet and confirm immediately.
     * - Otherwise, create Razorpay order and wait for verifyPayment().
     * POST /api/v1/mentee/sessions
     */
    public function store(Request $request, SessionBookingService $booking): JsonResponse
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

        $result = $booking->book($request->user(), $data, 'api');
        $payload = $result['payload'];

        if (! $result['ok']) {
            return response()->json(array_merge([
                'status'     => false,
                'statuscode' => $result['http'],
            ], $payload), $result['http']);
        }

        // Payment choice: no razorpay keys in response
        if (! empty($payload['requires_payment_choice'])) {
            return response()->json([
                'status'     => true,
                'statuscode' => 200,
                'message'    => $payload['message'],
                'data'       => $payload,
            ], 200);
        }

        // Razorpay checkout needed
        if (! empty($payload['requires_payment'])) {
            return response()->json([
                'status'     => true,
                'statuscode' => 201,
                'message'    => $payload['message'],
                'data'       => $payload,
            ], 201);
        }

        return response()->json([
            'status'     => true,
            'statuscode' => $result['http'],
            'message'    => $payload['message'],
            'data'       => $payload,
        ], $result['http']);
    }

    /**
     * Verify Razorpay payment and confirm the session.
     * POST /api/v1/mentee/sessions/verify
     */
    public function verifyPayment(Request $request, SessionBookingService $booking): JsonResponse
    {
        $data = $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $result = $booking->verify($request->user(), $data);
        $payload = $result['payload'];

        if (! $result['ok']) {
            return response()->json(array_merge([
                'status'     => false,
                'statuscode' => $result['http'],
            ], $payload), $result['http']);
        }

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => $payload['message'],
            'data'       => $payload,
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
                'status' => 'sometimes|in:upcoming,completed,cancelled',
                'notes'  => 'nullable|string',
            ]);

            if (($d['status'] ?? null) === 'completed') {
                $s->complete();
                $s->refresh();
            } else {
                $s->update($d);
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
        $session = ConsultationSession::query()
            ->where('meeting_channel', $channel)
            ->where(function ($q) use ($request) {
                $q->where('mentor_id', $request->user()->id)
                  ->orWhere('mentee_id', $request->user()->id);
            })
            ->with(['mentor:id,name,avatar_url', 'mentee:id,name,avatar_url'])
            ->first();

        if (! $session) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Session not found for this channel.',
            ], 404);
        }

        return $this->agoraTokenResponse($request, $session);
    }

    public function agoraToken(Request $request, int $id): JsonResponse
    {
        try {
            $session = $this->findOwnedSession($request, $id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Session not found',
            ], 404);
        }

        $session->load(['mentor:id,name,avatar_url', 'mentee:id,name,avatar_url']);

        return $this->agoraTokenResponse($request, $session);
    }

    private function agoraTokenResponse(Request $request, ConsultationSession $session): JsonResponse
    {
        try {
            $payload = app(\App\Services\AgoraService::class)->issueToken($request->user(), $session);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => $e->getStatusCode(),
                'message'    => $e->getMessage(),
            ], $e->getStatusCode());
        }

        return response()->json(array_merge([
            'status'     => true,
            'statuscode' => 200,
        ], $payload));
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
