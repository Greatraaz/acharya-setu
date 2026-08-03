<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\{
    ConsultationSession,
    CurriculumMcq,
    CurriculumMcqTopic,
    CurriculumMonth,
    CurriculumTask,
    CurriculumWeek,
    EducationStream,
    MenteeEnrollment,
    Plan,
    StudentCurriculumProgress,
    TaskSupportingMaterial,
    User,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CurriculumController extends Controller
{
    // ── Tracks ────────────────────────────────────────────────

    public function tracks(Request $request)
    {
        $mentorId = auth()->id();

        $tracks = EducationStream::with('mentee:id,name,email,avatar_url')
            ->withCount('months')
            ->where('mentor_id', $mentorId)
            ->when($request->filled('mentee_id'), fn ($q) => $q->where('mentee_id', $request->mentee_id))
            ->orderBy('sort_order')
            ->get();

        $mentees = $this->mentorMenteesQuery()->get(['id', 'name', 'email']);
        $filterMentee = $request->filled('mentee_id')
            ? $mentees->firstWhere('id', (int) $request->mentee_id)
            : null;

        return view('frontend.mentors.curriculum.tracks', compact('tracks', 'mentees', 'filterMentee'));
    }

    public function storeTrack(Request $request)
    {
        $mentor = auth()->user();

        $data = $request->validate([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        $this->assertMentorMentee((int) $data['mentee_id']);

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

        $this->ensureEnrollmentForTrack($track);

        return redirect()
            ->route('mentor.curriculum.tracks', ['mentee_id' => $data['mentee_id']])
            ->with('success', 'Track created.');
    }

    public function updateTrack(Request $request, EducationStream $track)
    {
        $this->assertOwnsTrack($track);

        $data = $request->validate([
            'mentee_id'   => ['sometimes', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:100',
            'color'       => 'nullable|string|max:50',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        if (isset($data['mentee_id'])) {
            $this->assertMentorMentee((int) $data['mentee_id']);
        }

        $fields = collect($data)->only([
            'mentee_id', 'name', 'description', 'icon', 'color', 'sort_order',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        if (isset($fields['name'])) {
            $slug = Str::slug($fields['name']);
            $slugExists = EducationStream::where('slug', $slug)
                ->where('id', '!=', $track->id)
                ->exists();

            if ($slugExists) {
                $slug .= '-' . ($fields['mentee_id'] ?? $track->mentee_id);
            }

            $fields['slug'] = $slug;
        }

        if ($fields !== []) {
            $track->update($fields);
        }

        $this->ensureEnrollmentForTrack($track->fresh());

        return redirect()->back()->with('success', 'Track updated.');
    }

    // ── Months ────────────────────────────────────────────────

    public function months(EducationStream $track)
    {
        $this->assertOwnsTrack($track);

        $track->load('mentee:id,name,email');
        $months = $track->months()
            ->with(['weeks.tasks', 'weeks.mcqs', 'weeks.mcqTopics'])
            ->orderBy('month_number')
            ->get();

        return view('frontend.mentors.curriculum.months', compact('track', 'months'));
    }

    public function storeMonth(Request $request, EducationStream $track)
    {
        $this->assertOwnsTrack($track);

        $data = $request->validate([
            'mentee_id'           => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'month_number'        => [
                'required', 'integer', 'min:1', 'max:12',
                Rule::unique('curriculum_months', 'month_number')->where('stream_id', $track->id),
            ],
            'title'               => 'required|string|max:200',
            'theme'               => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'learning_outcomes'   => 'nullable|string',
            'is_active'           => 'nullable',
            'sort_order'          => 'nullable|integer',
        ]);

        $this->assertMenteeMatchesParent($track->mentee_id, (int) $data['mentee_id'], 'track');

        CurriculumMonth::create([
            'stream_id'         => $track->id,
            'mentee_id'         => $data['mentee_id'],
            'month_number'      => $data['month_number'],
            'title'             => $data['title'],
            'theme'             => $data['theme'] ?? null,
            'description'       => $data['description'] ?? null,
            'learning_outcomes' => $this->parseLearningOutcomes($data['learning_outcomes'] ?? null),
            'is_active'         => $request->boolean('is_active', true),
            'sort_order'        => $data['sort_order'] ?? $data['month_number'],
        ]);

        return redirect()->back()->with('success', 'Month created.');
    }

    public function updateMonth(Request $request, CurriculumMonth $month)
    {
        $month->load('stream');
        $this->assertOwnsTrack($month->stream);

        $data = $request->validate([
            'month_number'      => [
                'sometimes', 'integer', 'min:1', 'max:12',
                Rule::unique('curriculum_months', 'month_number')
                    ->where('stream_id', $month->stream_id)
                    ->ignore($month->id),
            ],
            'title'             => 'sometimes|string|max:200',
            'theme'             => 'nullable|string|max:100',
            'description'       => 'nullable|string',
            'learning_outcomes' => 'nullable|string',
            'is_active'         => 'nullable',
            'sort_order'        => 'nullable|integer',
        ]);

        $fields = collect($data)->only([
            'month_number', 'title', 'theme', 'description', 'sort_order',
        ])->filter(fn ($v) => $v !== null)->all();

        if (array_key_exists('learning_outcomes', $data)) {
            $fields['learning_outcomes'] = $this->parseLearningOutcomes($data['learning_outcomes']);
        }

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        $month->update($fields);

        return redirect()->back()->with('success', 'Month updated.');
    }

    public function destroyMonth(CurriculumMonth $month)
    {
        $month->load('stream');
        $this->assertOwnsTrack($month->stream);

        $weekIds = $month->weeks()->pluck('id')->all();
        $this->deleteProgressForWeekIds($weekIds);
        $month->delete();

        return redirect()->back()->with('success', 'Month deleted.');
    }

    // ── Weeks ─────────────────────────────────────────────────

    public function weeks(CurriculumMonth $month)
    {
        $month->load(['stream.mentee:id,name,email']);
        $this->assertOwnsTrack($month->stream);

        $month->load([
            'weeks' => fn ($q) => $q->orderBy('week_number'),
            'weeks.tasks.plan',
            'weeks.mcqTopics.mcqs',
            'weeks.supportingMaterials',
        ]);

        $planColumns = ['id', 'slug'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('plans', 'plan_name')) {
            $planColumns[] = 'plan_name';
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('plans', 'name')) {
            $planColumns[] = 'name';
        }

        $activePlans = Plan::active()->orderBy('id')->get($planColumns);
        $plans = $activePlans->isNotEmpty()
            ? $activePlans
            : Plan::query()->orderBy('id')->get($planColumns);

        return view('frontend.mentors.curriculum.weeks', compact('month', 'plans'));
    }

    public function storeWeek(Request $request, CurriculumMonth $month)
    {
        $month->load('stream');
        $this->assertOwnsTrack($month->stream);

        $data = $request->validate([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'week_number' => [
                'required', 'integer', 'min:1', 'max:52',
                Rule::unique('curriculum_weeks', 'week_number')->where('month_id', $month->id),
            ],
            'title'       => 'required|string|max:200',
            'focus'       => 'nullable|string',
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        $this->assertMenteeMatchesParent($month->mentee_id, (int) $data['mentee_id'], 'month');

        CurriculumWeek::create([
            'month_id'    => $month->id,
            'mentee_id'   => $data['mentee_id'],
            'week_number' => $data['week_number'],
            'title'       => $data['title'],
            'focus'       => $data['focus'] ?? $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $data['sort_order'] ?? $data['week_number'],
        ]);

        return redirect()->back()->with('success', 'Week created.');
    }

    public function updateWeek(Request $request, CurriculumWeek $week)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);

        $data = $request->validate([
            'week_number' => [
                'sometimes', 'integer', 'min:1', 'max:52',
                Rule::unique('curriculum_weeks', 'week_number')
                    ->where('month_id', $week->month_id)
                    ->ignore($week->id),
            ],
            'title'       => 'sometimes|string|max:200',
            'focus'       => 'nullable|string',
            'description' => 'nullable|string',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        $fields = collect($data)->only(['week_number', 'title', 'sort_order'])
            ->filter(fn ($v) => $v !== null)->all();

        if (array_key_exists('focus', $data) || array_key_exists('description', $data)) {
            $fields['focus'] = $data['focus'] ?? $data['description'] ?? null;
        }

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        $week->update($fields);

        return redirect()->back()->with('success', 'Week updated.');
    }

    public function destroyWeek(CurriculumWeek $week)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);

        $this->deleteProgressForWeekIds([$week->id]);
        $week->delete();

        return redirect()->back()->with('success', 'Week deleted.');
    }

    // ── Tasks ─────────────────────────────────────────────────

    public function storeTask(Request $request, CurriculumWeek $week)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);

        $rules = [
            'mentee_id'       => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'title'           => 'required|string|max:200',
            'description'     => 'nullable|string',
            'type'            => 'nullable|in:task,reading,video,project,quiz,reflection',
            'plan_id'         => 'required|integer|exists:plans,id',
            'is_required'     => 'nullable',
            'is_active'       => 'nullable',
            'submission_type' => ['nullable', Rule::in(array_keys(CurriculumTask::SUBMISSION_TYPES))],
        ];

        if (! $request->hasFile('attachments')) {
            $rules['attachments'] = 'nullable|array';
        }

        $data = $request->validate($rules);
        $this->assertMenteeMatchesParent($week->mentee_id, (int) $data['mentee_id'], 'week');
        $this->validateTaskAttachmentFiles($request);

        $attachments = $this->processUploadedAttachments($request, $data['attachments'] ?? []);

        CurriculumTask::create([
            'week_id'         => $week->id,
            'mentee_id'       => $data['mentee_id'],
            'plan_id'         => $data['plan_id'],
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'type'            => $data['type'] ?? 'task',
            'attachments'     => $attachments,
            'is_required'     => $request->boolean('is_required', true),
            'is_active'       => $request->boolean('is_active', true),
            'submission_type' => $data['submission_type'] ?? 'none',
        ]);

        return redirect()->back()->with('success', 'Task created.');
    }

    public function updateTask(Request $request, CurriculumTask $task)
    {
        $task->load('week.month.stream');
        $this->assertOwnsTrack($task->week->month->stream);

        $rules = [
            'title'               => 'sometimes|string|max:200',
            'description'         => 'nullable|string',
            'type'                => 'sometimes|in:task,reading,video,project,quiz,reflection',
            'plan_id'             => 'sometimes|integer|exists:plans,id',
            'is_required'         => 'nullable',
            'is_active'           => 'nullable',
            'submission_type'     => ['nullable', Rule::in(array_keys(CurriculumTask::SUBMISSION_TYPES))],
            'replace_attachments' => 'nullable',
        ];

        if (! $request->hasFile('attachments')) {
            $rules['attachments'] = 'nullable|array';
        }

        $data = $request->validate($rules);
        $this->validateTaskAttachmentFiles($request);

        $taskFields = collect($data)->only([
            'title', 'description', 'type', 'plan_id', 'submission_type',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($request->hasFile('attachments')) {
            $replace = $request->boolean('replace_attachments', true);
            $existing = $replace ? [] : ($task->attachments ?? []);

            if ($replace) {
                $this->deleteStoredAttachments($task->attachments ?? []);
            }

            $taskFields['attachments'] = $this->processUploadedAttachments($request, $existing);
        }

        if ($request->has('is_required')) {
            $taskFields['is_required'] = $request->boolean('is_required');
        }
        if ($request->has('is_active')) {
            $taskFields['is_active'] = $request->boolean('is_active');
        }

        if (! empty($taskFields)) {
            $task->update($taskFields);
        }

        return redirect()->back()->with('success', 'Task updated.');
    }

    public function destroyTask(CurriculumTask $task)
    {
        $task->load('week.month.stream');
        $this->assertOwnsTrack($task->week->month->stream);

        StudentCurriculumProgress::where('item_type', 'task')
            ->where('item_id', $task->id)
            ->delete();

        $this->deleteStoredAttachments($task->attachments ?? []);
        $task->delete();

        return redirect()->back()->with('success', 'Task deleted.');
    }

    // ── MCQ Topics ────────────────────────────────────────────

    public function storeMcqTopic(Request $request, CurriculumWeek $week)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);

        $data = $request->validate(array_merge([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'is_active'   => 'nullable',
        ], $this->bulkMcqRules('mcqs')));

        $this->assertMenteeMatchesParent($week->mentee_id, (int) $data['mentee_id'], 'week');

        $existingTopic = $this->findMcqTopicByNameInWeek($week, (int) $data['mentee_id'], $data['name']);

        if ($existingTopic) {
            $nextOrder = ((int) $existingTopic->mcqs()->max('order_index')) + 1;
            foreach ($data['mcqs'] as $row) {
                if (! array_key_exists('order_index', $row)) {
                    $row['order_index'] = $nextOrder++;
                }
                $this->createMcqForTopic($existingTopic, $week, $row);
            }

            return redirect()->back()->with('success', 'MCQs added to existing topic.');
        }

        $topic = CurriculumMcqTopic::create([
            'week_id'     => $week->id,
            'mentee_id'   => $data['mentee_id'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'order_index' => $data['order_index'] ?? 0,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        foreach ($data['mcqs'] as $row) {
            $this->createMcqForTopic($topic, $week, $row);
        }

        return redirect()->back()->with('success', 'MCQ topic created.');
    }

    public function updateMcqTopic(Request $request, CurriculumWeek $week, CurriculumMcqTopic $topic)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);
        abort_unless((int) $topic->week_id === (int) $week->id, 404);

        $data = $request->validate(array_merge([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'is_active'   => 'nullable',
        ], $this->bulkMcqRules('mcqs', required: false)));

        $fields = collect($data)->only(['name', 'description', 'order_index'])
            ->filter(fn ($v) => $v !== null)->all();

        if (isset($fields['name'])) {
            $duplicate = $this->findMcqTopicByNameInWeek(
                $week,
                (int) $topic->mentee_id,
                $fields['name'],
                $topic->id
            );
            if ($duplicate) {
                throw ValidationException::withMessages([
                    'name' => 'A topic with this name already exists in this week.',
                ]);
            }
        }

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        if ($fields !== []) {
            $topic->update($fields);
        }

        if (array_key_exists('mcqs', $data)) {
            $this->syncMcqsForTopic($topic, $week, $data['mcqs']);
        }

        return redirect()->back()->with('success', 'MCQ topic updated.');
    }

    public function destroyMcqTopic(CurriculumWeek $week, CurriculumMcqTopic $topic)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);
        abort_unless((int) $topic->week_id === (int) $week->id, 404);

        $mcqIds = $topic->mcqs()->pluck('id');
        if ($mcqIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'mcq')
                ->whereIn('item_id', $mcqIds)
                ->delete();
        }

        $topic->delete();

        return redirect()->back()->with('success', 'MCQ topic deleted.');
    }

    public function destroyMcqItem(CurriculumWeek $week, CurriculumMcqTopic $topic, CurriculumMcq $mcq)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);
        abort_unless((int) $topic->week_id === (int) $week->id, 404);
        abort_unless((int) $mcq->topic_id === (int) $topic->id, 404);

        StudentCurriculumProgress::where('item_type', 'mcq')
            ->where('item_id', $mcq->id)
            ->delete();

        $mcq->delete();

        return redirect()->back()->with('success', 'MCQ deleted.');
    }

    // ── Supporting materials ──────────────────────────────────

    public function storeSupportingMaterial(Request $request, CurriculumWeek $week)
    {
        $week->load('month.stream');
        $this->assertOwnsTrack($week->month->stream);

        $data = $request->validate([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'type'        => ['required', Rule::in(array_keys(TaskSupportingMaterial::TYPES))],
            'title'       => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'link'        => 'nullable|url|max:2000',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        $this->assertMenteeMatchesParent($week->mentee_id, (int) $data['mentee_id'], 'week');

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

        TaskSupportingMaterial::create([
            'week_id'     => $week->id,
            'mentee_id'   => $data['mentee_id'],
            'mentor_id'   => auth()->id(),
            'title'       => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'type'        => $type,
            'is_active'   => $request->boolean('is_active', true),
            'sort_order'  => $data['sort_order'] ?? 0,
            ...$fileMeta,
        ]);

        return redirect()->back()->with('success', 'Supporting material created.');
    }

    public function updateSupportingMaterial(Request $request, TaskSupportingMaterial $material)
    {
        $material->load('week.month.stream');
        $this->assertOwnsTrack($material->week->month->stream);

        $data = $request->validate([
            'type'        => ['sometimes', Rule::in(array_keys(TaskSupportingMaterial::TYPES))],
            'title'       => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'link'        => 'nullable|url|max:2000',
            'is_active'   => 'nullable',
            'sort_order'  => 'nullable|integer',
        ]);

        $type = $data['type'] ?? $material->type;

        if ($type === 'videolink' && ! $request->filled('link') && empty($material->link)) {
            throw ValidationException::withMessages([
                'link' => 'link is required for videolink type.',
            ]);
        }

        $fields = collect($data)->only(['title', 'description', 'type', 'sort_order'])
            ->filter(fn ($v) => $v !== null)->all();

        if (array_key_exists('description', $data) && $data['description'] === '') {
            $fields['description'] = null;
        }

        if ($request->has('is_active')) {
            $fields['is_active'] = $request->boolean('is_active');
        }

        if ($type === 'videolink') {
            if ($request->filled('link')) {
                $fields['link'] = $data['link'];
            }
            if ($request->has('type') && $material->file_path) {
                $this->deleteSupportingMaterialFile($material);
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
            $this->deleteSupportingMaterialFile($material);
            $fields = array_merge($fields, $this->storeSupportingMaterialFile($request->file('file')));
            $fields['link'] = null;
        }

        if (! empty($fields)) {
            $material->update($fields);
        }

        return redirect()->back()->with('success', 'Supporting material updated.');
    }

    public function destroySupportingMaterial(TaskSupportingMaterial $material)
    {
        $material->load('week.month.stream');
        $this->assertOwnsTrack($material->week->month->stream);

        StudentCurriculumProgress::where('item_type', 'material')
            ->where('item_id', $material->id)
            ->delete();

        $this->deleteSupportingMaterialFile($material);
        $material->delete();

        return redirect()->back()->with('success', 'Supporting material deleted.');
    }

    // ── Auth / helpers ────────────────────────────────────────

    private function ensureEnrollmentForTrack(EducationStream $track): void
    {
        if (! $track->mentee_id || ! $track->mentor_id) {
            return;
        }

        MenteeEnrollment::firstOrCreate(
            [
                'mentee_id' => $track->mentee_id,
                'mentor_id' => $track->mentor_id,
                'stream_id' => $track->id,
            ],
            [
                'start_date'        => now()->toDateString(),
                'expected_end_date' => now()->addMonths(6)->toDateString(),
                'status'            => 'active',
                'current_month'     => 1,
                'current_week'      => 1,
            ]
        );
    }

    private function assertOwnsTrack(?EducationStream $track): void
    {
        abort_unless($track && (int) $track->mentor_id === (int) auth()->id(), 403);
    }

    private function assertMentorMentee(int $menteeId): void
    {
        abort_unless($this->mentorMenteesQuery()->where('id', $menteeId)->exists(), 403);
    }

    private function assertMenteeMatchesParent(?int $parentMenteeId, int $menteeId, string $label): void
    {
        if (! empty($parentMenteeId) && (int) $parentMenteeId !== $menteeId) {
            throw ValidationException::withMessages([
                'mentee_id' => "mentee_id must match this {$label}.",
            ]);
        }
    }

    private function mentorMenteesQuery()
    {
        $mentorId = auth()->id();

        $sessionIds = ConsultationSession::where('mentor_id', $mentorId)->pluck('mentee_id');
        $assignedIds = User::where('assigned_mentor_id', $mentorId)->where('role', 'mentee')->pluck('id');
        $enrolledIds = MenteeEnrollment::where('mentor_id', $mentorId)->pluck('mentee_id');
        $trackIds = EducationStream::where('mentor_id', $mentorId)->pluck('mentee_id');

        $ids = $sessionIds->merge($assignedIds)->merge($enrolledIds)->merge($trackIds)->unique()->filter()->values();

        return User::where('role', 'mentee')->whereIn('id', $ids)->orderBy('name');
    }

    private function parseLearningOutcomes(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $raw))));
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
        if (! $request->hasFile('attachments')) {
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

        if (! $request->hasFile('attachments')) {
            return $attachments;
        }

        foreach ($request->file('attachments') as $file) {
            if (! $file || ! $file->isValid()) {
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
        $mcqIds = CurriculumMcq::whereIn('week_id', $weekIds)->pluck('id');
        $materialIds = TaskSupportingMaterial::whereIn('week_id', $weekIds)->pluck('id');

        if ($taskIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'task')->whereIn('item_id', $taskIds)->delete();
        }
        if ($mcqIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'mcq')->whereIn('item_id', $mcqIds)->delete();
        }
        if ($materialIds->isNotEmpty()) {
            StudentCurriculumProgress::where('item_type', 'material')->whereIn('item_id', $materialIds)->delete();
        }
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
            StudentCurriculumProgress::where('item_type', 'mcq')->whereIn('item_id', $oldIds)->delete();
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
