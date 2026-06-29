<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    EducationStream, CurriculumMonth, CurriculumWeek,
    CurriculumTask, CurriculumMcq, StudentCurriculumProgress,
    McqAttempt, MenteeEnrollment, WeeklyCheckin
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class JourneyController extends Controller
{
    // ── My Journey dashboard ──────────────────────────────────
    public function index()
    {
        /** @var int $userId */
        $userId = auth()->id();
 
        $enrollment = MenteeEnrollment::where('mentee_id', $userId)
            ->where('status', 'active')
            ->with('stream')
            ->first();
 
        if (!$enrollment) {
            return view('mentee.journey.no-enrollment');
        }
 
        $stream   = $enrollment->stream;
        $months   = $stream->months()->with('weeks')->orderBy('month_number')->get();
        $progress = StudentCurriculumProgress::getOverallProgress($userId, $stream->id);
 
        $monthProgress = $months->map(fn($m) => array_merge(
            ['month' => $m],
            $m->getProgressForUser($userId)
        ));
 
        return view('mentee.journey.index', compact('enrollment', 'stream', 'months', 'progress', 'monthProgress'));
    }
 
    // ── Month view ────────────────────────────────────────────
    public function month(CurriculumMonth $month)
    {
        /** @var int $userId */
        $userId = auth()->id();
 
        $month->load(['weeks.tasks', 'weeks.mcqs', 'stream']);
        $progress     = $month->getProgressForUser($userId);
        $weekProgress = $month->weeks->map(fn($w) => array_merge(
            ['week' => $w],
            $w->getProgressForUser($userId)
        ));
 
        return view('mentee.journey.month', compact('month', 'progress', 'weekProgress'));
    }
 
    // ── Week view ─────────────────────────────────────────────
    public function week(CurriculumWeek $week)
    {
        /** @var int $userId */
        $userId = auth()->id();
 
        $week->load(['tasks', 'mcqs', 'month.stream']);
        $progress = $week->getProgressForUser($userId);
 
        $taskStatuses = $week->tasks->mapWithKeys(fn($t) => [
            $t->id => $t->getProgressForUser($userId),
        ]);
        $mcqStatuses = $week->mcqs->mapWithKeys(fn($m) => [
            $m->id => $m->getAttemptForUser($userId),
        ]);
 
        $checkin = WeeklyCheckin::where('mentee_id', $userId)
            ->where('week_id', $week->id)
            ->first();
 
        return view('mentee.journey.week', compact('week', 'progress', 'taskStatuses', 'mcqStatuses', 'checkin'));
    }
 
    // ── Mark task complete / submit ───────────────────────────
    public function completeTask(Request $request, CurriculumTask $task)
    {
        /** @var int $userId */
        $userId = auth()->id();
        $extra  = [];
 
        if ($task->submission_type && $task->submission_type !== 'none') {
            $request->validate([
                'submission_text' => 'nullable|string|max:5000',
                'submission_url'  => 'nullable|url',
                'submission_file' => 'nullable|file|max:10240',
            ]);
 
            $extra['submission_status'] = 'submitted';
 
            if ($request->hasFile('submission_file')) {
                $path = $request->file('submission_file')->store("submissions/{$userId}", 'public');
                $extra['submission_url'] = Storage::url($path);
            }
 
            if ($request->submission_text) {
                $extra['submission_text'] = $request->submission_text;
            }
 
            if ($request->submission_url) {
                $extra['submission_url'] = $request->submission_url;
            }
 
            // Auto-complete only if no review required
            $complete = false;
        } else {
            $complete = true;
        }
 
        StudentCurriculumProgress::markComplete(
            $userId,
            'task',
            $task->id,
            array_merge($extra, ['is_completed' => $complete])
        );
 
        return response()->json(['success' => true, 'completed' => $complete]);
    }
 
    // ── Answer MCQ ────────────────────────────────────────────
    public function answerMcq(Request $request, CurriculumMcq $mcq)
    {
        /** @var int $userId */
        $userId = auth()->id();
 
        $request->validate(['selected_index' => 'required|integer|min:0']);
 
        // Prevent re-attempt if already correct
        if ($mcq->isAnsweredCorrectlyByUser($userId)) {
            return response()->json(['error' => 'Already answered correctly.'], 422);
        }
 
        $correct = (int) $request->selected_index === (int) $mcq->correct_index;
        $points  = $correct ? $mcq->points : 0;
 
        McqAttempt::create([
            'user_id'        => $userId,
            'mcq_id'         => $mcq->id,
            'selected_index' => $request->selected_index,
            'is_correct'     => $correct,
            'points_earned'  => $points,
            'attempted_at'   => now(),
        ]);
 
        if ($correct) {
            StudentCurriculumProgress::markComplete($userId, 'mcq', $mcq->id);
        }
 
        return response()->json([
            'correct'       => $correct,
            'correct_index' => (int) $mcq->correct_index,
            'explanation'   => $mcq->explanation,
            'points_earned' => $points,
        ]);
    }
 
    // ── Weekly check-in ───────────────────────────────────────
    public function submitCheckin(Request $request, CurriculumWeek $week)
    {
        /** @var int $userId */
        $userId = auth()->id();
 
        $data = $request->validate([
            'mood_score' => 'required|integer|between:1,5',
            'wins'       => 'nullable|string|max:1000',
            'challenges' => 'nullable|string|max:1000',
            'questions'  => 'nullable|string|max:1000',
        ]);
 
        WeeklyCheckin::updateOrCreate(
            ['mentee_id' => $userId, 'week_id' => $week->id],
            array_merge($data, ['submitted_at' => now()])
        );
 
        return redirect()->back()->with('success', 'Check-in submitted!');
    }
 
    // ── Mentor: reply to check-in ─────────────────────────────
    public function replyCheckin(Request $request, WeeklyCheckin $checkin)
    {
        $request->validate(['mentor_response' => 'required|string|max:2000']);
 
        $checkin->update([
            'mentor_response'   => $request->mentor_response,
            'mentor_replied_at' => now(),
        ]);
 
        return redirect()->back()->with('success', 'Response sent.');
    }
}