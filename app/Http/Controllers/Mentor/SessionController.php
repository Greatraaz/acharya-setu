<?php
// ──────────────────────────────────────────────────────────────
// app/Http/Controllers/Mentor/SessionController.php
// ──────────────────────────────────────────────────────────────
namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\SessionNote;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $mentorId = auth()->id();

        // Past pending/confirmed/upcoming sessions with no join → no_show
        ConsultationSession::expireMissedSessions($mentorId);

        $query = ConsultationSession::where('mentor_id', $mentorId)
            ->with('mentee')
            ->latest('scheduled_at');

        $filter = $request->input('filter', $request->input('status', 'all'));

        if ($filter && $filter !== 'all') {
            if ($filter === 'upcoming') {
                $query->whereIn('status', ['pending', 'confirmed', 'upcoming'])
                    ->where('scheduled_at', '>', now());
            } elseif ($filter === 'pending') {
                $query->where('status', 'pending');
            } elseif ($filter === 'missed') {
                $query->where('status', 'no_show');
            } else {
                $query->where('status', $filter);
            }
        }

        $sessions = $query->paginate(15)->withQueryString();
        $pendingCount = ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', 'pending')
            ->count();

        return view('frontend.mentors.sessions', compact('sessions', 'pendingCount', 'filter'));
    }

    public function show(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->with(['mentee', 'notes', 'menteeReview'])
            ->findOrFail($id);
        return view('frontend.mentors.session-show', compact('session'));
    }

    public function confirm(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())->where('status','pending')->findOrFail($id);
        $session->update(['status' => 'confirmed']);
        // TODO: notify mentee via email/SMS
        return response()->json(['message' => 'Session confirmed!']);
    }

    public function cancel(int $id, Request $request)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->whereIn('status',['pending','confirmed'])->findOrFail($id);
        $session->update(['status'=>'cancelled','cancellation_reason'=>$request->reason,'cancelled_by'=>auth()->id(),'cancelled_at'=>now()]);
        // TODO: refund mentee wallet
        return response()->json(['message' => 'Session cancelled and mentee refunded.']);
    }

    public function complete(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->whereIn('status', ['confirmed', 'upcoming', 'ongoing', 'pending'])
            ->findOrFail($id);

        $session->complete();

        return response()->json([
            'message' => $session->payment_status === 'paid'
                ? 'Session marked complete. Earnings credited to your wallet.'
                : 'Session marked complete. Earnings will appear after mentee payment is confirmed.',
        ]);
    }

    public function noShow(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())->findOrFail($id);
        $session->update(['status'=>'no_show']);
        return response()->json(['message' => 'Marked as no-show.']);
    }

    public function updateMeetingLink(int $id, Request $request)
    {
        $data = $request->validate([
            'meeting_link' => 'required|url|max:500',
        ]);

        $session = ConsultationSession::where('mentor_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed', 'upcoming'])
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
        $session = ConsultationSession::where(function($q) {
            $q->where('mentor_id', auth()->id())->orWhere('mentee_id', auth()->id());
        })->findOrFail($id);
        $notes = $session->notes()->where(function($q){
            $q->where('author_id', auth()->id())->orWhere('is_shared', true);
        })->get();
        return response()->json(['notes' => $notes]);
    }

    public function videoToken(int $id)
    {
        $session = ConsultationSession::where(function($q) {
            $q->where('mentor_id', auth()->id())->orWhere('mentee_id', auth()->id());
        })->findOrFail($id);
        // TODO: generate Agora token
        return response()->json(['channel' => $session->meeting_channel ?? 'session-'.$id, 'token' => 'AGORA_TOKEN', 'uid' => auth()->id()]);
    }
}