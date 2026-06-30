<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\{
    EducationStream,
    CurriculumMonth,
    CurriculumWeek,
    CurriculumTask,
    CurriculumMcq,
    StudentCurriculumProgress,
};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CurriculumController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentor/curriculum/tracks
    // ─────────────────────────────────────────────
    public function tracks(): JsonResponse
    {
        $tracks = EducationStream::orderBy('sort_order')->get();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'tracks'     => $tracks,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/curriculum/tracks/{track}/months
    // ─────────────────────────────────────────────
    public function storeMonth(Request $request, int $track): JsonResponse
    {
        $trackModel = EducationStream::findOrFail($track);

        $data = $request->validate([
            'month_number'      => [
                'required',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('curriculum_months', 'month_number')->where('stream_id', $trackModel->id),
            ],
            'title'             => 'required|string|max:200',
            'theme'             => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'learning_outcomes' => 'nullable|array',
            'learning_outcomes.*' => 'string',
            'is_active'         => 'nullable|boolean',
            'sort_order'        => 'nullable|integer',
        ]);

        $month = CurriculumMonth::create([
            'stream_id'         => $trackModel->id,
            'month_number'      => $data['month_number'],
            'title'             => $data['title'],
            'theme'             => $data['theme'] ?? null,
            'description'       => $data['description'] ?? null,
            'learning_outcomes' => $data['learning_outcomes'] ?? [],
            'is_active'         => $request->boolean('is_active', true),
            'sort_order'        => $data['sort_order'] ?? $data['month_number'],
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Month created.',
            'month'      => $month,
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  PATCH /mentor/curriculum/months/{month}
    // ─────────────────────────────────────────────
    public function updateMonth(Request $request, int $month): JsonResponse
    {
        $monthModel = CurriculumMonth::findOrFail($month);

        $data = $request->validate([
            'month_number'        => [
                'sometimes',
                'integer',
                'min:1',
                'max:12',
                Rule::unique('curriculum_months', 'month_number')
                    ->where('stream_id', $monthModel->stream_id)
                    ->ignore($monthModel->id),
            ],
            'title'               => 'sometimes|string|max:200',
            'theme'               => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'learning_outcomes'   => 'nullable|array',
            'learning_outcomes.*' => 'string',
            'is_active'           => 'nullable|boolean',
            'sort_order'          => 'nullable|integer',
        ]);

        $fields = collect($data)->only([
            'month_number', 'title', 'theme', 'description', 'learning_outcomes', 'sort_order',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        $monthModel->update($fields);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Month updated.',
            'month'      => $monthModel->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /mentor/curriculum/months/{month}
    // ─────────────────────────────────────────────
    public function destroyMonth(int $month): JsonResponse
    {
        $monthModel = CurriculumMonth::findOrFail($month);
        $weekIds = $monthModel->weeks()->pluck('id')->all();

        $this->deleteProgressForWeekIds($weekIds);
        $monthModel->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Month deleted.',
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentor/curriculum/tracks/{track}/months
    // ─────────────────────────────────────────────
    public function months(int $track): JsonResponse
    {
        $trackModel = EducationStream::findOrFail($track);

        $months = $trackModel->months()
            ->withCount('weeks')
            ->orderBy('month_number')
            ->get();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'track_id'   => $trackModel->id,
            'months'     => $months,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/curriculum/months/{month}/weeks
    // ─────────────────────────────────────────────
    public function storeWeek(Request $request, int $month): JsonResponse
    {
        $monthModel = CurriculumMonth::findOrFail($month);

        $data = $request->validate([
            'week_number'  => [
                'required',
                'integer',
                'min:1',
                'max:52',
                Rule::unique('curriculum_weeks', 'week_number')->where('month_id', $monthModel->id),
            ],
            'title'        => 'required|string|max:200',
            'focus'        => 'nullable|string',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
            'sort_order'   => 'nullable|integer',
        ]);

        $week = CurriculumWeek::create([
            'month_id'     => $monthModel->id,
            'week_number'  => $data['week_number'],
            'title'        => $data['title'],
            'focus'        => $data['focus'] ?? $data['description'] ?? null,
            'is_active'    => $request->boolean('is_active', true),
            'sort_order'   => $data['sort_order'] ?? $data['week_number'],
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Week created.',
            'week'       => $week,
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  PATCH /mentor/curriculum/weeks/{week}
    // ─────────────────────────────────────────────
    public function updateWeek(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);

        $data = $request->validate([
            'week_number'  => [
                'sometimes',
                'integer',
                'min:1',
                'max:52',
                Rule::unique('curriculum_weeks', 'week_number')
                    ->where('month_id', $weekModel->month_id)
                    ->ignore($weekModel->id),
            ],
            'title'        => 'sometimes|string|max:200',
            'focus'        => 'nullable|string',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
            'sort_order'   => 'nullable|integer',
        ]);

        $fields = collect($data)->only([
            'week_number', 'title', 'sort_order',
        ])->filter(fn ($v) => $v !== null)->all();

        if (array_key_exists('focus', $data) || array_key_exists('description', $data)) {
            $fields['focus'] = $data['focus'] ?? $data['description'] ?? null;
        }

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        $weekModel->update($fields);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Week updated.',
            'week'       => $weekModel->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /mentor/curriculum/weeks/{week}
    // ─────────────────────────────────────────────
    public function destroyWeek(int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);

        $this->deleteProgressForWeekIds([$weekModel->id]);
        $weekModel->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Week deleted.',
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentor/curriculum/months/{month}/weeks
    // ─────────────────────────────────────────────
    public function weeks(int $month): JsonResponse
    {
        $monthModel = CurriculumMonth::findOrFail($month);

        $weeks = $monthModel->weeks()
            ->withCount('tasks')
            ->orderBy('week_number')
            ->get();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'month_id'   => $monthModel->id,
            'weeks'      => $weeks,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/curriculum/weeks/{week}/tasks
    // ─────────────────────────────────────────────
    public function storeTask(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);

        $data = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'type'              => 'nullable|in:task,reading,video,project,quiz,reflection',
            'plan_id'           => 'required|integer|exists:plans,id',
            'attachments'       => 'nullable|array',
            'is_required'       => 'nullable|boolean',
            'is_active'         => 'nullable|boolean',
            'submission_type'   => 'nullable|in:none,text,file,link',
        ]);

        $this->validateTaskAttachmentFiles($request);

        $attachments = $this->processUploadedAttachments(
            $request,
            $data['attachments'] ?? []
        );

        $task = CurriculumTask::create([
            'week_id'           => $weekModel->id,
            'plan_id'           => $data['plan_id'],
            'title'             => $data['title'],
            'description'       => $data['description'] ?? null,
            'type'              => $data['type'] ?? 'task',
            'attachments'       => $attachments,
            'is_required'       => $request->boolean('is_required', true),
            'is_active'         => $request->boolean('is_active', true),
            'submission_type'   => $data['submission_type'] ?? 'none',
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Task created.',
            'task'       => $task->load('plan:id,name,slug'),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  GET /mentor/curriculum/weeks/{week}/tasks
    // ─────────────────────────────────────────────
    public function tasks(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);
        $menteeId = $request->query('mentee_id');

        $tasks = $weekModel->tasks()->with('plan:id,name,slug')->orderBy('order_index')->get()->map(function (CurriculumTask $task) use ($menteeId) {
            $row = $task->toArray();
            $row['is_completed'] = false;

            if ($menteeId) {
                $row['is_completed'] = $task->isCompletedByUser((int) $menteeId);
            }

            return $row;
        });

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'week_id'    => $weekModel->id,
            'tasks'      => $tasks,
        ]);
    }

    // ─────────────────────────────────────────────
    //  PATCH /mentor/curriculum/tasks/{task}
    // ─────────────────────────────────────────────
    public function updateTask(Request $request, int $task): JsonResponse
    {
        $taskModel = CurriculumTask::findOrFail($task);

        $data = $request->validate([
            'title'             => 'sometimes|string|max:200',
            'description'       => 'nullable|string',
            'type'              => 'sometimes|in:task,reading,video,project,quiz,reflection',
            'plan_id'           => 'sometimes|integer|exists:plans,id',
            'attachments'       => 'nullable|array',
            'is_required'       => 'nullable|boolean',
            'is_active'         => 'nullable|boolean',
            'submission_type'   => 'nullable|in:none,text,file,link',
            'mentee_id'         => 'nullable|integer|exists:users,id',
            'is_completed'      => 'nullable|boolean',
        ]);

        $this->validateTaskAttachmentFiles($request);

        $taskFields = collect($data)->only([
            'title', 'description', 'type', 'plan_id', 'submission_type',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($request->hasFile('attachments')) {
            $taskFields['attachments'] = $this->processUploadedAttachments(
                $request,
                $taskModel->attachments ?? []
            );
        } elseif ($request->has('attachments') && is_array($request->input('attachments'))) {
            $taskFields['attachments'] = $request->input('attachments');
        }

        if ($request->has('is_required')) {
            $taskFields['is_required'] = $request->boolean('is_required');
        }
        if ($request->has('is_active')) {
            $taskFields['is_active'] = $request->boolean('is_active');
        }

        if (!empty($taskFields)) {
            $taskModel->update($taskFields);
        }

        $progress = null;
        if ($request->filled('mentee_id') && $request->has('is_completed')) {
            $completed = $request->boolean('is_completed');
            $progress = StudentCurriculumProgress::updateOrCreate(
                [
                    'user_id'   => (int) $data['mentee_id'],
                    'item_type' => 'task',
                    'item_id'   => $taskModel->id,
                ],
                [
                    'is_completed' => $completed,
                    'completed_at' => $completed ? now() : null,
                ]
            );
        }

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Task updated.',
            'task'       => $taskModel->fresh()->load('plan:id,name,slug'),
            'progress'   => $progress,
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /mentor/curriculum/tasks/{task}
    // ─────────────────────────────────────────────
    public function destroyTask(int $task): JsonResponse
    {
        $taskModel = CurriculumTask::findOrFail($task);

        StudentCurriculumProgress::where('item_type', 'task')
            ->where('item_id', $taskModel->id)
            ->delete();

        $taskModel->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Task deleted.',
        ]);
    }

    private function validateTaskAttachmentFiles(Request $request): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        $request->validate([
            'attachments'   => 'array',
            'attachments.*' => 'file|mimes:pdf,mp4,mov,avi,webm,mpeg,quicktime|max:102400',
        ]);
    }

    private function processUploadedAttachments(Request $request, array $existing = []): array
    {
        $attachments = $existing;

        if (!$request->hasFile('attachments')) {
            return $attachments;
        }

        foreach ($request->file('attachments') as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $path = $file->store('curriculum-tasks', 'public');
            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'url'  => url(Storage::url($path)),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $attachments;
    }

    private function deleteProgressForWeekIds(array $weekIds): void
    {
        if ($weekIds === []) {
            return;
        }

        $taskIds = CurriculumTask::whereIn('week_id', $weekIds)->pluck('id');
        $mcqIds  = CurriculumMcq::whereIn('week_id', $weekIds)->pluck('id');

        if ($taskIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'task')
                ->whereIn('item_id', $taskIds)
                ->delete();
        }

        if ($mcqIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'mcq')
                ->whereIn('item_id', $mcqIds)
                ->delete();
        }
    }
}
