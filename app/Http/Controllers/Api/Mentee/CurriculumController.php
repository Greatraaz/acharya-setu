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
TaskSupportingMaterial,
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
$canViewProgress = $request->user()->canAccessProgressReport();

$taskProgressMap = $canViewProgress
    ? StudentCurriculumProgress::where('user_id', $menteeId)
        ->where('item_type', 'task')
        ->get()
        ->keyBy('item_id')
    : collect();

$materialProgressMap = $canViewProgress
    ? StudentCurriculumProgress::where('user_id', $menteeId)
        ->where('item_type', 'material')
        ->get()
        ->keyBy('item_id')
    : collect();

$tracks = EducationStream::where('mentee_id', $menteeId)
    ->where('is_active', true)
    ->when($request->filled('search'), function ($q) use ($request) {
        $term = '%'.trim((string) $request->search).'%';
        $q->where(function ($inner) use ($term) {
            $inner->where('name', 'like', $term)
                ->orWhere('slug', 'like', $term);
        });
    })
    ->when($request->filled('track_id'), fn ($q) => $q->where('id', (int) $request->track_id))
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
    ->paginate((int) $request->input('per_page', 20))
    ->withQueryString();

$trackItems = collect($tracks->items())
    ->map(fn (EducationStream $track) => $this->formatTrack(
        $track,
        $menteeId,
        $taskProgressMap,
        $materialProgressMap,
        $canViewProgress
    ));

$trackItems = $trackItems->map(function (array $track) use ($canViewProgress) {
    $track['summary'] = $canViewProgress ? $this->buildTrackSummary($track) : null;
    return $track;
})->values();

$trackSummaries = $canViewProgress
    ? $trackItems->map(fn (array $track) => $track['summary'])->values()
    : collect();

return response()->json([
    'status'          => true,
    'statuscode'      => 200,
    'mentee_id'       => $menteeId,
    'summary'         => $canViewProgress ? StudentCurriculumProgress::getMenteeProgressSummary($menteeId) : null,
    'track_summaries' => $trackSummaries,
    'tracks'          => $trackItems,
    'meta'            => [
        'current_page' => $tracks->currentPage(),
        'last_page'    => $tracks->lastPage(),
        'per_page'     => $tracks->perPage(),
        'total'        => $tracks->total(),
    ],
    'filters'         => [
        'search'   => $request->filled('search') ? trim((string) $request->search) : null,
        'track_id' => $request->filled('track_id') ? (int) $request->track_id : null,
    ],
    'entitlement'     => [
        'progress_report_enabled' => $canViewProgress,
    ],
]);
}

// ─────────────────────────────────────────────
//  GET /mentee/curriculum/tasks
//  Query: search, status, type, week_id, track_id,
//         completed_from, completed_to, per_page, page
// ─────────────────────────────────────────────
public function tasks(Request $request): JsonResponse
{
$menteeId = $request->user()->id;
$canViewProgress = $request->user()->canAccessProgressReport();

$data = $request->validate([
    'search'         => 'nullable|string|max:100',
    'status'         => 'nullable|in:pending,in_progress,completed',
    'type'           => 'nullable|in:'.implode(',', array_keys(CurriculumTask::TYPES)),
    'week_id'        => 'nullable|integer',
    'track_id'       => 'nullable|integer',
    'completed_from' => 'nullable|date',
    'completed_to'   => 'nullable|date|after_or_equal:completed_from',
    'per_page'       => 'nullable|integer|min:1|max:100',
]);

$search  = trim((string) ($data['search'] ?? ''));
$perPage = $data['per_page'] ?? 20;
$status  = $canViewProgress ? ($data['status'] ?? null) : null;

$summaryQuery = CurriculumTask::where('mentee_id', $menteeId)->where('is_active', true);
$allTasks = (clone $summaryQuery)->pluck('id');
$progressMapAll = $canViewProgress
    ? StudentCurriculumProgress::where('user_id', $menteeId)
        ->where('item_type', 'task')
        ->whereIn('item_id', $allTasks)
        ->get()
        ->keyBy('item_id')
    : collect();

$summaryTotal = $allTasks->count();
$summaryCompleted = $canViewProgress
    ? $progressMapAll->where('is_completed', true)->count()
    : null;
$summaryInProgress = $canViewProgress
    ? $progressMapAll->filter(fn ($p) => ! $p->is_completed && (
        in_array($p->submission_status, ['submitted', 'reviewed', 'rejected'], true)
        || $p->submission_text
        || $p->submission_url
    ))->count()
    : null;
$summaryPending = $canViewProgress
    ? max(0, $summaryTotal - $summaryCompleted - $summaryInProgress)
    : null;

$query = CurriculumTask::where('mentee_id', $menteeId)
    ->where('is_active', true)
    ->with([
        'plan' => fn ($q) => $q->brief(),
        'week:id,month_id,week_number,title,focus',
        'week.month:id,stream_id,month_number,title',
        'week.month.stream:id,name,slug',
    ])
    ->when($search !== '', fn ($q) => $q->where('title', 'like', '%'.$search.'%'))
    ->when(! empty($data['type']), fn ($q) => $q->where('type', $data['type']))
    ->when(! empty($data['week_id']), fn ($q) => $q->where('week_id', (int) $data['week_id']))
    ->when(! empty($data['track_id']), function ($q) use ($data) {
        $q->whereHas('week.month', fn ($m) => $m->where('stream_id', (int) $data['track_id']));
    })
    ->when(! empty($data['completed_from']) || ! empty($data['completed_to']), function ($q) use ($menteeId, $data) {
        $q->whereExists(function ($sub) use ($menteeId, $data) {
            $sub->selectRaw('1')
                ->from('student_curriculum_progress as scp')
                ->whereColumn('scp.item_id', 'curriculum_tasks.id')
                ->where('scp.user_id', $menteeId)
                ->where('scp.item_type', 'task')
                ->where('scp.is_completed', 1)
                ->when(! empty($data['completed_from']), fn ($s) => $s->whereDate('scp.completed_at', '>=', $data['completed_from']))
                ->when(! empty($data['completed_to']), fn ($s) => $s->whereDate('scp.completed_at', '<=', $data['completed_to']));
        });
    })
    ->when($status === 'completed', function ($q) use ($menteeId) {
        $q->whereExists(function ($sub) use ($menteeId) {
            $sub->selectRaw('1')
                ->from('student_curriculum_progress as scp')
                ->whereColumn('scp.item_id', 'curriculum_tasks.id')
                ->where('scp.user_id', $menteeId)
                ->where('scp.item_type', 'task')
                ->where('scp.is_completed', 1);
        });
    })
    ->when($status === 'in_progress', function ($q) use ($menteeId) {
        $q->whereExists(function ($sub) use ($menteeId) {
            $sub->selectRaw('1')
                ->from('student_curriculum_progress as scp')
                ->whereColumn('scp.item_id', 'curriculum_tasks.id')
                ->where('scp.user_id', $menteeId)
                ->where('scp.item_type', 'task')
                ->where('scp.is_completed', 0)
                ->where(function ($inner) {
                    $inner->whereIn('scp.submission_status', ['submitted', 'reviewed', 'rejected'])
                        ->orWhereNotNull('scp.submission_text')
                        ->orWhereNotNull('scp.submission_url');
                });
        });
    })
    ->when($status === 'pending', function ($q) use ($menteeId) {
        $q->whereNotExists(function ($sub) use ($menteeId) {
            $sub->selectRaw('1')
                ->from('student_curriculum_progress as scp')
                ->whereColumn('scp.item_id', 'curriculum_tasks.id')
                ->where('scp.user_id', $menteeId)
                ->where('scp.item_type', 'task')
                ->where(function ($inner) {
                    $inner->where('scp.is_completed', 1)
                        ->orWhereIn('scp.submission_status', ['submitted', 'reviewed', 'rejected'])
                        ->orWhereNotNull('scp.submission_text')
                        ->orWhereNotNull('scp.submission_url');
                });
        });
    })
    ->orderBy('week_id')
    ->orderBy('order_index');

$paginator = $query->paginate($perPage)->withQueryString();

$progressMap = $canViewProgress
    ? StudentCurriculumProgress::where('user_id', $menteeId)
        ->where('item_type', 'task')
        ->whereIn('item_id', collect($paginator->items())->pluck('id'))
        ->get()
        ->keyBy('item_id')
    : collect();

$taskList = collect($paginator->items())
    ->map(fn (CurriculumTask $task) => $this->formatTaskWithStatus(
        $task,
        $canViewProgress ? $progressMap->get($task->id) : null,
        $canViewProgress
    ))
    ->values();

return response()->json([
    'status'     => true,
    'statuscode' => 200,
    'mentee_id'  => $menteeId,
    'summary'    => $canViewProgress ? [
        'total'       => $summaryTotal,
        'completed'   => $summaryCompleted,
        'in_progress' => $summaryInProgress,
        'pending'     => $summaryPending,
        'percent'     => $summaryTotal ? (int) round($summaryCompleted / $summaryTotal * 100) : 0,
    ] : null,
    'tasks'      => $taskList,
    'meta'       => [
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
        'per_page'     => $paginator->perPage(),
        'total'        => $paginator->total(),
    ],
    'filters'    => [
        'search'         => $search !== '' ? $search : null,
        'status'         => $status,
        'type'           => $data['type'] ?? null,
        'week_id'        => isset($data['week_id']) ? (int) $data['week_id'] : null,
        'track_id'       => isset($data['track_id']) ? (int) $data['track_id'] : null,
        'completed_from' => $data['completed_from'] ?? null,
        'completed_to'   => $data['completed_to'] ?? null,
    ],
    'entitlement'=> [
        'progress_report_enabled' => $canViewProgress,
    ],
]);
}

// ─────────────────────────────────────────────
//  GET /mentee/curriculum/mcqs
//  Query: search, status, week_id, track_id,
//         attempted_from, attempted_to, per_page, page
// ─────────────────────────────────────────────
public function mcqs(Request $request): JsonResponse
{
$menteeId = $request->user()->id;
$canViewProgress = $request->user()->canAccessProgressReport();

$data = $request->validate([
    'search'          => 'nullable|string|max:100',
    'status'          => 'nullable|in:pending,in_progress,completed',
    'week_id'         => 'nullable|integer',
    'track_id'        => 'nullable|integer',
    'attempted_from'  => 'nullable|date',
    'attempted_to'    => 'nullable|date|after_or_equal:attempted_from',
    'per_page'        => 'nullable|integer|min:1|max:100',
]);

$search  = trim((string) ($data['search'] ?? ''));
$perPage = $data['per_page'] ?? 20;
$status  = $canViewProgress ? ($data['status'] ?? null) : null;

$baseTopics = CurriculumMcqTopic::where('mentee_id', $menteeId)
    ->where('is_active', true)
    ->with(['mcqs' => fn ($q) => $q->where('is_active', true)->orderBy('order_index')])
    ->get();

$allMcqsFormatted = $baseTopics
    ->flatMap(fn (CurriculumMcqTopic $topic) => $this->formatMcqTopic($topic, $menteeId, $canViewProgress)['mcqs'])
    ->values();

$summaryTotal = $allMcqsFormatted->count();
$summaryCompleted = $canViewProgress ? $allMcqsFormatted->where('status', 'completed')->count() : null;
$summaryInProgress = $canViewProgress ? $allMcqsFormatted->where('status', 'in_progress')->count() : null;
$summaryPending = $canViewProgress ? $allMcqsFormatted->where('status', 'pending')->count() : null;

$query = CurriculumMcqTopic::where('mentee_id', $menteeId)
    ->where('is_active', true)
    ->with([
        'mcqs' => fn ($q) => $q->where('is_active', true)->orderBy('order_index'),
        'week:id,month_id,week_number,title,focus',
        'week.month:id,stream_id,month_number,title',
        'week.month.stream:id,name,slug',
    ])
    ->when($search !== '', function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
            $inner->where('name', 'like', '%'.$search.'%')
                ->orWhereHas('mcqs', fn ($m) => $m->where('question', 'like', '%'.$search.'%'));
        });
    })
    ->when(! empty($data['week_id']), fn ($q) => $q->where('week_id', (int) $data['week_id']))
    ->when(! empty($data['track_id']), function ($q) use ($data) {
        $q->whereHas('week.month', fn ($m) => $m->where('stream_id', (int) $data['track_id']));
    })
    ->when(! empty($data['attempted_from']) || ! empty($data['attempted_to']), function ($q) use ($menteeId, $data) {
        $q->whereHas('mcqs', function ($mcqQ) use ($menteeId, $data) {
            $mcqQ->whereHas('attempts', function ($a) use ($menteeId, $data) {
                $a->where('user_id', $menteeId)
                    ->when(! empty($data['attempted_from']), fn ($s) => $s->whereDate('attempted_at', '>=', $data['attempted_from']))
                    ->when(! empty($data['attempted_to']), fn ($s) => $s->whereDate('attempted_at', '<=', $data['attempted_to']));
            });
        });
    })
    ->orderBy('week_id')
    ->orderBy('order_index');

$paginator = $query->paginate($perPage)->withQueryString();

$formattedTopics = collect($paginator->items())
    ->map(fn (CurriculumMcqTopic $topic) => $this->formatMcqTopicWithContext($topic, $menteeId, $canViewProgress))
    ->values();

if ($status) {
    $formattedTopics = $formattedTopics
        ->map(function (array $topic) use ($status) {
            $topic['mcqs'] = collect($topic['mcqs'] ?? [])
                ->filter(fn ($mcq) => ($mcq['status'] ?? null) === $status)
                ->values();

            return $topic;
        })
        ->filter(fn (array $topic) => collect($topic['mcqs'])->isNotEmpty())
        ->values();
}

return response()->json([
    'status'     => true,
    'statuscode' => 200,
    'mentee_id'  => $menteeId,
    'summary'    => $canViewProgress ? [
        'total'       => $summaryTotal,
        'completed'   => $summaryCompleted,
        'in_progress' => $summaryInProgress,
        'pending'     => $summaryPending,
        'percent'     => $summaryTotal ? (int) round($summaryCompleted / $summaryTotal * 100) : 0,
    ] : null,
    'mcq_topics' => $formattedTopics,
    'meta'       => [
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
        'per_page'     => $paginator->perPage(),
        'total'        => $paginator->total(),
    ],
    'filters'    => [
        'search'         => $search !== '' ? $search : null,
        'status'         => $status,
        'week_id'        => isset($data['week_id']) ? (int) $data['week_id'] : null,
        'track_id'       => isset($data['track_id']) ? (int) $data['track_id'] : null,
        'attempted_from' => $data['attempted_from'] ?? null,
        'attempted_to'   => $data['attempted_to'] ?? null,
    ],
    'entitlement'=> [
        'progress_report_enabled' => $canViewProgress,
    ],
]);
}

// ─────────────────────────────────────────────
//  GET /mentee/curriculum/admin-mcqs
//  Query: search, per_page, page
// ─────────────────────────────────────────────
public function adminMcqs(Request $request): JsonResponse
{
$menteeId = $request->user()->id;

$data = $request->validate([
    'search'   => 'nullable|string|max:100',
    'per_page' => 'nullable|integer|min:1|max:100',
]);

$search  = trim((string) ($data['search'] ?? ''));
$perPage = $data['per_page'] ?? 20;

$paginator = Quiz::where('is_published', true)
    ->where('is_active', true)
    ->with([
        'questions' => fn ($q) => $q->orderBy('order'),
        'questions.options' => fn ($q) => $q->orderBy('order'),
        'creator:id,name',
    ])
    ->withCount('questions')
    ->when($search !== '', function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
            $inner->where('title', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%');
        });
    })
    ->latest()
    ->paginate($perPage)
    ->withQueryString();

$quizzes = collect($paginator->items())
    ->map(fn (Quiz $quiz) => $this->formatQuiz($quiz))
    ->values();

return response()->json([
    'status'     => true,
    'statuscode' => 200,
    'mentee_id'  => $menteeId,
    'quizzes'    => $quizzes,
    'meta'       => [
        'current_page' => $paginator->currentPage(),
        'last_page'    => $paginator->lastPage(),
        'per_page'     => $paginator->perPage(),
        'total'        => $paginator->total(),
    ],
    'filters'    => [
        'search' => $search !== '' ? $search : null,
    ],
]);
}

private function formatTrack(EducationStream $track, int $menteeId, $taskProgressMap = null, $materialProgressMap = null, bool $includeProgress = true): array
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
            'tasks'        => $week->tasks->map(fn (CurriculumTask $task) => $this->formatTaskWithStatus(
                $task,
                $includeProgress
                    ? ($taskProgressMap?->get($task->id) ?? $this->getTaskProgress($menteeId, $task->id))
                    : null,
                $includeProgress
            ))->values(),
            'mcq_topics'   => $week->mcqTopics->map(fn (CurriculumMcqTopic $topic) => $this->formatMcqTopic($topic, $menteeId, $includeProgress))->values(),
            'materials'    => $week->supportingMaterials
                ->map(fn ($material) => $this->formatMaterialWithStatus(
                    $material,
                    $includeProgress ? $materialProgressMap?->get($material->id) : null,
                    $includeProgress
                ))
                ->values(),
        ])->values(),
    ])->values(),
];
}

private function formatMaterialWithStatus(TaskSupportingMaterial $material, ?StudentCurriculumProgress $progress, bool $includeProgress = true): array
{
$payload = [
    'id'           => $material->id,
    'task_id'      => $material->task_id,
    'week_id'      => $material->week_id,
    'mentee_id'    => $material->mentee_id,
    'mentor_id'    => $material->mentor_id,
    'title'        => $material->title,
    'description'  => $material->description,
    'type'         => $material->type,
    'file_name'    => $material->file_name,
    'file_path'    => $material->file_path,
    'file_url'     => $material->file_url,
    'mime_type'    => $material->mime_type,
    'file_size'    => $material->file_size,
    'link'         => $material->link,
    'is_active'    => $material->is_active,
    'sort_order'   => $material->sort_order,
    'created_at'   => $material->created_at,
    'updated_at'   => $material->updated_at,
];

if ($includeProgress) {
    $payload['status'] = $progress?->is_completed ? 'completed' : 'pending';
    $payload['is_completed'] = (bool) ($progress?->is_completed ?? false);
    $payload['completed_at'] = $progress?->completed_at;
}

return $payload;
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
return $this->formatTaskWithStatus($task, $this->getTaskProgress($menteeId, $task->id));
}

private function formatTaskWithStatus(CurriculumTask $task, ?StudentCurriculumProgress $progress, bool $includeProgress = true): array
{
$status = $includeProgress ? $this->resolveTaskStatus($progress) : 'pending';

$payload = [
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

if ($includeProgress) {
    $payload['status'] = $status;
    $payload['is_completed'] = $status === 'completed';
    $payload['submission_status'] = $progress?->submission_status ?? 'none';
    $payload['completed_at'] = $progress?->completed_at;
    $payload['mentor_feedback'] = $progress?->mentor_feedback;
}

return $payload;
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

private function getTaskProgress(int $menteeId, int $taskId): ?StudentCurriculumProgress
{
return StudentCurriculumProgress::where('user_id', $menteeId)
    ->where('item_type', 'task')
    ->where('item_id', $taskId)
    ->first();
}

private function formatMcqTopic(CurriculumMcqTopic $topic, int $menteeId, bool $includeProgress = true): array
{
return [
    'id'          => $topic->id,
    'name'        => $topic->name,
    'description' => $topic->description,
    'order_index' => $topic->order_index,
    'is_active'   => $topic->is_active,
    'mcqs'        => $topic->mcqs->map(fn (CurriculumMcq $mcq) => $this->formatMcqForMentee($mcq, $menteeId, $includeProgress))->values(),
];
}

private function formatMcqTopicWithContext(CurriculumMcqTopic $topic, int $menteeId, bool $includeProgress = true): array
{
return array_merge($this->formatMcqTopic($topic, $menteeId, $includeProgress), [
    'week_id' => $topic->week_id,
    'track'   => $topic->week?->month?->stream ? [
        'id'   => $topic->week->month->stream->id,
        'name' => $topic->week->month->stream->name,
        'slug' => $topic->week->month->stream->slug,
    ] : null,
    'month'   => $topic->week?->month ? [
        'id'           => $topic->week->month->id,
        'month_number' => $topic->week->month->month_number,
        'title'        => $topic->week->month->title,
    ] : null,
    'week'    => $topic->week ? [
        'id'          => $topic->week->id,
        'week_number' => $topic->week->week_number,
        'title'       => $topic->week->title,
        'focus'       => $topic->week->focus,
    ] : null,
]);
}

private function formatMcqForMentee(CurriculumMcq $mcq, int $menteeId, bool $includeProgress = true): array
{
$options = $mcq->options ?? [];

$payload = [
    'id'          => $mcq->id,
    'question'    => $mcq->question,
    'options'     => $options,
    'difficulty'  => $mcq->difficulty,
    'order_index' => $mcq->order_index,
];

if (! $includeProgress) {
    return $payload;
}

$completed = $mcq->isAnsweredCorrectlyByUser($menteeId);
$attempt   = $mcq->getAttemptForUser($menteeId);
$correctIndex = $mcq->correct_index;

return array_merge($payload, [
    'correct_index'  => $correctIndex,
    'correct_answer' => is_numeric($correctIndex) && array_key_exists((int) $correctIndex, $options)
        ? $options[(int) $correctIndex]
        : null,
    'explanation'    => $mcq->explanation,
    'points'         => $mcq->points,
    'is_completed'   => $completed,
    'status'         => $completed ? 'completed' : ($attempt ? 'in_progress' : 'pending'),
    'last_attempt'   => $attempt ? [
        'is_correct'    => $attempt->is_correct,
        'points_earned' => $attempt->points_earned,
        'attempted_at'  => $attempt->attempted_at,
    ] : null,
]);
}

private function buildTrackSummary(array $track): array
{
$tasks = collect($track['months'] ?? [])
    ->flatMap(fn (array $month) => $month['weeks'] ?? [])
    ->flatMap(fn (array $week) => $week['tasks'] ?? []);

$mcqs = collect($track['months'] ?? [])
    ->flatMap(fn (array $month) => $month['weeks'] ?? [])
    ->flatMap(fn (array $week) => $week['mcq_topics'] ?? [])
    ->flatMap(fn (array $topic) => $topic['mcqs'] ?? []);

$materials = collect($track['months'] ?? [])
    ->flatMap(fn (array $month) => $month['weeks'] ?? [])
    ->flatMap(fn (array $week) => $week['materials'] ?? []);

$taskTotal = $tasks->count();
$taskCompleted = $tasks->where('status', 'completed')->count();

$mcqTotal = $mcqs->count();
$mcqCompleted = $mcqs->where('status', 'completed')->count();

$materialTotal = $materials->count();
$materialCompleted = $materials->where('status', 'completed')->count();

$overallTotal = $taskTotal + $mcqTotal + $materialTotal;
$overallCompleted = $taskCompleted + $mcqCompleted + $materialCompleted;

return [
    'track_id'   => $track['id'] ?? null,
    'track_name' => $track['name'] ?? null,
    'track_slug' => $track['slug'] ?? null,
    'overall'    => [
        'total'     => $overallTotal,
        'completed' => $overallCompleted,
        'percent'   => $overallTotal ? (int) round($overallCompleted / $overallTotal * 100) : 0,
    ],
    'tasks'      => [
        'total'     => $taskTotal,
        'completed' => $taskCompleted,
        'percent'   => $taskTotal ? (int) round($taskCompleted / $taskTotal * 100) : 0,
    ],
    'mcqs'       => [
        'total'     => $mcqTotal,
        'completed' => $mcqCompleted,
        'percent'   => $mcqTotal ? (int) round($mcqCompleted / $mcqTotal * 100) : 0,
    ],
    'materials'  => [
        'total'     => $materialTotal,
        'completed' => $materialCompleted,
        'percent'   => $materialTotal ? (int) round($materialCompleted / $materialTotal * 100) : 0,
    ],
];
}
}
