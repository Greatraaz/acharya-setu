<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MockInterviewRequest;

class MockInterviewController extends Controller
{
    public function index()
    {
        $items = MockInterviewRequest::query()
            ->with('user')
            ->where('mentor_id', auth()->id())
            ->whereIn('status', [
                MockInterviewRequest::STATUS_CONFIRMED,
                MockInterviewRequest::STATUS_COMPLETED,
            ])
            ->latest('preferred_at')
            ->paginate(15);

        return view('frontend.mentors.mock-interviews.index', compact('items'));
    }

    public function show(MockInterviewRequest $mockInterview)
    {
        abort_unless((int) $mockInterview->mentor_id === (int) auth()->id(), 404);

        $mockInterview->load('user');

        return view('frontend.mentors.mock-interviews.show', ['item' => $mockInterview]);
    }
}
