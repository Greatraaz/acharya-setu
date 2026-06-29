<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\SessionReview;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 
class ConsultationSessionController extends Controller
{
    // ── Admin Index ───────────────────────────────────────────
    public function index(Request $request)
    {
        $query = ConsultationSession::with(['mentor', 'mentee', 'menteeReview'])
            ->latest('scheduled_at');
 
        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhere('booking_ref', 'like', "%$s%")
                  ->orWhereHas('mentor', fn($q) => $q->where('name', 'like', "%$s%"))
                  ->orWhereHas('mentee', fn($q) => $q->where('name', 'like', "%$s%"));
            });
        }
 
        if ($request->status)   $query->where('status', $request->status);
        if ($request->date_from) $query->whereDate('scheduled_at', '>=', $request->date_from);
        if ($request->date_to)   $query->whereDate('scheduled_at', '<=', $request->date_to);
 
        $sessions = $query->paginate(15)->withQueryString();
 
        $stats = [
            'total'     => ConsultationSession::count(),
            'upcoming'  => ConsultationSession::upcoming()->count(),
            'completed' => ConsultationSession::completed()->count(),
            'cancelled' => ConsultationSession::where('status', 'cancelled')->count(),
            'revenue'   => ConsultationSession::where('payment_status', 'paid')->sum('amount'),
            'avg_rating'=> round(SessionReview::where('reviewer_role','mentee')->avg('overall_rating') ?? 0, 1),
        ];
 
        return view('admin.sessions.index', compact('sessions', 'stats'));
    }
 
    // ── Create / Book ─────────────────────────────────────────
    public function create()
    {
        $mentors = User::mentors()->active()->approved()->get();;
        $mentees = User::mentees()->active()->get(); // all users can be mentees
        return view('admin.sessions.form', [
            'session' => new ConsultationSession(),
            'mentors' => $mentors,
            'mentees' => $mentees,
        ]);
    }
 
    public function store(Request $request)
    {
        $data = $this->validateSession($request);
        ConsultationSession::create($data);
        return redirect()->route('admin.sessions.index')->with('success', 'Session booked successfully.');
    }
 
    // ── Show ─────────────────────────────────────────────────
    public function show(ConsultationSession $session)
    {
        $session->load(['mentor', 'mentee', 'reviews.reviewer', 'notes.author', 'cancelledBy']);
        return view('admin.sessions.show', compact('session'));
    }
 
    // ── Edit / Update ─────────────────────────────────────────
    public function edit(ConsultationSession $session)
    {
        $mentors = User::whereHas('mentorProfile')->with('mentorProfile')->get();
        $mentees = User::all();
        return view('admin.sessions.form', compact('session', 'mentors', 'mentees'));
    }
 
    public function update(Request $request, ConsultationSession $session)
    {
        $data = $this->validateSession($request);
        $session->update($data);
        return redirect()->route('admin.sessions.show', $session)->with('success', 'Session updated.');
    }
 
    // ── Status transitions ────────────────────────────────────
    public function confirm(ConsultationSession $session)
    {
        $session->confirm();
        return redirect()->back()->with('success', 'Session confirmed.');
    }
 
    public function cancel(Request $request, ConsultationSession $session)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $session->cancel(auth()->id(), $request->reason ?? '');
        return redirect()->back()->with('success', 'Session cancelled.');
    }
 
    public function complete(ConsultationSession $session)
    {
        $session->complete();
        return redirect()->back()->with('success', 'Session marked as completed.');
    }
 
    public function markNoShow(ConsultationSession $session)
    {
        $session->update(['status' => 'no_show']);
        return redirect()->back()->with('success', 'Marked as no-show.');
    }
 
    // ── Notes ─────────────────────────────────────────────────
    public function addNote(Request $request, ConsultationSession $session)
    {
        $request->validate([
            'content'      => 'required|string|max:2000',
            'type'         => 'required|in:note,resource,action_item',
            'resource_url' => 'nullable|url',
            'is_shared'    => 'nullable|boolean',
        ]);
 
        $session->notes()->create([
            'author_id'    => auth()->id(),
            'content'      => $request->content,
            'type'         => $request->type,
            'resource_url' => $request->resource_url,
            'is_shared'    => $request->boolean('is_shared'),
        ]);
 
        return redirect()->back()->with('success', 'Note added.');
    }
 
    // ── Delete ────────────────────────────────────────────────
    public function destroy(ConsultationSession $session)
    {
        $session->delete();
        return redirect()->route('admin.sessions.index')->with('success', 'Session deleted.');
    }
 
    // ── Export ────────────────────────────────────────────────
    public function export(Request $request)
    {
        $sessions = ConsultationSession::with(['mentor','mentee','menteeReview'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('scheduled_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('scheduled_at', '<=', $request->date_to))
            ->latest('scheduled_at')->get();
 
        $csv = "Booking Ref,Title,Mentor,Mentee,Scheduled At,Duration,Status,Amount,Payment,Rating\n";
        foreach ($sessions as $s) {
            $csv .= implode(',', [
                $s->booking_ref,
                '"' . $s->title . '"',
                '"' . ($s->mentor->name ?? '') . '"',
                '"' . ($s->mentee->name ?? '') . '"',
                $s->scheduled_at->format('Y-m-d H:i'),
                $s->duration_minutes . 'min',
                $s->status,
                $s->amount,
                $s->payment_status,
                $s->menteeReview?->overall_rating ?? '—',
            ]) . "\n";
        }
 
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sessions_' . now()->format('Ymd') . '.csv"',
        ]);
    }
 
    private function validateSession(Request $request): array
    {
        return $request->validate([
            'mentor_id'        => 'required|exists:users,id',
            'mentee_id'        => 'required|exists:users,id|different:mentor_id',
            'title'            => 'required|string|max:200',
            'agenda'           => 'nullable|string|max:2000',
            'scheduled_at'     => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'timezone'         => 'nullable|string',
            'meeting_provider' => 'nullable|in:agora,zoom,google,other',
            'meeting_link'     => 'nullable|url',
            'amount'           => 'nullable|numeric|min:0',
            'currency'         => 'nullable|string|size:3',
            'status'           => 'nullable|in:pending,confirmed',
        ]);
    }
}