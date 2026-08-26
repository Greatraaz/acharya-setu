<?php
namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $mentorId = auth()->id();

        ConsultationSession::expireMissedSessions($mentorId);

        $query = ConsultationSession::where('mentor_id', $mentorId)
            ->with('mentee')
            ->latest('scheduled_at');

        $filter = $request->input('filter', $request->input('status', 'all'));

        if ($filter && $filter !== 'all' && array_key_exists($filter, ConsultationSession::STATUSES)) {
            $query->where('status', $filter);
        }

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('booking_ref', 'like', '%'.$search.'%')
                    ->orWhereHas('mentee', fn ($m) => $m->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->input('date'));
        }

        $sessions = $query->paginate(15)->withQueryString();

        return view('frontend.mentors.sessions', compact('sessions', 'filter', 'search'));
    }

    public function show(int $id)
    {
        ConsultationSession::expireMissedSessions(auth()->id());

        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->with(['mentee', 'notes', 'menteeReview'])
            ->findOrFail($id);
        return view('frontend.mentors.session-show', compact('session'));
    }

    public function cancel(int $id, Request $request)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->findOrFail($id);
        $session->cancel(auth()->id(), $request->reason ?? 'Cancelled by mentor');

        return response()->json(['message' => 'Session cancelled.']);
    }

    public function complete(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->findOrFail($id);

        $session->complete();

        return response()->json([
            'message' => $session->payment_status === 'paid'
                ? 'Session marked complete. Earnings credited to your wallet.'
                : 'Session marked complete.',
        ]);
    }

    public function updateMeetingLink(int $id, Request $request)
    {
        $data = $request->validate([
            'meeting_link' => 'required|url|max:500',
        ]);

        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->findOrFail($id);

        $session->update([
            'meeting_link' => $data['meeting_link'],
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message'      => 'Meeting link saved.',
                'meeting_link' => $session->meeting_link,
            ]);
        }

        return back()->with('success', 'Meeting link saved.');
    }

    public function confirm(int $id)
    {
        return response()->json([
            'message' => 'Sessions are automatically upcoming after payment. Confirmation is no longer required.',
        ], 200);
    }

    public function noShow(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->where('status', ConsultationSession::STATUS_UPCOMING)
            ->findOrFail($id);
        $session->complete();

        return response()->json(['message' => 'Session marked as completed.']);
    }

    public function addNote(int $id, Request $request)
    {
        $request->validate([
            'content'   => 'required|string',
            'type'      => 'nullable|in:note,resource,action_item',
            'is_shared' => 'nullable|boolean',
        ]);

        $session = ConsultationSession::where('mentor_id', auth()->id())->findOrFail($id);
        $type = $request->input('type', 'note');

        $note = $session->notes()
            ->where('author_id', auth()->id())
            ->where('type', $type)
            ->where('is_shared', true)
            ->latest()
            ->first();

        $payload = [
            'content'   => $request->content,
            'is_shared' => $request->boolean('is_shared', true),
            'type'      => $type,
        ];

        if ($note) {
            $note->update($payload);
        } else {
            $note = $session->notes()->create(array_merge($payload, [
                'author_id' => auth()->id(),
            ]));
        }

        return response()->json(['message' => 'Notes saved.', 'note' => $note]);
    }

    public function notes(int $id)
    {
        $session = ConsultationSession::where(function ($q) {
            $q->where('mentor_id', auth()->id())->orWhere('mentee_id', auth()->id());
        })->findOrFail($id);
        $notes = $session->notes()->where(function ($q) {
            $q->where('author_id', auth()->id())->orWhere('is_shared', true);
        })->get();

        return response()->json(['notes' => $notes]);
    }
}
