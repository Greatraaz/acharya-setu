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
        $query = ConsultationSession::where('mentor_id', auth()->id())
            ->with('mentee')
            ->latest('scheduled_at');

        if ($status = $request->status) {
            if ($status === 'upcoming') {
                $query->whereIn('status',['pending','confirmed'])->where('scheduled_at','>',now());
            } else {
                $query->where('status', $status);
            }
        }

        $sessions = $query->paginate(15);
        return view('mentor.sessions', compact('sessions'));
    }

    public function show(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())->with(['mentee','notes','review'])->findOrFail($id);
        return view('mentor.session-detail', compact('session'));
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
        $session = ConsultationSession::where('mentor_id', auth()->id())->where('status','confirmed')->findOrFail($id);
        $session->update(['status'=>'completed','ended_at'=>now()]);
        // TODO: transfer payment to mentor wallet
        return response()->json(['message' => 'Session marked complete.']);
    }

    public function noShow(int $id)
    {
        $session = ConsultationSession::where('mentor_id', auth()->id())->findOrFail($id);
        $session->update(['status'=>'no_show']);
        return response()->json(['message' => 'Marked as no-show.']);
    }

    public function addNote(int $id, Request $request)
    {
        $request->validate(['content'=>'required|string','type'=>'in:note,resource,action_item','is_shared'=>'boolean']);
        $session = ConsultationSession::where('mentor_id', auth()->id())->findOrFail($id);
        $note = $session->notes()->create([
            'author_id' => auth()->id(),
            'type'      => $request->type ?? 'note',
            'content'   => $request->content,
            'is_shared' => $request->boolean('is_shared'),
        ]);
        return response()->json(['message' => 'Note added.', 'note' => $note]);
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