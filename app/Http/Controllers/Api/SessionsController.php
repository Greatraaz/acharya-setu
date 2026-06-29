<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{ConsultationSession, User, Notification};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Carbon\Carbon;
use App\Models\WalletTransaction;
use App\Helpers\Agora\RtcTokenBuilder;
use App\Helpers\Agora\AccessToken;

class SessionsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $u        = $request->user();
        $f        = $u->role === 'mentor' ? 'mentor_id' : 'mentee_id';
        $sessions = ConsultationSession::where($f, $u->id)
            ->with(['mentor:id,name,avatar_url,gender', 'mentee:id,name,avatar_url'])
            ->orderByDesc('scheduled_at')
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'mentorId'     => $s->mentor_id,
                'mentorName'   => $s->mentor?->name,
                'mentorAvatar' => $s->mentor?->avatar_url,
                'mentorGender' => $s->mentor?->gender,
                'menteeId'     => $s->mentee_id,
                'menteeName'   => $s->mentee?->name,
                'menteeAvatar' => $s->mentee?->avatar_url,
                'date'         => $s->scheduled_at?->format('d M Y'),
                'time'         => $s->scheduled_at?->format('h:i A'),
                'duration'     => $s->duration_minutes,
                'status'       => $s->status,
                'topic'        => $s->topic,
                'notes'        => $s->notes,
                'meetingLink'  => $s->meeting_link,
                'channel'      => $s->channel,
                'amountPaid'   => $s->amount_paid,
            ]);
        $statusCode = 200;
        return response()->json([
            'status'     => true,
            'statuscode' => $statusCode,
            'sessions'   => $sessions
        ], $statusCode);
    }

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
        $mentor = User::where('id', $data['mentor_id'])->where('role', 'mentor')->where('mentor_status', 'approved')->first();

        if (!$mentor) {
            return response()->json([
                'status'  => false,
                'message' => 'Mentor not found.'
            ], 404);
        }
        $amount = $mentor->rate_per_minute * $data['duration'];

        $scheduledAt = Carbon::parse($data['date'] . ' ' . $data['time'],'Asia/Kolkata');
        $alreadyBooked = ConsultationSession::where('mentor_id', $mentor->id)->where('scheduled_at', $scheduledAt)->exists();

        if ($alreadyBooked) {
            return response()->json([
                'status'  => false,
                'message' => 'This mentor already has an appointment at the selected date and time.'
            ], 422);
        }

        $channel = Str::random(10);

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
            'booking_id'       => 'AS-' . mt_rand(10000000, 99999999),
            'channel'          => $channel,
            'meeting_link'     => url('as/' . $channel),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Session booked successfully.',
            /*'data' => [
                'id'            => $session->id,
                'booking_id'    => $session->booking_id,
                'mentor_id'     => $session->mentor_id,
                'mentee_id'     => $session->mentee_id,
                'scheduled_at'  => $session->scheduled_at,
                'duration'      => $session->duration_minutes,
                'amount'        => $session->amount,
                'currency'      => $session->currency,
                'meeting_link'  => $session->meeting_link,
                'status'        => $session->status,
            ]*/
        ], 201);
    }

    /*public function store(Request $request): JsonResponse
    {
        $statusCode = 201;
        try {
            $d = $request->validate([
                'mentor_id'        => 'required|integer|exists:users,id',
                'scheduled_at'     => 'required|date',
                'duration_minutes' => 'sometimes|integer|min:15|max:120',
                'topic'            => 'nullable|string|max:200',
                'notes'            => 'nullable|string',
            ]);
            $s = ConsultationSession::create(array_merge($d, [
                'mentee_id'    => $request->user()->id,
                'status'       => 'upcoming',
                'meeting_link' => 'https://meet.acharyasetu.com/' . Str::random(8),
            ]));
            Notification::create([
                'user_id' => $d['mentor_id'],
                'type'    => 'session_booked',
                'title'   => 'New Session Booked',
                'body'    => 'Session booked for: ' . ($d['topic'] ?? 'General'),
            ]);
            return response()->json([
                'status'     => true,
                'statuscode' => $statusCode,
                'session'    => $s->load(['mentor:id,name,avatar_url', 'mentee:id,name,avatar_url'])
            ], $statusCode);
        } catch (\Throwable $e) {
            $statusCode = 400;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => $e->getMessage(),
            ], $statusCode);
        }
    }*/

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $s = ConsultationSession::findOrFail($id);
            $d = $request->validate([
                'status' => 'sometimes|in:upcoming,completed,cancelled',
                'notes'  => 'nullable|string',
            ]);
            $s->update($d);
            if (($d['status'] ?? null) === 'completed') {
                User::where('id', $s->mentor_id)->increment('total_sessions');
            }
            $statusCode = 200;
            return response()->json([
                'status'     => true,
                'statuscode' => $statusCode,
                'session'    => $s
            ], $statusCode);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $statusCode = 404;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => 'Session not found'
            ], $statusCode);
        } catch (\Throwable $e) {
            $statusCode = 400;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => $e->getMessage(),
            ], $statusCode);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $session = ConsultationSession::findOrFail($id);
            $session->update(['status' => 'cancelled']);
            $statusCode = 200;
            return response()->json([
                'status'     => true,
                'statuscode' => $statusCode,
                'message'    => 'Cancelled'
            ], $statusCode);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $statusCode = 404;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => 'Session not found'
            ], $statusCode);
        } catch (\Throwable $e) {
            $statusCode = 400;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => $e->getMessage(),
            ], $statusCode);
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
            'status'     => true,
            'token' => $newToken
        ], 200);
    }
}
