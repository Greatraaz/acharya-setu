<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\{
    CurriculumMcq,
    CurriculumMcqTopic,
    CurriculumTask,
    EducationStream,
    MenteeEnrollment,
    StudentCurriculumProgress,
    TaskSupportingMaterial,
    User,
};
use Illuminate\Http\{JsonResponse, Request};

class ProgressController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentor/mentees/{mentee}/curriculum
    //  Same shape as GET /mentee/curriculum, for this mentee.
    // ─────────────────────────────────────────────
    public function curriculum(Request $request, int $mentee): JsonResponse
    {
        $mentor = $request->user();
        $menteeModel = $this->findMentorMentee($mentor->id, $mentee);
        $menteeId = $menteeModel->id;

        $taskProgressMap = StudentCurriculumProgress::where('user_id', $menteeId)
            ->where('item_type', 'task')
            ->get()
            ->keyBy('item_id');

        $materialProgressMap = StudentCurriculumProgress::where('user_id', $menteeId)
            ->where('item_type', 'material')
            ->get()
            ->keyBy('item_id');

        $tracks = EducationStream::where('mentee_id', $menteeId)
            ->where('mentor_id', $mentor->id)
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
            ->map(fn (EducationStream $track) => $this->formatTrack(
                $track,
                $menteeId,
                $taskProgressMap,
                $materialProgressMap
            ));

        $summary = StudentCurriculumProgress::getMenteeProgressSummary($menteeId, $mentor->id);

        $tracks = $tracks->map(function (array $track) {
            $track['summary'] = $this->buildTrackSummary($track);

            return $track;
        })->values();

        $trackSummaries = $tracks
            ->map(fn (array $track) => $track['summary'])
            ->values();

        return response()->json([
            'status'          => true,
            'statuscode'      => 200,
            'mentee_id'       => $menteeId,
            'summary'         => $summary,
            'track_summaries' => $trackSummaries,
            'tracks'          => $tracks,
            'total'           => $tracks->count(),
        ]);
    }

    private function findMentorMentee(int $mentorId, int $menteeId): User
    {
        $isLinked = MenteeEnrollment::where('mentor_id', $mentorId)->where('mentee_id', $menteeId)->exists()
            || User::where('id', $menteeId)->where('role', 'mentee')->where('assigned_mentor_id', $mentorId)->exists()
            || EducationStream::where('mentor_id', $mentorId)->where('mentee_id', $menteeId)->exists();

        if (! $isLinked) {
            abort(404, 'Mentee not found for this mentor.');
        }

        return User::where('id', $menteeId)->where('role', 'mentee')->firstOrFail();
    }

    private function formatTrack(
        EducationStream $track,
        int $menteeId,
        $taskProgressMap = null,
        $materialProgressMap = null
    ): array {
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
                        $taskProgressMap?->get($task->id)
                    ))->values(),
                    'mcq_topics'   => $week->mcqTopics->map(fn (CurriculumMcqTopic $topic) => $this->formatMcqTopic($topic, $menteeId))->values(),
                    'materials'    => $week->supportingMaterials
                        ->map(fn ($material) => $this->formatMaterialWithStatus($material, $materialProgressMap?->get($material->id)))
                        ->values(),
                ])->values(),
            ])->values(),
        ];
    }

    private function formatMaterialWithStatus(TaskSupportingMaterial $material, ?StudentCurriculumProgress $progress): array
    {
        return [
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
            'status'       => $progress?->is_completed ? 'completed' : 'pending',
            'is_completed' => (bool) ($progress?->is_completed ?? false),
            'completed_at' => $progress?->completed_at,
        ];
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
            'submission_text'   => $progress?->submission_text,
            'submission_url'    => $progress?->submission_url,
            'progress_id'       => $progress?->id,
            'completed_at'      => $progress?->completed_at,
            'mentor_feedback'   => $progress?->mentor_feedback,
            'reviewed_at'       => $progress?->reviewed_at,
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

    private function formatMcqTopic(CurriculumMcqTopic $topic, int $menteeId): array
    {
        return [
            'id'          => $topic->id,
            'name'        => $topic->name,
            'description' => $topic->description,
            'order_index' => $topic->order_index,
            'is_active'   => $topic->is_active,
            'mcqs'        => $topic->mcqs->map(function (CurriculumMcq $mcq) use ($menteeId) {
                $completed = $mcq->isAnsweredCorrectlyByUser($menteeId);
                $attempt   = $mcq->getAttemptForUser($menteeId);

                return [
                    'id'           => $mcq->id,
                    'question'     => $mcq->question,
                    'options'      => $mcq->options,
                    'difficulty'   => $mcq->difficulty,
                    'points'       => $mcq->points,
                    'order_index'  => $mcq->order_index,
                    'is_completed' => $completed,
                    'status'       => $completed ? 'completed' : ($attempt ? 'in_progress' : 'pending'),
                    'last_attempt' => $attempt ? [
                        'is_correct'    => $attempt->is_correct,
                        'points_earned' => $attempt->points_earned,
                        'attempted_at'  => $attempt->attempted_at,
                    ] : null,
                ];
            })->values(),
        ];
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
