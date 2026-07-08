<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\{
    EducationStream,
    CurriculumMonth,
    CurriculumWeek,
    CurriculumTask,
    CurriculumMcq,
    CurriculumMcqTopic,
    StudentCurriculumProgress,
    TaskSupportingMaterial,
};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CurriculumController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentor/curriculum/tracks
    // ─────────────────────────────────────────────
    public function tracks(Request $request): JsonResponse
    {
        $tracks = EducationStream::with('mentee:id,name,email,avatar_url')
            ->where('mentor_id', $request->user()->id)
            ->when($request->filled('mentee_id'), fn ($q) => $q->where('mentee_id', $request->mentee_id))
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'tracks'     => $tracks,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/curriculum/tracks
    // ─────────────────────────────────────────────
    public function storeTrack(Request $request): JsonResponse
    {
        $mentor = $request->user();

        $data = $request->validate([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        $slug = Str::slug($data['name']);
        if (EducationStream::where('slug', $slug)->exists()) {
            $slug .= '-' . $data['mentee_id'];
        }

        $track = EducationStream::create([
            'mentee_id'   => $data['mentee_id'],
            'mentor_id'   => $mentor->id,
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $data['sort_order'] ?? 0,
        ]);

        $track->load('mentee:id,name,email,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Track created.',
            'track'      => $track,
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  PATCH /mentor/curriculum/tracks/{track}
    // ─────────────────────────────────────────────
    public function updateTrack(Request $request, int $track): JsonResponse
    {
        $trackModel = EducationStream::where('mentor_id', $request->user()->id)->findOrFail($track);

        $data = $request->validate([
            'mentee_id'   => ['sometimes', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:50',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $fields = collect($data)->only([
            'mentee_id', 'name', 'description', 'icon', 'color', 'sort_order',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        if (isset($fields['name'])) {
            $slug = Str::slug($fields['name']);
            $slugExists = EducationStream::where('slug', $slug)
                ->where('id', '!=', $trackModel->id)
                ->exists();

            if ($slugExists) {
                $slug .= '-' . ($fields['mentee_id'] ?? $trackModel->mentee_id);
            }

            $fields['slug'] = $slug;
        }

        if ($fields !== []) {
            $trackModel->update($fields);
        }

        $trackModel->load('mentee:id,name,email,avatar_url');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Track updated.',
            'track'      => $trackModel,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/curriculum/tracks/{track}/months
    // ─────────────────────────────────────────────
    public function storeMonth(Request $request, int $track): JsonResponse
    {
        $trackModel = EducationStream::findOrFail($track);

        $data = $request->validate([
            'mentee_id'         => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
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

        if (!empty($trackModel->mentee_id) && (int) $data['mentee_id'] !== (int) $trackModel->mentee_id) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'mentee_id must match this track.',
            ], 422);
        }

        $month = CurriculumMonth::create([
            'stream_id'         => $trackModel->id,
            'mentee_id'         => $data['mentee_id'],
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
            'mentee_id'    => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
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

        if (!empty($monthModel->mentee_id) && (int) $data['mentee_id'] !== (int) $monthModel->mentee_id) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'mentee_id must match this month.',
            ], 422);
        }

        $week = CurriculumWeek::create([
            'month_id'     => $monthModel->id,
            'mentee_id'    => $data['mentee_id'],
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
            ->withCount(['tasks', 'mcqTopics', 'mcqs'])
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

        $rules = [
            'mentee_id'         => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'type'              => 'nullable|in:task,reading,video,project,quiz,reflection',
            'plan_id'           => 'required|integer|exists:plans,id',
            'is_required'       => 'nullable',
            'is_active'         => 'nullable',
            'submission_type'   => ['nullable', Rule::in(array_keys(CurriculumTask::SUBMISSION_TYPES))],
        ];

        if (!$request->hasFile('attachments')) {
            $rules['attachments'] = 'nullable|array';
        }

        $data = $request->validate($rules);

        if (!empty($weekModel->mentee_id) && (int) $data['mentee_id'] !== (int) $weekModel->mentee_id) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'mentee_id must match this week.',
            ], 422);
        }

        $this->validateTaskAttachmentFiles($request);

        $attachments = $this->processUploadedAttachments(
            $request,
            $data['attachments'] ?? []
        );

        $task = CurriculumTask::create([
            'week_id'           => $weekModel->id,
            'mentee_id'         => $data['mentee_id'],
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
            'task'       => $task->load(['plan' => fn ($q) => $q->brief()]),
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  GET /mentor/curriculum/weeks/{week}/tasks
    // ─────────────────────────────────────────────
    public function tasks(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);
        $menteeId = $request->query('mentee_id');

        $tasks = $weekModel->tasks()->with(['plan' => fn ($q) => $q->brief()])->orderBy('order_index')->get()->map(function (CurriculumTask $task) use ($menteeId) {
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
    //  MCQ Topics + MCQs — single CRUD under /weeks/{week}/mcqs
    //  GET    list topics with nested mcqs
    //  POST   create topic with mcqs
    //  PATCH  update topic + replace mcqs
    //  DELETE delete topic and its mcqs
    // ─────────────────────────────────────────────
    public function mcqs(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);

        $mcqTopics = $weekModel->mcqTopics()
            ->with(['mcqs' => fn ($q) => $q->orderBy('order_index')])
            ->when($request->filled('mentee_id'), fn ($q) => $q->where('mentee_id', $request->mentee_id))
            ->orderBy('order_index')
            ->get()
            ->map(fn (CurriculumMcqTopic $topic) => $this->transformMcqTopic($topic));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'week_id'    => $weekModel->id,
            'mcq_topics' => $mcqTopics,
            'total'      => $mcqTopics->count(),
        ]);
    }

    public function storeMcq(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);

        $data = $request->validate(array_merge([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ], $this->bulkMcqRules('mcqs')));

        if (! empty($weekModel->mentee_id) && (int) $weekModel->mentee_id !== (int) $data['mentee_id']) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'mentee_id must match this week.',
            ], 422);
        }

        $existingTopic = $this->findMcqTopicByNameInWeek($weekModel, (int) $data['mentee_id'], $data['name']);

        if ($existingTopic) {
            $nextOrder = ((int) $existingTopic->mcqs()->max('order_index')) + 1;

            foreach ($data['mcqs'] as $row) {
                if (! array_key_exists('order_index', $row)) {
                    $row['order_index'] = $nextOrder++;
                }
                $this->createMcqForTopic($existingTopic, $weekModel, $row);
            }

            $existingTopic->load(['mcqs' => fn ($q) => $q->orderBy('order_index')]);

            return response()->json([
                'status'     => true,
                'statuscode' => 200,
                'message'    => 'MCQs added to existing topic in this week.',
                'mcq_topic'  => $this->transformMcqTopic($existingTopic),
            ]);
        }

        $topic = CurriculumMcqTopic::create([
            'week_id'     => $weekModel->id,
            'mentee_id'   => $data['mentee_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'order_index' => $data['order_index'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        foreach ($data['mcqs'] as $row) {
            $this->createMcqForTopic($topic, $weekModel, $row);
        }

        $topic->load(['mcqs' => fn ($q) => $q->orderBy('order_index')]);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'MCQ topic created.',
            'mcq_topic'  => $this->transformMcqTopic($topic),
        ], 201);
    }

    public function updateMcq(Request $request, int $week, int $topic): JsonResponse
    {
        $weekModel  = CurriculumWeek::findOrFail($week);
        $topicModel = $this->findWeekMcqTopic($weekModel, $topic);

        $data = $request->validate(array_merge([
            'mentee_id'   => ['sometimes', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ], $this->bulkMcqRules('mcqs', required: false)));

        $fields = collect($data)->only(['name', 'description', 'order_index', 'mentee_id'])
            ->filter(fn ($v) => $v !== null)
            ->all();

        if (array_key_exists('mentee_id', $fields) && ! empty($weekModel->mentee_id) && (int) $fields['mentee_id'] !== (int) $weekModel->mentee_id) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'mentee_id must match this week.',
            ], 422);
        }

        if (isset($fields['name'])) {
            $menteeId = (int) ($fields['mentee_id'] ?? $topicModel->mentee_id);
            $duplicate = $this->findMcqTopicByNameInWeek($weekModel, $menteeId, $fields['name'], $topicModel->id);

            if ($duplicate) {
                return response()->json([
                    'status'     => false,
                    'statuscode' => 422,
                    'message'    => 'A topic with this name already exists in this week.',
                ], 422);
            }
        }

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        if ($fields !== []) {
            $topicModel->update($fields);
        }

        if (array_key_exists('mcqs', $data)) {
            $this->syncMcqsForTopic($topicModel, $weekModel, $data['mcqs']);
        }

        $topicModel->load(['mcqs' => fn ($q) => $q->orderBy('order_index')]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'MCQ topic updated.',
            'mcq_topic'  => $this->transformMcqTopic($topicModel),
        ]);
    }

    public function destroyMcq(int $week, int $topic): JsonResponse
    {
        $weekModel  = CurriculumWeek::findOrFail($week);
        $topicModel = $this->findWeekMcqTopic($weekModel, $topic);

        $mcqIds = $topicModel->mcqs()->pluck('id');
        if ($mcqIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'mcq')
                ->whereIn('item_id', $mcqIds)
                ->delete();
        }

        $topicModel->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'MCQ topic deleted.',
        ]);
    }

    // ─────────────────────────────────────────────
    //  PATCH / POST /mentor/curriculum/tasks/{task}
    //  Use POST (not PATCH) when sending form-data with file attachments.
    // ─────────────────────────────────────────────
    public function updateTask(Request $request, int $task): JsonResponse
    {
        $taskModel = CurriculumTask::findOrFail($task);

        if (
            $request->isMethod('PATCH')
            && str_contains($request->header('Content-Type', ''), 'multipart/form-data')
            && !$request->hasAny(['title', 'description', 'type', 'plan_id', 'submission_type'])
            && !$request->hasFile('attachments')
        ) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'File uploads on update require POST with form-data. PATCH does not support multipart in PHP.',
            ], 422);
        }

        $rules = [
            'title'             => 'sometimes|string|max:200',
            'description'       => 'nullable|string',
            'type'              => 'sometimes|in:task,reading,video,project,quiz,reflection',
            'plan_id'           => 'sometimes|integer|exists:plans,id',
            'is_required'       => 'nullable',
            'is_active'         => 'nullable',
            'submission_type'   => ['nullable', Rule::in(array_keys(CurriculumTask::SUBMISSION_TYPES))],
            'mentee_id'         => 'nullable|integer|exists:users,id',
            'is_completed'      => 'nullable',
            'replace_attachments' => 'nullable',
        ];

        if (!$request->hasFile('attachments')) {
            $rules['attachments'] = 'nullable|array';
        }

        $data = $request->validate($rules);

        $this->validateTaskAttachmentFiles($request);

        $taskFields = collect($data)->only([
            'title', 'description', 'type', 'plan_id', 'submission_type',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($request->hasFile('attachments')) {
            $replace = $request->boolean('replace_attachments', true);
            $existing = $replace ? [] : ($taskModel->attachments ?? []);

            if ($replace) {
                $this->deleteStoredAttachments($taskModel->attachments ?? []);
            }

            $taskFields['attachments'] = $this->processUploadedAttachments($request, $existing);
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
            'task'       => $taskModel->fresh()->load(['plan' => fn ($q) => $q->brief()]),
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

    // ─────────────────────────────────────────────
    //  GET /mentor/curriculum/weeks/{week}/supporting-materials
    // ─────────────────────────────────────────────
    public function supportingMaterials(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);

        $materials = $weekModel->supportingMaterials()
            ->when($request->filled('mentee_id'), fn ($q) => $q->where('mentee_id', $request->mentee_id))
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'week_id'    => $weekModel->id,
            'materials'  => $materials,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/curriculum/weeks/{week}/supporting-materials
    // ─────────────────────────────────────────────
    public function storeSupportingMaterial(Request $request, int $week): JsonResponse
    {
        $weekModel = CurriculumWeek::findOrFail($week);

        $data = $request->validate([
            'mentee_id'  => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'type'       => ['required', Rule::in(array_keys(TaskSupportingMaterial::TYPES))],
            'title'      => 'nullable|string|max:200',
            'link'       => 'nullable|url|max:2000',
            'is_active'  => 'nullable',
            'sort_order' => 'nullable|integer',
        ]);

        if ($contextError = $this->validateSupportingMaterialContext($weekModel, (int) $data['mentee_id'])) {
            return $contextError;
        }

        $type = $data['type'];

        if ($type === 'videolink') {
            $request->validate(['link' => 'required|url|max:2000']);
            $fileMeta = [
                'file_name' => null,
                'file_path' => null,
                'file_url'  => null,
                'mime_type' => null,
                'file_size' => null,
                'link'      => $data['link'],
            ];
        } else {
            $request->validate([
                'file' => array_merge(['required'], $this->supportingMaterialFileRules($type)),
            ]);
            $fileMeta = $this->storeSupportingMaterialFile($request->file('file'));
            $fileMeta['link'] = null;
        }

        $material = TaskSupportingMaterial::create([
            'week_id'    => $weekModel->id,
            'mentee_id'  => $data['mentee_id'],
            'mentor_id'  => $request->user()->id,
            'title'      => $data['title'] ?? null,
            'type'       => $type,
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? 0,
            ...$fileMeta,
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Supporting material created.',
            'material'   => $material,
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  PATCH / POST /mentor/curriculum/supporting-materials/{material}
    //  Use POST (not PATCH) when sending form-data with file.
    // ─────────────────────────────────────────────
    public function updateSupportingMaterial(Request $request, int $material): JsonResponse
    {
        $materialModel = TaskSupportingMaterial::findOrFail($material);

        if (
            $request->isMethod('PATCH')
            && str_contains($request->header('Content-Type', ''), 'multipart/form-data')
            && !$request->hasFile('file')
        ) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'File uploads on update require POST with form-data. PATCH does not support multipart in PHP.',
            ], 422);
        }

        $data = $request->validate([
            'mentee_id'  => ['sometimes', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'week_id'    => ['sometimes', 'integer', 'exists:curriculum_weeks,id'],
            'type'       => ['sometimes', Rule::in(array_keys(TaskSupportingMaterial::TYPES))],
            'title'      => 'nullable|string|max:200',
            'link'       => 'nullable|url|max:2000',
            'is_active'  => 'nullable',
            'sort_order' => 'nullable|integer',
        ]);

        $weekModel = CurriculumWeek::findOrFail((int) ($data['week_id'] ?? $materialModel->week_id));
        $menteeId = (int) ($data['mentee_id'] ?? $materialModel->mentee_id);

        if ($contextError = $this->validateSupportingMaterialContext($weekModel, $menteeId)) {
            return $contextError;
        }

        $type = $data['type'] ?? $materialModel->type;

        if ($type === 'videolink' && !$request->filled('link') && empty($materialModel->link)) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'link is required for videolink type.',
            ], 422);
        }

        $fields = collect($data)->only([
            'week_id', 'mentee_id', 'title', 'type', 'sort_order',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        if ($type === 'videolink') {
            if ($request->filled('link')) {
                $fields['link'] = $data['link'];
            }

            if ($request->has('type') && $type === 'videolink' && $materialModel->file_path) {
                $this->deleteSupportingMaterialFile($materialModel);
                $fields['file_name'] = null;
                $fields['file_path'] = null;
                $fields['file_url']  = null;
                $fields['mime_type'] = null;
                $fields['file_size'] = null;
            }
        } elseif ($request->hasFile('file')) {
            $request->validate([
                'file' => $this->supportingMaterialFileRules($type),
            ]);

            $this->deleteSupportingMaterialFile($materialModel);
            $fields = array_merge($fields, $this->storeSupportingMaterialFile($request->file('file')));
            $fields['link'] = null;
        }

        if (!empty($fields)) {
            $materialModel->update($fields);
        }

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Supporting material updated.',
            'material'   => $materialModel->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /mentor/curriculum/supporting-materials/{material}
    // ─────────────────────────────────────────────
    public function destroySupportingMaterial(int $material): JsonResponse
    {
        $materialModel = TaskSupportingMaterial::findOrFail($material);

        $this->deleteSupportingMaterialFile($materialModel);
        $materialModel->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Supporting material deleted.',
        ]);
    }

    private function validateSupportingMaterialContext(CurriculumWeek $week, int $menteeId): ?JsonResponse
    {
        if (!empty($week->mentee_id) && (int) $week->mentee_id !== $menteeId) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'mentee_id does not match this week.',
            ], 422);
        }

        return null;
    }

    private function supportingMaterialFileRules(string $type): array
    {
        return match ($type) {
            'pdf'   => ['file', 'mimes:pdf', 'max:20480'],
            'doc'   => ['file', 'mimes:doc,docx', 'max:20480'],
            'image' => ['file', 'mimes:jpg,jpeg,png,gif,webp,heic,heif', 'max:10240'],
            'ppt'   => ['file', 'mimes:ppt,pptx', 'max:30720'],
            default => ['file', 'max:20480'],
        };
    }

    private function storeSupportingMaterialFile($file): array
    {
        $path = $file->store('curriculum-supporting-materials', 'public');

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_url'  => TaskSupportingMaterial::buildMediaUrl($path),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    private function deleteSupportingMaterialFile(TaskSupportingMaterial $material): void
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
    }

    private function validateTaskAttachmentFiles(Request $request): void
    {
        if (!$request->hasFile('attachments')) {
            return;
        }

        $request->validate([
            'attachments'   => 'array',
            'attachments.*' => 'file|mimes:' . implode(',', CurriculumTask::ALLOWED_ATTACHMENT_MIMES) . '|max:' . CurriculumTask::ATTACHMENT_MAX_KB,
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
                'url'  => CurriculumTask::buildAttachmentUrl($path),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $attachments;
    }

    private function deleteStoredAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $url = $attachment['url'] ?? '';
            if ($url === '') {
                continue;
            }

            $path = CurriculumTask::resolveAttachmentPathFromUrl($url) ?? '';

            if ($path !== '') {
                Storage::disk('public')->delete($path);
            }
        }
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

    private function transformMcq(CurriculumMcq $mcq): array
    {
        $row = $mcq->toArray();
        $row['correct_option'] = ((int) $mcq->correct_index) + 1;
        return $row;
    }

    private function transformMcqTopic(CurriculumMcqTopic $topic): array
    {
        return [
            'id'          => $topic->id,
            'week_id'     => $topic->week_id,
            'mentee_id'   => $topic->mentee_id,
            'name'        => $topic->name,
            'description' => $topic->description,
            'order_index' => $topic->order_index,
            'is_active'   => $topic->is_active,
            'mcqs'        => $topic->mcqs->map(fn (CurriculumMcq $mcq) => $this->transformMcq($mcq))->values(),
            'created_at'  => $topic->created_at,
            'updated_at'  => $topic->updated_at,
        ];
    }

    private function findWeekMcqTopic(CurriculumWeek $week, int $topicId): CurriculumMcqTopic
    {
        return CurriculumMcqTopic::where('week_id', $week->id)
            ->with('mcqs')
            ->findOrFail($topicId);
    }

    private function findMcqTopicByNameInWeek(
        CurriculumWeek $week,
        int $menteeId,
        string $name,
        ?int $excludeTopicId = null
    ): ?CurriculumMcqTopic {
        return CurriculumMcqTopic::where('week_id', $week->id)
            ->where('mentee_id', $menteeId)
            ->when($excludeTopicId, fn ($q) => $q->where('id', '!=', $excludeTopicId))
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])
            ->first();
    }

    private function syncMcqsForTopic(CurriculumMcqTopic $topic, CurriculumWeek $week, array $mcqRows): void
    {
        $oldIds = $topic->mcqs()->pluck('id');
        if ($oldIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'mcq')
                ->whereIn('item_id', $oldIds)
                ->delete();
            $topic->mcqs()->delete();
        }

        foreach ($mcqRows as $row) {
            $this->createMcqForTopic($topic, $week, $row);
        }
    }

    private function bulkMcqRules(string $prefix, bool $required = true): array
    {
        $arrayRule = $required ? 'required|array|min:1' : 'sometimes|array|min:1';

        return [
            $prefix                      => $arrayRule,
            "{$prefix}.*.question"       => 'required|string|max:2000',
            "{$prefix}.*.options"        => 'required|array|size:4',
            "{$prefix}.*.options.*"      => 'required|string|max:500',
            "{$prefix}.*.correct_option" => 'required|integer|min:1|max:4',
            "{$prefix}.*.explanation"    => 'nullable|string|max:5000',
            "{$prefix}.*.difficulty"     => 'nullable|in:easy,medium,hard',
            "{$prefix}.*.points"         => 'nullable|integer|min:1|max:100',
            "{$prefix}.*.is_active"      => 'nullable|boolean',
            "{$prefix}.*.order_index"    => 'nullable|integer|min:0',
        ];
    }

    private function createMcqForTopic(CurriculumMcqTopic $topic, CurriculumWeek $week, array $row): CurriculumMcq
    {
        return CurriculumMcq::create([
            'week_id'       => $week->id,
            'topic_id'      => $topic->id,
            'mentee_id'     => $topic->mentee_id,
            'question'      => $row['question'],
            'options'       => array_values($row['options']),
            'correct_index' => ((int) $row['correct_option']) - 1,
            'explanation'   => $row['explanation'] ?? null,
            'difficulty'    => $row['difficulty'] ?? 'medium',
            'points'        => $row['points'] ?? 1,
            'is_active'     => (bool) ($row['is_active'] ?? true),
            'order_index'   => $row['order_index'] ?? 0,
        ]);
    }
}
