<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentorRequest;
use App\Services\MentorRequestService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MentorRequestController extends Controller
{
    public function __construct(
        private readonly MentorRequestService $requests
    ) {}

    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $search = trim((string) $request->input('search', $request->input('q', '')));

        $query = MentorRequest::where('mentor_id', auth()->id())
            ->with('mentee')
            ->when($status !== 'all' && in_array($status, [
                MentorRequest::STATUS_PENDING,
                MentorRequest::STATUS_ACCEPTED,
                MentorRequest::STATUS_REJECTED,
            ], true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->whereHas('mentee', function ($m) use ($search) {
                $m->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('college', 'like', '%'.$search.'%');
            }))
            ->latest($status === MentorRequest::STATUS_PENDING ? 'created_at' : 'responded_at');

        $requests = $query->paginate(15)->withQueryString();

        $counts = MentorRequest::where('mentor_id', auth()->id())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('frontend.mentors.requests', compact('requests', 'status', 'search', 'counts'));
    }

    public function accept(int $id)
    {
        $mentorRequest = MentorRequest::where('mentor_id', auth()->id())->findOrFail($id);

        try {
            $this->requests->accept(auth()->user(), $mentorRequest);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Mentee request accepted. They are now assigned to you.');
    }

    public function reject(Request $request, int $id)
    {
        $data = $request->validate([
            'mentor_note' => 'nullable|string|max:1000',
        ]);

        $mentorRequest = MentorRequest::where('mentor_id', auth()->id())->findOrFail($id);

        try {
            $this->requests->reject(auth()->user(), $mentorRequest, $data['mentor_note'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Mentee request declined.');
    }
}
