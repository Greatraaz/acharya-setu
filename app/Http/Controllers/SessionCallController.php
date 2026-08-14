<?php

namespace App\Http\Controllers;

use App\Models\ConsultationSession;
use App\Models\SessionNote;
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
            'notesUrl'   => route('sessions.my-note.show', $session->id),
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

    /**
     * Load the current user's private session notes.
     */
    public function myNote(int $id)
    {
        $user = auth()->user();
        $session = $this->ownedSession($id, $user->id);
        $note = $this->findPersonalNote($session, $user->id);

        return response()->json([
            'content'    => $note?->content ?? '',
            'updated_at' => $note?->updated_at?->toIso8601String(),
        ]);
    }

    /**
     * Save the current user's private session notes (used during and after the call).
     */
    public function saveMyNote(Request $request, int $id)
    {
        $user = auth()->user();
        $session = $this->ownedSession($id, $user->id);

        $data = $request->validate([
            'content' => 'nullable|string|max:65535',
        ]);

        $content = trim((string) ($data['content'] ?? ''));
        $note = $this->findPersonalNote($session, $user->id);

        if ($note) {
            if ($content === '') {
                $note->delete();
                $note = null;
            } else {
                $note->update(['content' => $content]);
            }
        } elseif ($content !== '') {
            $note = $session->notes()->create([
                'author_id' => $user->id,
                'type'      => 'note',
                'content'   => $content,
                'is_shared' => false,
            ]);
        }

        return response()->json([
            'message'    => 'Notes saved.',
            'content'    => $content,
            'updated_at' => $note?->fresh()?->updated_at?->toIso8601String(),
        ]);
    }

    private function findPersonalNote(ConsultationSession $session, int $userId): ?SessionNote
    {
        return $session->notes()
            ->where('author_id', $userId)
            ->where('is_shared', false)
            ->where('type', 'note')
            ->first();
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
