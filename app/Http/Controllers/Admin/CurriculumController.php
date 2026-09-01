<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{EducationStream, CurriculumMonth, CurriculumWeek, CurriculumTask, CurriculumMcq, MenteeEnrollment, Plan, StudentCurriculumProgress, User};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
 
class CurriculumController extends Controller
{
    // ── Streams ───────────────────────────────────────────────
    public function streams(Request $request)
    {
        $filterMentee = null;

        if ($request->filled('mentee_id')) {
            $filterMentee = User::where('role', 'mentee')->find($request->mentee_id);
        }

        $streams = EducationStream::with('mentee:id,name,email')
            ->withCount(['months', 'enrollments'])
            ->when(
                $filterMentee,
                fn ($q) => $q->where('mentee_id', $filterMentee->id),
                fn ($q) => $q->whereNotNull('mentee_id')
            )
            ->orderBy('sort_order')
            ->paginate(12)
            ->withQueryString();

        $mentees = User::where('role', 'mentee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
 
        return view('admin.curriculum.streams.index', compact('streams', 'mentees', 'filterMentee'));
    }

    // ── Global catalog streams (onboarding picker) ────────────
    public function catalog()
    {
        $streams = EducationStream::query()
            ->whereNull('mentee_id')
            ->withCount(['enrollments'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.curriculum.catalog.index', compact('streams'));
    }

    public function storeCatalog(Request $request)
    {
        $data = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:100',
                Rule::unique('education_streams', 'name')->whereNull('mentee_id'),
            ],
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $data['mentee_id']  = null;
        $data['mentor_id']  = null;
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['slug']       = $this->resolveCatalogSlug($data['name']);

        EducationStream::create($data);

        return redirect()->route('admin.curriculum.catalog')->with('success', 'Global stream created.');
    }

    public function updateCatalog(Request $request, EducationStream $stream)
    {
        abort_unless($stream->mentee_id === null, 404);

        $data = $request->validate([
            'name'        => [
                'required',
                'string',
                'max:100',
                Rule::unique('education_streams', 'name')->whereNull('mentee_id')->ignore($stream->id),
            ],
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? $stream->sort_order;
        $data['slug']       = $this->resolveCatalogSlug($data['name'], $stream->id);

        $stream->update($data);

        return redirect()->route('admin.curriculum.catalog')->with('success', 'Global stream updated.');
    }

    public function destroyCatalog(EducationStream $stream)
    {
        abort_unless($stream->mentee_id === null, 404);

        $stream->delete();

        return redirect()->route('admin.curriculum.catalog')->with('success', 'Global stream deleted.');
    }
 
    public function storeStream(Request $request)
    {
        $data = $request->validate([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $mentee = User::findOrFail($data['mentee_id']);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['mentor_id']  = $mentee->assigned_mentor_id;
        $data['slug']       = $this->resolveStreamSlug($data['name'], $data['mentee_id']);
 
        $stream = EducationStream::create($data);
        $this->ensureEnrollmentForStream($stream);

        return redirect()->back()->with('success', 'Stream created.');
    }
 
    public function updateStream(Request $request, EducationStream $stream)
    {
        abort_unless($stream->mentee_id !== null, 404);

        $data = $request->validate([
            'mentee_id'   => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:10',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $mentee = User::findOrFail($data['mentee_id']);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['mentor_id'] = $mentee->assigned_mentor_id;
        $data['slug']      = $this->resolveStreamSlug($data['name'], $data['mentee_id'], $stream->id);
 
        $stream->update($data);
        $this->ensureEnrollmentForStream($stream->fresh());

        return redirect()->back()->with('success', 'Stream updated.');
    }
 
    public function destroyStream(EducationStream $stream)
    {
        abort_unless($stream->mentee_id !== null, 404);

        $stream->delete();
        return redirect()->back()->with('success', 'Stream deleted.');
    }
 
    // ── Months ────────────────────────────────────────────────
    public function months(EducationStream $stream)
    {
        abort_unless($stream->mentee_id !== null, 404);

        $stream->load('mentee:id,name,email');
        $months = $stream->months()->with('weeks.tasks', 'weeks.mcqs')->orderBy('month_number')->get();
        return view('admin.curriculum.months.index', compact('stream', 'months'));
    }
 
    public function storeMonth(Request $request, EducationStream $stream)
    {
        $data = $request->validate([
            'mentee_id'         => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'month_number'      => 'required|integer|min:1|max:12',
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'theme'             => 'nullable|string|max:100',
            'learning_outcomes' => 'nullable|string',
            'milestone_badge'   => 'nullable|string|max:100',
            'is_active'         => 'nullable|boolean',
        ]);

        $this->assertMenteeMatchesStream($stream, (int) $data['mentee_id']);
 
        $data['stream_id']         = $stream->id;
        $data['is_active']         = $request->boolean('is_active', true);
        $data['learning_outcomes'] = array_values(
            array_filter(array_map('trim', explode("\n", $data['learning_outcomes'] ?? '')))
        );
 
        CurriculumMonth::create($data);
        return redirect()->back()->with('success', 'Month created.');
    }
 
    public function updateMonth(Request $request, CurriculumMonth $month)
    {
        $month->load('stream');

        $data = $request->validate([
            'mentee_id'         => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'month_number'      => 'required|integer|min:1|max:12',
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'theme'             => 'nullable|string|max:100',
            'learning_outcomes' => 'nullable|string',
            'milestone_badge'   => 'nullable|string|max:100',
            'is_active'         => 'nullable|boolean',
        ]);

        $this->assertMenteeMatchesStream($month->stream, (int) $data['mentee_id']);
 
        $data['is_active']         = $request->boolean('is_active', true);
        $data['learning_outcomes'] = array_values(
            array_filter(array_map('trim', explode("\n", $data['learning_outcomes'] ?? '')))
        );
 
        $month->update($data);
        return redirect()->back()->with('success', 'Month updated.');
    }
 
    public function destroyMonth(CurriculumMonth $month)
    {
        $month->delete();
        return redirect()->back()->with('success', 'Month deleted.');
    }
 
    // ── Weeks ─────────────────────────────────────────────────
    public function weeks(CurriculumMonth $month)
    {
        $month->load(['stream.mentee:id,name,email', 'weeks.tasks', 'weeks.mcqs']);
        $plans = Plan::query()->orderBy('id')->get(['id', 'plan_name', 'slug', 'status']);
        if ($plans->contains(fn ($p) => ($p->status ?? null) === 'active')) {
            $plans = $plans->where('status', 'active')->values();
        }

        return view('admin.curriculum.weeks.index', compact('month', 'plans'));
    }
 
    public function storeWeek(Request $request, CurriculumMonth $month)
    {
        $month->load('stream');

        $data = $request->validate([
            'week_number'  => [
                'required',
                'integer',
                'min:1',
                'max:5',
                Rule::unique('curriculum_weeks', 'week_number')->where('month_id', $month->id),
            ],
            'title'        => 'required|string|max:200',
            'focus'        => 'nullable|string',
            'mentor_guide' => 'nullable|string',
            'video_url'    => 'nullable|url',
            'is_active'    => 'nullable|boolean',
        ]);
 
        $data['month_id']  = $month->id;
        $data['mentee_id'] = $this->resolveMenteeIdForStream($month->stream, $month->mentee_id, required: false);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['week_number'];
 
        CurriculumWeek::create($data);
        return redirect()->back()->with('success', 'Week created.');
    }
 
    public function updateWeek(Request $request, CurriculumWeek $week)
    {
        $week->load('month.stream');

        $data = $request->validate([
            'week_number'  => [
                'required',
                'integer',
                'min:1',
                'max:5',
                Rule::unique('curriculum_weeks', 'week_number')
                    ->where('month_id', $week->month_id)
                    ->ignore($week->id),
            ],
            'title'        => 'required|string|max:200',
            'focus'        => 'nullable|string',
            'mentor_guide' => 'nullable|string',
            'video_url'    => 'nullable|url',
            'is_active'    => 'nullable|boolean',
        ]);
 
        $data['is_active']  = $request->boolean('is_active', true);
        $data['mentee_id']  = $this->resolveMenteeIdForStream(
            $week->month->stream,
            $week->month->mentee_id ?? $week->mentee_id,
            required: false
        );
        $week->update($data);
        return redirect()->back()->with('success', 'Week updated.');
    }
 
    public function destroyWeek(CurriculumWeek $week)
    {
        $week->delete();
        return redirect()->back()->with('success', 'Week deleted.');
    }
 
    // ── Tasks ─────────────────────────────────────────────────
    public function storeTask(Request $request, CurriculumWeek $week)
    {
        $week->load('month.stream');

        $data = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'type'              => 'required|in:task,reading,video,project,quiz,reflection',
            'plan_id'           => 'nullable|integer|exists:plans,id',
            'order_index'       => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:0',
            'is_required'       => 'nullable|boolean',
            'is_active'         => 'nullable|boolean',
            'submission_type'   => 'nullable|in:none,text,file,link,pdf,video',
        ]);
 
        $data['week_id']     = $week->id;
        $data['mentee_id']   = $this->resolveMenteeIdForStream(
            $week->month->stream,
            $week->mentee_id ?? $week->month->mentee_id,
            required: false
        );
        $data['plan_id']     = $data['plan_id'] ?? null;
        $data['is_required'] = $request->boolean('is_required', true);
        $data['is_active']   = $request->boolean('is_active', true);
        $data['submission_type'] = $data['submission_type'] ?? 'none';
 
        CurriculumTask::create($data);
        return redirect()->back()->with('success', 'Task created.');
    }
 
    public function updateTask(Request $request, CurriculumTask $task)
    {
        $data = $request->validate([
            'title'             => 'required|string|max:200',
            'description'       => 'nullable|string',
            'type'              => 'required|in:task,reading,video,project,quiz,reflection',
            'plan_id'           => 'nullable|integer|exists:plans,id',
            'order_index'       => 'nullable|integer|min:0',
            'estimated_minutes' => 'nullable|integer|min:0',
            'is_required'       => 'nullable|boolean',
            'is_active'         => 'nullable|boolean',
            'submission_type'   => 'nullable|in:none,text,file,link,pdf,video',
        ]);
 
        $data['is_required'] = $request->boolean('is_required', true);
        $data['is_active']   = $request->boolean('is_active', true);
 
        $task->update($data);
        return redirect()->back()->with('success', 'Task updated.');
    }
 
    public function destroyTask(CurriculumTask $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Task deleted.');
    }
 
    // ── MCQs ──────────────────────────────────────────────────
    public function storeMcq(Request $request, CurriculumWeek $week)
    {
        $week->load('month.stream');

        $data = $request->validate([
            'question'      => 'required|string',
            'options'       => 'required|array|min:2|max:6',
            'options.*'     => 'required|string|max:500',
            'correct_index' => 'required|integer|min:0',
            'explanation'   => 'nullable|string',
            'difficulty'    => 'required|in:easy,medium,hard',
            'points'        => 'nullable|integer|min:1',
            'order_index'   => 'nullable|integer|min:0',
        ]);
 
        $data['week_id']   = $week->id;
        $data['mentee_id'] = $this->resolveMenteeIdForStream(
            $week->month->stream,
            $week->mentee_id ?? $week->month->mentee_id,
            required: false
        );
 
        CurriculumMcq::create($data);
        return redirect()->back()->with('success', 'MCQ created.');
    }
 
    public function updateMcq(Request $request, CurriculumMcq $mcq)
    {
        $data = $request->validate([
            'question'      => 'required|string',
            'options'       => 'required|array|min:2|max:6',
            'options.*'     => 'required|string|max:500',
            'correct_index' => 'required|integer|min:0',
            'explanation'   => 'nullable|string',
            'difficulty'    => 'required|in:easy,medium,hard',
            'points'        => 'nullable|integer|min:1',
            'order_index'   => 'nullable|integer|min:0',
        ]);
 
        $mcq->update($data);
        return redirect()->back()->with('success', 'MCQ updated.');
    }
 
    public function destroyMcq(CurriculumMcq $mcq)
    {
        $mcq->delete();
        return redirect()->back()->with('success', 'MCQ deleted.');
    }
 
    // ── Enrollments ───────────────────────────────────────────
    public function enrollments(Request $request)
    {
        $enrollments = MenteeEnrollment::with(['mentee', 'mentor', 'stream'])
            ->when($request->stream_id, fn($q) => $q->where('stream_id', $request->stream_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);
 
        $streams = EducationStream::active()->get();
 
        return view('admin.curriculum.enrollments.index', compact('enrollments', 'streams'));
    }
 
    public function enrollmentShow(MenteeEnrollment $enrollment)
    {
        $enrollment->load(['mentee', 'mentor', 'stream.months.weeks.tasks', 'stream.months.weeks.mcqs']);
        $progress = StudentCurriculumProgress::getOverallProgress(
            $enrollment->mentee_id,
            $enrollment->stream_id
        );
 
        return view('admin.curriculum.enrollments.show', compact('enrollment', 'progress'));
    }
 
    public function progressOverview(EducationStream $stream)
    {
        $months      = $stream->months()->with(['weeks.tasks', 'weeks.mcqs'])->orderBy('month_number')->get();
        $enrollments = MenteeEnrollment::where('stream_id', $stream->id)->with('mentee')->get();
 
        return view('admin.curriculum.progress', compact('stream', 'months', 'enrollments'));
    }
 
    public function reviewSubmission(Request $request, StudentCurriculumProgress $progress)
    {
        $request->validate([
            'submission_status' => 'required|in:approved,rejected',
            'mentor_feedback'   => 'nullable|string|max:2000',
        ]);
 
        $progress->update([
            'submission_status' => $request->submission_status,
            'mentor_feedback'   => $request->mentor_feedback,
            'reviewed_at'       => now(),
            'is_completed'      => $request->submission_status === 'approved',
            'completed_at'      => $request->submission_status === 'approved' ? now() : null,
        ]);
 
        return redirect()->back()->with('success', 'Submission reviewed.');
    }

    private function resolveStreamSlug(string $name, int $menteeId, ?int $excludeId = null): string
    {
        $slug  = Str::slug($name);
        $query = EducationStream::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            $slug .= '-' . $menteeId;
        }

        return $slug;
    }

    private function resolveCatalogSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name) ?: 'stream';
        $slug = $base;
        $i = 2;

        while (
            EducationStream::query()
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function assertMenteeMatchesStream(EducationStream $stream, int $menteeId): void
    {
        if ($stream->mentee_id && (int) $stream->mentee_id !== $menteeId) {
            throw ValidationException::withMessages([
                'mentee_id' => 'The selected mentee must match this stream.',
            ]);
        }

        if (! $stream->mentee_id) {
            throw ValidationException::withMessages([
                'mentee_id' => 'This is a global catalog stream. Build the 6-month plan on the mentee copy, not the catalog row.',
            ]);
        }
    }

    private function resolveMenteeIdForStream(EducationStream $stream, ?int $fallbackMenteeId = null, bool $required = true): ?int
    {
        $menteeId = $stream->mentee_id ?? $fallbackMenteeId;

        if (! $menteeId) {
            if ($required) {
                throw ValidationException::withMessages([
                    'mentee_id' => 'Assign a mentee to this stream before adding curriculum content.',
                ]);
            }

            return null;
        }

        return (int) $menteeId;
    }

    private function ensureEnrollmentForStream(EducationStream $stream): void
    {
        if (! $stream->mentee_id) {
            return;
        }

        $mentorId = $stream->mentor_id
            ?? User::find($stream->mentee_id)?->assigned_mentor_id;

        if (! $mentorId) {
            return;
        }

        if ((int) $stream->mentor_id !== (int) $mentorId) {
            $stream->update(['mentor_id' => $mentorId]);
        }

        MenteeEnrollment::firstOrCreate(
            [
                'mentee_id' => $stream->mentee_id,
                'stream_id' => $stream->id,
            ],
            [
                'mentor_id'         => $mentorId,
                'start_date'        => now()->toDateString(),
                'expected_end_date' => now()->addMonths(6)->toDateString(),
                'status'            => 'active',
                'current_month'     => 1,
                'current_week'      => 1,
            ]
        );
    }
}