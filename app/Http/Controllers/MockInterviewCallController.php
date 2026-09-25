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
        $item = $this->accessible($id, $user)->load(['assigner', 'user']);
        $this->agora->assertMockParticipant($user, $item);

        if (! $item->canJoinCall()) {
            $message = $item->callWindowEnded()
                ? 'This mock interview time has ended. You can no longer join the call.'
                : 'This mock interview is not available to join yet.';

            return redirect()
                ->to($this->backUrl($user, $item->id))
                ->with('error', $message);
        }

        $peer = $item->isHost($user) ? $item->user : $item->assigner;

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
            'isMentee'       => $item->isMentee($user),
            'scheduledEndTs' => $item->scheduledEnd()?->getTimestamp(),
            'serverNowTs'    => now()->getTimestamp(),
            'tokenUrl'       => route('mock-interviews.video-token', $item->id),
            'endUrl'         => route('mock-interviews.call.end', $item->id),
            'notesUrl'       => route('mock-interviews.notes.show', $item->id),
            'backUrl'        => $this->backUrl($user, $item->id),
        ]);
    }

    public function token(int $id)
    {
        $user = auth()->user();
        $item = $this->accessible($id, $user)->load(['assigner:id,name,avatar_url', 'user:id,name,avatar_url']);

        try {
            return response()->json($this->agora->issueMockInterviewToken($user, $item));
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function end(Request $request, int $id)
    {
        $user = auth()->user();
        $item = $this->accessible($id, $user);

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
        $this->accessible($id, auth()->user());

        return response()->json(['content' => '', 'updated_at' => null]);
    }

    public function saveMyNote(Request $request, int $id)
    {
        $this->accessible($id, auth()->user());
        $request->validate(['content' => 'nullable|string|max:65535']);

        return response()->json([
            'message'    => 'Notes saved.',
            'content'    => (string) $request->input('content', ''),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    private function accessible(int $id, $user): MockInterviewRequest
    {
        $item = MockInterviewRequest::query()->where('id', $id)->firstOrFail();

        if (! $item->isParticipant($user)) {
            abort(404);
        }

        return $item;
    }

    private function backUrl($user, int $id): string
    {
        if (($user->role ?? null) === 'admin') {
            return route('admin.mock-interviews.show', $id);
        }

        return route('mentee.mock-interviews.show', $id);
    }
}
