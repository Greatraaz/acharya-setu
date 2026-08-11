<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\MentorRequest;
use App\Models\User;
use App\Services\MentorRequestService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MentorRequestController extends Controller
{
    public function __construct(
        private readonly MentorRequestService $requests
    ) {}

    /** Change / choose mentor page */
    public function change()
    {
        $mentee = auth()->user();
        $assignedMentor = $mentee->assignedMentor;
        $pendingRequests = MentorRequest::where('mentee_id', $mentee->id)
            ->where('status', MentorRequest::STATUS_PENDING)
            ->with('mentor')
            ->latest()
            ->get();

        $mentors = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->where('is_active', true)
            ->when($mentee->assigned_mentor_id, fn ($q) => $q->where('id', '!=', $mentee->assigned_mentor_id))
            ->orderByDesc('rating')
            ->paginate(12);

        $pendingMentorIds = $pendingRequests->pluck('mentor_id')->all();

        return view('frontend.mentee.change-mentor', compact(
            'assignedMentor',
            'pendingRequests',
            'mentors',
            'pendingMentorIds'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mentor_id' => 'required|integer|exists:users,id',
            'message'   => 'nullable|string|max:1000',
        ]);

        try {
            $this->requests->create(auth()->user(), (int) $data['mentor_id'], $data['message'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('mentee.dashboard')
            ->with('success', 'Mentor request sent. You’ll be notified when they accept.');
    }

    public function destroy(int $id)
    {
        $mentorRequest = MentorRequest::where('mentee_id', auth()->id())->findOrFail($id);

        try {
            $this->requests->cancel(auth()->user(), $mentorRequest);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Mentor request cancelled.');
    }
}
