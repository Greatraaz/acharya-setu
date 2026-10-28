<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenteeTask;
use App\Models\StudentCurriculumProgress;
use App\Models\User;
use App\Services\MenteeTaskService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MenteeTaskController extends Controller
{
    public function __construct(private readonly MenteeTaskService $tasks)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));

        $items = MenteeTask::query()
            ->with(['mentee:id,name,email', 'mentor:id,name,email'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhereHas('mentee', fn ($u) => $u->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))
                        ->orWhereHas('mentor', fn ($u) => $u->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($status !== '', fn ($q) => $this->constrainByUiStatus($q, $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'            => MenteeTask::query()->count(),
            'pending'          => $this->constrainByUiStatus(MenteeTask::query(), 'pending')->count(),
            'awaiting_review'  => $this->constrainByUiStatus(MenteeTask::query(), 'awaiting_review')->count(),
            'completed'        => $this->constrainByUiStatus(MenteeTask::query(), 'completed')->count(),
        ];

        return view('admin.mentee-tasks.index', compact('items', 'search', 'status', 'stats'));
    }

    public function create()
    {
        return view('admin.mentee-tasks.form', [
            'task'    => null,
            'mentees' => $this->mentees(),
        ]);
    }

    public function store(Request $request)
    {
        $rules = $this->tasks->validationRules(true);
        $rules['mentor_id'] = ['required', 'integer', 'exists:users,id'];
        $data = $request->validate($rules);

        try {
            $task = $this->tasks->create(auth()->user(), $data, $request);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.mentee-tasks.show', $task)
            ->with('success', 'Task created and assigned.');
    }

    public function show(MenteeTask $menteeTask)
    {
        $menteeTask->load(['mentee', 'mentor', 'creator']);
        $progress = $menteeTask->getProgressForUser();

        return view('admin.mentee-tasks.show', [
            'task'     => $menteeTask,
            'progress' => $progress,
        ]);
    }

    public function linkedMentors(Request $request)
    {
        $data = $request->validate([
            'mentee_id' => 'required|integer|exists:users,id',
        ]);

        $menteeId = (int) $data['mentee_id'];
        $ids = User::mentorIdsLinkedToMentee($menteeId);

        $mentors = User::query()
            ->where('role', 'mentor')
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json([
            'mentors' => $mentors,
        ]);
    }

    public function edit(MenteeTask $menteeTask)
    {
        return view('admin.mentee-tasks.form', [
            'task'    => $menteeTask,
            'mentees' => $this->mentees(),
        ]);
    }

    public function update(Request $request, MenteeTask $menteeTask)
    {
        $rules = $this->tasks->validationRules(false);
        $rules['mentor_id'] = ['sometimes', 'required', 'integer', 'exists:users,id'];
        $data = $request->validate($rules);

        try {
            $this->tasks->update(auth()->user(), $menteeTask, $data, $request);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.mentee-tasks.show', $menteeTask)
            ->with('success', 'Task updated.');
    }

    public function destroy(MenteeTask $menteeTask)
    {
        $this->tasks->delete(auth()->user(), $menteeTask);

        return redirect()
            ->route('admin.mentee-tasks.index')
            ->with('success', 'Task deleted.');
    }

    public function review(Request $request, int $progress)
    {
        $data = $request->validate([
            'submission_status' => 'required|in:approved,rejected',
            'mentor_feedback'   => 'nullable|string|max:5000',
        ]);

        $record = StudentCurriculumProgress::query()
            ->where('item_type', MenteeTask::ITEM_TYPE)
            ->findOrFail($progress);

        $this->tasks->review(
            auth()->user(),
            $record,
            $data['submission_status'],
            $data['mentor_feedback'] ?? null,
        );

        return back()->with('success', 'Submission '.$data['submission_status'].'.');
    }

    private function constrainByUiStatus(Builder $query, string $status): Builder
    {
        $progressExists = function ($q, ?callable $extra = null): void {
            $q->selectRaw('1')
                ->from('student_curriculum_progress as p')
                ->whereColumn('p.item_id', 'mentee_tasks.id')
                ->where('p.item_type', MenteeTask::ITEM_TYPE)
                ->whereColumn('p.user_id', 'mentee_tasks.mentee_id');

            if ($extra) {
                $extra($q);
            }
        };

        return match ($status) {
            'pending' => $query->whereNotExists(fn ($q) => $progressExists($q)),
            'in_progress' => $query->whereExists(fn ($q) => $progressExists($q, function ($q) {
                $q->where(function ($s) {
                    $s->where('p.is_completed', false)->orWhereNull('p.is_completed');
                })->where(function ($s) {
                    $s->whereNull('p.submission_status')
                        ->orWhereNotIn('p.submission_status', ['submitted', 'approved', 'rejected']);
                });
            })),
            'awaiting_review' => $query->whereExists(fn ($q) => $progressExists($q, function ($q) {
                $q->where('p.submission_status', 'submitted')
                    ->where(function ($s) {
                        $s->where('p.is_completed', false)->orWhereNull('p.is_completed');
                    });
            })),
            'completed' => $query->whereExists(fn ($q) => $progressExists($q, function ($q) {
                $q->where(function ($s) {
                    $s->where('p.is_completed', true)->orWhere('p.submission_status', 'approved');
                });
            })),
            'rejected' => $query->whereExists(fn ($q) => $progressExists($q, function ($q) {
                $q->where('p.submission_status', 'rejected')
                    ->where(function ($s) {
                        $s->where('p.is_completed', false)->orWhereNull('p.is_completed');
                    });
            })),
            default => $query,
        };
    }

    private function mentees()
    {
        return User::query()->where('role', 'mentee')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']);
    }

}
