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
    public function index(Request $request)
    {
        $mentee = auth()->user();
        $canViewProgress = $mentee->canAccessProgressReport();

        EducationStream::syncEnrollmentsForMentee($mentee->id);

        $personalTracks = EducationStream::query()
            ->where('mentee_id', $mentee->id)
            ->where('is_active', true)
            ->withCount('months')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $requestedTrackId = (int) $request->query('track', 0);
        $selectedTrack = $requestedTrackId > 0
            ? $personalTracks->firstWhere('id', $requestedTrackId)
            : null;

        // Prefer a track that already has months; otherwise first personal track.
        if (! $selectedTrack) {
            $selectedTrack = $personalTracks->first(fn ($t) => (int) $t->months_count > 0)
                ?? $personalTracks->first();
        }

        $enrollment = null;
        if ($selectedTrack) {
            $enrollment = MenteeEnrollment::where('mentee_id', $mentee->id)
                ->where('stream_id', $selectedTrack->id)
                ->with('stream')
                ->first();

            // Ensure an active enrollment exists so month/week links work.
            if (! $enrollment && ($selectedTrack->mentor_id || $mentee->assigned_mentor_id)) {
                $enrollment = MenteeEnrollment::updateOrCreate(
                    [
                        'mentee_id' => $mentee->id,
                        'stream_id' => $selectedTrack->id,
                    ],
                    [
                        'mentor_id'         => $selectedTrack->mentor_id ?? $mentee->assigned_mentor_id,
                        'start_date'        => now()->toDateString(),
                        'expected_end_date' => now()->addMonths(6)->toDateString(),
                        'status'            => 'active',
                        'current_month'     => 1,
                        'current_week'      => 1,
                    ]
                );
                $enrollment->load('stream');
            } elseif ($enrollment && $enrollment->status !== 'active') {
                $enrollment->update(['status' => 'active']);
            }
        }

        $catalogStreams = collect();
        $assignedMentor = $mentee->assignedMentor;

        $months = collect();
        $progress = ['percent' => 0, 'completed' => 0, 'total' => 0];
        $monthProgress = collect();

        if ($selectedTrack) {
            $months = $selectedTrack->months()->with('weeks')->orderBy('month_number')->get();

            if ($canViewProgress) {
                $progress = StudentCurriculumProgress::getOverallProgress($mentee->id, $selectedTrack->id);
                $monthProgress = $months->map(fn ($m) => array_merge(
                    ['month' => $m],
                    $m->getProgressForUser($mentee->id)
                ));
            } else {
                $monthProgress = $months->map(fn ($m) => [
                    'month' => $m,
                    'percent' => null,
                    'completed' => null,
                    'total' => null,
                ]);
            }
        }

        return view('frontend.mentee.journey', compact(
            'enrollment',
            'selectedTrack',
            'personalTracks',
            'catalogStreams',
            'assignedMentor',
            'months',
            'progress',
            'monthProgress',
            'canViewProgress'
        ));
    }

    public function month($month)
    {
        $canViewProgress = auth()->user()->canAccessProgressReport();
        $monthRecord = CurriculumMonth::with(['weeks.tasks', 'weeks.mcqs', 'stream'])->findOrFail($month);
        $this->assertEnrolledInStream($monthRecord->stream_id);

        if ($canViewProgress) {
            $progress = $monthRecord->getProgressForUser(auth()->id());
            $weekProgress = $monthRecord->weeks->map(fn ($w) => array_merge(
                ['week' => $w],
                $w->getProgressForUser(auth()->id())
            ));
        } else {
            $progress = ['percent' => null, 'completed' => null, 'total' => null];
            $weekProgress = $monthRecord->weeks->map(fn ($w) => [
                'week' => $w,
                'percent' => null,
                'completed' => null,
                'total' => null,
            ]);
        }

        return view('frontend.mentee.journey-month', [
            'month' => $monthRecord,
            'progress' => $progress,
            'weekProgress' => $weekProgress,
            'canViewProgress' => $canViewProgress,
        ]);
    }

    public function week($week)
    {
        $canViewProgress = auth()->user()->canAccessProgressReport();
        $weekRecord = CurriculumWeek::with(['tasks', 'mcqs', 'month.stream'])->findOrFail($week);
        $this->assertEnrolledInStream($weekRecord->month?->stream_id);

        $progress = $canViewProgress
            ? $weekRecord->getProgressForUser(auth()->id())
            : ['percent' => null, 'completed' => null, 'total' => null];

        $completedTaskIds = $canViewProgress
            ? StudentCurriculumProgress::where('user_id', auth()->id())
                ->where('item_type', 'task')
                ->where('is_completed', true)
                ->whereIn('item_id', $weekRecord->tasks->pluck('id'))
                ->pluck('item_id')
                ->all()
            : [];

        $mcqAttempts = $canViewProgress
            ? McqAttempt::where('user_id', auth()->id())
                ->whereIn('mcq_id', $weekRecord->mcqs->pluck('id'))
                ->latest()
                ->get()
                ->unique('mcq_id')
                ->keyBy('mcq_id')
            : collect();

        $checkin = $canViewProgress
            ? WeeklyCheckin::where('mentee_id', auth()->id())
                ->where('week_id', $weekRecord->id)
                ->first()
            : null;

        return view('frontend.mentee.journey-week', [
            'week' => $weekRecord,
            'progress' => $progress,
            'completedTaskIds' => $completedTaskIds,
            'mcqAttempts' => $mcqAttempts,
            'checkin' => $checkin,
            'canViewProgress' => $canViewProgress,
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

        $canViewProgress = auth()->user()->canAccessProgressReport();

        return response()->json([
            'message' => $canViewProgress
                ? 'Task completed!'
                : 'Task submitted. Upgrade your plan to view scores and progress.',
            'completed' => $canViewProgress,
            'progress_report_enabled' => $canViewProgress,
        ]);
    }

    public function answerMcq($mcqId, Request $request)
    {
        $mcq = CurriculumMcq::with('week.month')->findOrFail($mcqId);
        $this->assertEnrolledInStream($mcq->week?->month?->stream_id);
        $canViewProgress = auth()->user()->canAccessProgressReport();

        $request->validate(['selected_index' => 'required|integer|min:0']);

        if ($canViewProgress && $mcq->isAnsweredCorrectlyByUser(auth()->id())) {
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

        if (! $canViewProgress) {
            return response()->json([
                'message' => 'Answer submitted. Upgrade your plan to view scores and progress.',
                'progress_report_enabled' => false,
            ]);
        }

        return response()->json([
            'correct' => $isCorrect,
            'correct_index' => (int) $mcq->correct_index,
            'explanation' => $mcq->explanation,
            'points_earned' => $points,
            'progress_report_enabled' => true,
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

        $canViewProgress = auth()->user()->canAccessProgressReport();

        return response()->json([
            'message' => $canViewProgress
                ? 'Check-in submitted! Your mentor will respond soon.'
                : 'Check-in submitted. Upgrade your plan to view progress history.',
            'progress_report_enabled' => $canViewProgress,
        ]);
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

        if ($enrolled) {
            return;
        }

        // Allow access when the track belongs to this mentee even if enrollment sync lagged.
        $ownsTrack = EducationStream::where('id', $streamId)
            ->where('mentee_id', auth()->id())
            ->where('is_active', true)
            ->exists();

        if (! $ownsTrack) {
            abort(403, 'You are not enrolled in this journey.');
        }
    }
}
