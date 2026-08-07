<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\CurriculumMcq;
use App\Models\CurriculumMonth;
use App\Models\CurriculumTask;
use App\Models\CurriculumWeek;
use App\Models\EducationStream;
use App\Models\McqAttempt;
use App\Models\MenteeEnrollment;
use App\Models\StudentCurriculumProgress;
use App\Models\WeeklyCheckin;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    public function index()
    {
        $enrollment = MenteeEnrollment::where('mentee_id', auth()->id())
            ->where('status', 'active')
            ->with('stream')
            ->first();

        $streams = EducationStream::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $months = collect();
        $progress = ['percent' => 0, 'completed' => 0, 'total' => 0];
        $monthProgress = collect();

        if ($enrollment?->stream) {
            $months = $enrollment->stream->months()->with('weeks')->orderBy('month_number')->get();
            $progress = StudentCurriculumProgress::getOverallProgress(auth()->id(), $enrollment->stream_id);
            $monthProgress = $months->map(fn ($m) => array_merge(
                ['month' => $m],
                $m->getProgressForUser(auth()->id())
            ));
        }

        return view('frontend.mentee.journey', compact(
            'enrollment',
            'streams',
            'months',
            'progress',
            'monthProgress'
        ));
    }

    public function month($month)
    {
        $monthRecord = CurriculumMonth::with(['weeks.tasks', 'weeks.mcqs', 'stream'])->findOrFail($month);
        $this->assertEnrolledInStream($monthRecord->stream_id);

        $progress = $monthRecord->getProgressForUser(auth()->id());
        $weekProgress = $monthRecord->weeks->map(fn ($w) => array_merge(
            ['week' => $w],
            $w->getProgressForUser(auth()->id())
        ));

        return view('frontend.mentee.journey-month', [
            'month' => $monthRecord,
            'progress' => $progress,
            'weekProgress' => $weekProgress,
        ]);
    }

    public function week($week)
    {
        $weekRecord = CurriculumWeek::with(['tasks', 'mcqs', 'month.stream'])->findOrFail($week);
        $this->assertEnrolledInStream($weekRecord->month?->stream_id);

        $progress = $weekRecord->getProgressForUser(auth()->id());
        $completedTaskIds = StudentCurriculumProgress::where('user_id', auth()->id())
            ->where('item_type', 'task')
            ->where('is_completed', true)
            ->whereIn('item_id', $weekRecord->tasks->pluck('id'))
            ->pluck('item_id')
            ->all();

        $mcqAttempts = McqAttempt::where('user_id', auth()->id())
            ->whereIn('mcq_id', $weekRecord->mcqs->pluck('id'))
            ->latest()
            ->get()
            ->unique('mcq_id')
            ->keyBy('mcq_id');

        $checkin = WeeklyCheckin::where('mentee_id', auth()->id())
            ->where('week_id', $weekRecord->id)
            ->first();

        return view('frontend.mentee.journey-week', [
            'week' => $weekRecord,
            'progress' => $progress,
            'completedTaskIds' => $completedTaskIds,
            'mcqAttempts' => $mcqAttempts,
            'checkin' => $checkin,
        ]);
    }

    public function completeTask($taskId, Request $request)
    {
        $task = CurriculumTask::with('week.month')->findOrFail($taskId);
        $this->assertEnrolledInStream($task->week?->month?->stream_id);

        $request->validate([
            'submission_text' => 'nullable|string|max:5000',
            'submission_url' => 'nullable|url',
        ]);

        $extra = [];
        if ($task->submission_type && $task->submission_type !== 'none') {
            $extra['submission_status'] = 'submitted';
            if ($request->submission_text) {
                $extra['submission_text'] = $request->submission_text;
            }
            if ($request->submission_url) {
                $extra['submission_url'] = $request->submission_url;
            }
        }

        StudentCurriculumProgress::markComplete(
            auth()->id(),
            'task',
            $task->id,
            array_merge($extra, ['is_completed' => true])
        );

        return response()->json(['message' => 'Task completed!', 'completed' => true]);
    }

    public function answerMcq($mcqId, Request $request)
    {
        $mcq = CurriculumMcq::with('week.month')->findOrFail($mcqId);
        $this->assertEnrolledInStream($mcq->week?->month?->stream_id);

        $request->validate(['selected_index' => 'required|integer|min:0']);

        if ($mcq->isAnsweredCorrectlyByUser(auth()->id())) {
            return response()->json(['message' => 'Already answered correctly.'], 422);
        }

        $isCorrect = (int) $request->selected_index === (int) $mcq->correct_index;
        $points = $isCorrect ? (int) $mcq->points : 0;

        McqAttempt::create([
            'user_id' => auth()->id(),
            'mcq_id' => $mcq->id,
            'selected_index' => $request->selected_index,
            'is_correct' => $isCorrect,
            'points_earned' => $points,
            'attempted_at' => now(),
        ]);

        if ($isCorrect) {
            StudentCurriculumProgress::markComplete(auth()->id(), 'mcq', $mcq->id);
        }

        return response()->json([
            'correct' => $isCorrect,
            'correct_index' => (int) $mcq->correct_index,
            'explanation' => $mcq->explanation,
            'points_earned' => $points,
        ]);
    }

    public function checkin($weekId, Request $request)
    {
        $week = CurriculumWeek::with('month')->findOrFail($weekId);
        $this->assertEnrolledInStream($week->month?->stream_id);

        $data = $request->validate([
            'mood_score' => 'nullable|integer|between:1,5',
            'wins' => 'nullable|string|max:1000',
            'challenges' => 'nullable|string|max:1000',
            'questions' => 'nullable|string|max:1000',
        ]);

        WeeklyCheckin::updateOrCreate(
            ['mentee_id' => auth()->id(), 'week_id' => $week->id],
            array_merge($data, ['submitted_at' => now()])
        );

        return response()->json(['message' => 'Check-in submitted! Your mentor will respond soon.']);
    }

    private function assertEnrolledInStream(?int $streamId): void
    {
        if (! $streamId) {
            abort(404);
        }

        $enrolled = MenteeEnrollment::where('mentee_id', auth()->id())
            ->where('stream_id', $streamId)
            ->where('status', 'active')
            ->exists();

        if (! $enrolled) {
            abort(403, 'You are not enrolled in this journey.');
        }
    }
}
