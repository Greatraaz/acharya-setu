<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeeklyCheckin;
use Illuminate\Http\Request;

/**
 * Legacy admin "journey" endpoints mirrored a mentee learner UI and pointed at
 * missing views. Keep the routes stable by redirecting to the real Journey Manager,
 * while still allowing check-in replies from admin tools.
 */
class JourneyController extends Controller
{
    public function index()
    {
        return redirect()
            ->route('admin.curriculum.streams')
            ->with('success', 'Use the 6-Month Journey Manager to build and review mentee curricula.');
    }

    public function month()
    {
        return $this->index();
    }

    public function week()
    {
        return $this->index();
    }

    public function completeTask()
    {
        return $this->index();
    }

    public function answerMcq()
    {
        return $this->index();
    }

    public function submitCheckin()
    {
        return $this->index();
    }

    public function replyCheckin(Request $request, WeeklyCheckin $checkin)
    {
        $request->validate([
            'mentor_reply' => 'required|string|max:5000',
        ]);

        $checkin->update([
            'mentor_reply' => $request->mentor_reply,
            'replied_at'   => now(),
        ]);

        return back()->with('success', 'Check-in reply saved.');
    }
}
