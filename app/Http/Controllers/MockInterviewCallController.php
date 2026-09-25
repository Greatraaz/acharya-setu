<?php

namespace App\Http\Controllers;

use App\Models\MockInterviewRequest;
use App\Services\AgoraService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MockInterviewCallController extends Controller
{
    public function __construct(private readonly AgoraService $agora)
    {
    }

    public function show(int $id)
    {
        $user = auth()->user();
        $item = $this->owned($id, $user->id)->load(['mentor', 'user']);
        $this->agora->assertMockParticipant($user, $item);

        if (! $item->canJoinCall()) {
            $message = $item->callWindowEnded()
                ? 'This mock interview time has ended. You can no longer join the call.'
                : 'This mock interview is not available to join yet.';

            return redirect()
                ->to($this->backUrl($user->role, $item->id))
                ->with('error', $message);
        }

        $peer = (int) $item->mentor_id === (int) $user->id ? $item->user : $item->mentor;

        // Reuse the session Agora room UI with a lightweight session-shaped object.
        $session = (object) [
            'id'               => $item->id,
            'title'            => 'Mock Interview'.($item->target_role ? ' · '.$item->target_role : ''),
            'duration_minutes' => (int) $item->duration_minutes,
        ];

        return view('frontend.sessions.call', [
            'session'        => $session,
            'peer'           => $peer,
            'role'           => $user->role,
            'isMentee'       => (int) $item->user_id === (int) $user->id,
            'scheduledEndTs' => $item->scheduledEnd()?->getTimestamp(),
            'serverNowTs'    => now()->getTimestamp(),
            'tokenUrl'       => route('mock-interviews.video-token', $item->id),
            'endUrl'         => route('mock-interviews.call.end', $item->id),
            'notesUrl'       => route('mock-interviews.notes.show', $item->id),
            'backUrl'        => $this->backUrl($user->role, $item->id),
        ]);
    }

    public function token(int $id)
    {
        $user = auth()->user();
        $item = $this->owned($id, $user->id)->load(['mentor:id,name,avatar_url', 'user:id,name,avatar_url']);

        try {
            return response()->json($this->agora->issueMockInterviewToken($user, $item));
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function end(Request $request, int $id)
    {
        $user = auth()->user();
        $item = $this->owned($id, $user->id);

        $this->agora->endMockInterviewCall($user, $item, $request->input('reason', 'normal'));
        $item->refresh();

        return response()->json([
            'message'    => $item->canJoinCall()
                ? 'Left the call. You can rejoin until the mock interview window ends.'
                : 'Call ended.',
            'can_rejoin' => $item->canJoinCall(),
        ]);
    }

    /** Notes stub so the shared call UI does not 404. */
    public function myNote(int $id)
    {
        $this->owned($id, auth()->id());

        return response()->json(['content' => '', 'updated_at' => null]);
    }

    public function saveMyNote(Request $request, int $id)
    {
        $this->owned($id, auth()->id());
        $request->validate(['content' => 'nullable|string|max:65535']);

        return response()->json([
            'message'    => 'Notes saved.',
            'content'    => (string) $request->input('content', ''),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function owned(int $id, int $userId): MockInterviewRequest
    {
        return MockInterviewRequest::query()
            ->where('id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('mentor_id', $userId);
            })
            ->firstOrFail();
    }

    private function backUrl(string $role, int $id): string
    {
        return $role === 'mentor'
            ? route('mentor.mock-interviews.show', $id)
            : route('mentee.mock-interviews.show', $id);
    }
}
