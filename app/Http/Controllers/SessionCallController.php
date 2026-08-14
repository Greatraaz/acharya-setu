<?php

namespace App\Http\Controllers;

use App\Models\ConsultationSession;
use App\Services\AgoraService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SessionCallController extends Controller
{
    public function __construct(private readonly AgoraService $agora)
    {
    }

    public function show(int $id)
    {
        $user = auth()->user();
        $session = $this->ownedSession($id, $user->id)->load(['mentor', 'mentee']);
        $this->agora->assertParticipant($user, $session);

        if (! $session->canJoinCall()) {
            return redirect()
                ->to($this->sessionUrl($user->role, $session->id))
                ->with('error', 'This session is not available to join.');
        }

        $peer = (int) $session->mentor_id === (int) $user->id ? $session->mentee : $session->mentor;

        return view('frontend.sessions.call', [
            'session'    => $session,
            'peer'       => $peer,
            'role'       => $user->role,
            'tokenUrl'   => route('sessions.video-token', $session->id),
            'endUrl'     => route('sessions.call.end', $session->id),
            'backUrl'    => $this->sessionUrl($user->role, $session->id),
        ]);
    }

    public function token(int $id)
    {
        $user = auth()->user();
        $session = $this->ownedSession($id, $user->id);
        $session->load(['mentor:id,name,avatar_url', 'mentee:id,name,avatar_url']);

        try {
            return response()->json($this->agora->issueToken($user, $session));
        } catch (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }
    }

    public function end(Request $request, int $id)
    {
        $user = auth()->user();
        $session = $this->ownedSession($id, $user->id);

        $this->agora->endCall($user, $session, $request->input('reason', 'normal'));

        return response()->json(['message' => 'Left the call.']);
    }

    private function ownedSession(int $id, int $userId): ConsultationSession
    {
        return ConsultationSession::where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('mentor_id', $userId)->orWhere('mentee_id', $userId);
            })
            ->firstOrFail();
    }

    private function sessionUrl(string $role, int $id): string
    {
        return $role === 'mentor'
            ? route('mentor.sessions.show', $id)
            : route('mentee.sessions.show', $id);
    }
}
