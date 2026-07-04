<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\{
    CurriculumMcq,
    CurriculumMcqTopic,
    CurriculumTask,
    EducationStream,
    Quiz,
    QuizOption,
    QuizQuestion,
    StudentCurriculumProgress,
};
use Illuminate\Http\{JsonResponse, Request};

class CurriculumController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentee/curriculum
    //  Full mentor-assigned curriculum for logged-in mentee.
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $menteeId = $request->user()->id;

        $tracks = EducationStream::where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->with([
                'mentor:id,name,email,avatar_url',
                'months' => fn ($q) => $q
                    ->where('mentee_id', $menteeId)
                    ->where('is_active', true)
                    ->orderBy('month_number'),
                'months.weeks' => fn ($q) => $q
                    ->where('mentee_id', $menteeId)
                    ->where('is_active', true)
                    ->orderBy('week_number'),
                'months.weeks.tasks' => fn ($q) => $q
                    ->where('mentee_id', $menteeId)
                    ->where('is_active', true)
                    ->orderBy('order_index')
                    ->with(['plan' => fn ($q) => $q->brief()]),
                'months.weeks.mcqTopics' => fn ($q) => $q
                    ->where('mentee_id', $menteeId)
                    ->where('is_active', true)
                    ->orderBy('order_index')
                    ->with(['mcqs' => fn ($q) => $q->where('is_active', true)->orderBy('order_index')]),
                'months.weeks.supportingMaterials' => fn ($q) => $q
                    ->where('mentee_id', $menteeId)
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
            ])
            ->orderBy('sort_order')
            ->get()
            ->map(fn (EducationStream $track) => $this->formatTrack($track, $menteeId));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mentee_id'  => $menteeId,
            'tracks'     => $tracks,
            'total'      => $tracks->count(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentee/curriculum/tasks
    //  Curriculum tasks only, with status + completion summary.
    // ─────────────────────────────────────────────
    public function tasks(Request $request): JsonResponse
    {
        $menteeId = $request->user()->id;

        $tasks = CurriculumTask::where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->with([
                'plan' => fn ($q) => $q->brief(),
                'week:id,month_id,week_number,title,focus',
                'week.month:id,stream_id,month_number,title',
                'week.month.stream:id,name,slug',
            ])
            ->orderBy('week_id')
            ->orderBy('order_index')
            ->get();

        $progressMap = StudentCurriculumProgress::where('user_id', $menteeId)
            ->where('item_type', 'task')
            ->whereIn('item_id', $tasks->pluck('id'))
            ->get()
            ->keyBy('item_id');

        $formatted = $tasks
            ->map(fn (CurriculumTask $task) => $this->formatTaskWithStatus($task, $progressMap->get($task->id)))
            ->values();

        $completed  = $formatted->where('status', 'completed')->count();
        $inProgress = $formatted->where('status', 'in_progress')->count();
        $pending    = $formatted->where('status', 'pending')->count();
        $total      = $formatted->count();

        $taskList = $formatted;
        if ($request->filled('status')) {
            $taskList = $formatted->filter(fn ($task) => $task['status'] === $request->status)->values();
        }

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mentee_id'  => $menteeId,
            'summary'    => [
                'total'       => $total,
                'completed'   => $completed,
                'in_progress' => $inProgress,
                'pending'     => $pending,
                'percent'     => $total ? (int) round($completed / $total * 100) : 0,
            ],
            'tasks'      => $taskList,
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentee/curriculum/admin-mcqs
    //  Quizzes created from admin panel (quizzes + questions + options).
    // ─────────────────────────────────────────────
    public function adminMcqs(Request $request): JsonResponse
    {
        $menteeId = $request->user()->id;

        $quizzes = Quiz::where('is_published', true)
            ->where('is_active', true)
            ->with([
                'questions' => fn ($q) => $q->orderBy('order'),
                'questions.options' => fn ($q) => $q->orderBy('order'),
                'creator:id,name',
            ])
            ->withCount('questions')
            ->latest()
            ->get()
            ->map(fn (Quiz $quiz) => $this->formatQuiz($quiz));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mentee_id'  => $menteeId,
            'quizzes'    => $quizzes,
            'total'      => $quizzes->count(),
        ]);
    }

    private function formatTrack(EducationStream $track, int $menteeId): array
    {
        return [
            'id'          => $track->id,
            'name'        => $track->name,
            'slug'        => $track->slug,
            'description' => $track->description,
            'is_active'   => $track->is_active,
            'sort_order'  => $track->sort_order,
            'mentor'      => $track->mentor,
            'months'      => $track->months->map(fn ($month) => [
                'id'                => $month->id,
                'month_number'      => $month->month_number,
                'title'             => $month->title,
                'theme'             => $month->theme,
                'description'       => $month->description,
                'learning_outcomes' => $month->learning_outcomes,
                'is_active'         => $month->is_active,
                'sort_order'        => $month->sort_order,
                'weeks'             => $month->weeks->map(fn ($week) => [
                    'id'           => $week->id,
                    'week_number'  => $week->week_number,
                    'title'        => $week->title,
                    'focus'        => $week->focus,
                    'video_url'    => $week->video_url,
                    'resources'    => $week->resources,
                    'is_active'    => $week->is_active,
                    'sort_order'   => $week->sort_order,
                    'tasks'        => $week->tasks->map(fn (CurriculumTask $task) => $this->formatTask($task, $menteeId))->values(),
                    'mcq_topics'   => $week->mcqTopics->map(fn (CurriculumMcqTopic $topic) => $this->formatMcqTopic($topic))->values(),
                    'materials'    => $week->supportingMaterials->values(),
                ])->values(),
            ])->values(),
        ];
    }

    private function formatQuiz(Quiz $quiz): array
    {
        return [
            'id'               => $quiz->id,
            'title'            => $quiz->title,
            'description'      => $quiz->description,
            'time_limit'       => $quiz->time_limit,
            'pass_score'       => $quiz->pass_score,
            'show_results'     => $quiz->show_results,
            'questions_count'  => $quiz->questions_count,
            'total_marks'      => $quiz->questions->sum('marks'),
            'created_by'       => $quiz->creator,
            'questions'        => $quiz->questions->map(fn (QuizQuestion $question) => $this->formatQuizQuestion($question))->values(),
            'created_at'       => $quiz->created_at,
            'updated_at'       => $quiz->updated_at,
        ];
    }

    private function formatQuizQuestion(QuizQuestion $question): array
    {
        return [
            'id'       => $question->id,
            'question' => $question->question,
            'type'     => $question->type,
            'marks'    => $question->marks,
            'order'    => $question->order,
            'options'  => $question->options->map(fn (QuizOption $option) => [
                'id'          => $option->id,
                'option_text' => $option->option_text,
                'order'       => $option->order,
            ])->values(),
        ];
    }

    private function formatTask(CurriculumTask $task, int $menteeId): array
    {
        $row = $task->toArray();
        $row['is_completed'] = $task->isCompletedByUser($menteeId);

        return $row;
    }

    private function formatTaskWithStatus(CurriculumTask $task, ?StudentCurriculumProgress $progress): array
    {
        $status = $this->resolveTaskStatus($progress);

        return [
            'id'                => $task->id,
            'week_id'           => $task->week_id,
            'title'             => $task->title,
            'description'       => $task->description,
            'type'              => $task->type,
            'order_index'       => $task->order_index,
            'estimated_minutes' => $task->estimated_minutes,
            'is_required'       => $task->is_required,
            'submission_type'   => $task->submission_type,
            'attachments'       => $task->attachments,
            'plan'              => $task->plan,
            'status'            => $status,
            'is_completed'      => $status === 'completed',
            'submission_status' => $progress?->submission_status ?? 'none',
            'completed_at'      => $progress?->completed_at,
            'mentor_feedback'   => $progress?->mentor_feedback,
            'track'             => $task->week?->month?->stream ? [
                'id'   => $task->week->month->stream->id,
                'name' => $task->week->month->stream->name,
                'slug' => $task->week->month->stream->slug,
            ] : null,
            'month'             => $task->week?->month ? [
                'id'           => $task->week->month->id,
                'month_number' => $task->week->month->month_number,
                'title'        => $task->week->month->title,
            ] : null,
            'week'              => $task->week ? [
                'id'          => $task->week->id,
                'week_number' => $task->week->week_number,
                'title'       => $task->week->title,
                'focus'       => $task->week->focus,
            ] : null,
        ];
    }

    private function resolveTaskStatus(?StudentCurriculumProgress $progress): string
    {
        if (! $progress) {
            return 'pending';
        }

        if ($progress->is_completed) {
            return 'completed';
        }

        if (
            in_array($progress->submission_status, ['submitted', 'reviewed', 'rejected'], true)
            || $progress->submission_text
            || $progress->submission_url
        ) {
            return 'in_progress';
        }

        return 'pending';
    }

    private function formatMcqTopic(CurriculumMcqTopic $topic): array
    {
        return [
            'id'          => $topic->id,
            'name'        => $topic->name,
            'description' => $topic->description,
            'order_index' => $topic->order_index,
            'is_active'   => $topic->is_active,
            'mcqs'        => $topic->mcqs->map(fn (CurriculumMcq $mcq) => $this->formatMcqForMentee($mcq))->values(),
        ];
    }

    private function formatMcqForMentee(CurriculumMcq $mcq): array
    {
        return [
            'id'          => $mcq->id,
            'question'    => $mcq->question,
            'options'     => $mcq->options,
            'difficulty'  => $mcq->difficulty,
            'points'      => $mcq->points,
            'order_index' => $mcq->order_index,
        ];
    }
}
