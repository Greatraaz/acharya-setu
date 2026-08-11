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

    public function index()
    {
        $pending = MentorRequest::where('mentor_id', auth()->id())
            ->where('status', MentorRequest::STATUS_PENDING)
            ->with('mentee')
            ->latest()
            ->get();

        $recent = MentorRequest::where('mentor_id', auth()->id())
            ->whereIn('status', [MentorRequest::STATUS_ACCEPTED, MentorRequest::STATUS_REJECTED])
            ->with('mentee')
            ->latest('responded_at')
            ->limit(20)
            ->get();

        return view('frontend.mentors.requests', compact('pending', 'recent'));
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
