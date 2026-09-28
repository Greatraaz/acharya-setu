<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MenteeEnrollment;
use App\Models\MenteeTask;
use App\Models\StudentCurriculumProgress;
use App\Models\User;
use App\Services\MenteeTaskService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MenteeTaskController extends Controller
{
    public function __construct(private readonly MenteeTaskService $tasks)
    {
    }

    public function index(Request $request)
    {
        $mentorId = auth()->id();
        $menteeId = $request->integer('mentee_id') ?: null;

        $items = MenteeTask::query()
            ->with(['mentee:id,name,email,avatar_url'])
            ->where('mentor_id', $mentorId)
            ->when($menteeId, fn ($q) => $q->where('mentee_id', $menteeId))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $pending = $this->tasks->pendingForMentor($mentorId, $menteeId)->paginate(10, ['*'], 'reviews_page')->withQueryString();
        $pending->getCollection()->transform(function (StudentCurriculumProgress $p) {
            $task = MenteeTask::find($p->item_id);

            return [
                'progress' => $p,
                'task'     => $task,
            ];
        });

        $mentees = $this->mentorMentees($mentorId);

        return view('frontend.mentors.tasks.index', compact('items', 'pending', 'mentees', 'menteeId'));
    }

    public function create(Request $request)
    {
        $mentees = $this->mentorMentees(auth()->id());
        $selectedMentee = $request->integer('mentee_id') ?: null;

        return view('frontend.mentors.tasks.form', [
            'task'           => null,
            'mentees'        => $mentees,
            'selectedMentee' => $selectedMentee,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->tasks->validationRules(true));

        try {
            $task = $this->tasks->create(auth()->user(), $data, $request);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('mentor.tasks.show', $task)
            ->with('success', 'Task created.');
    }

    public function show(MenteeTask $task)
    {
        $this->tasks->assertCanManage(auth()->user(), $task);
        $task->load(['mentee', 'mentor', 'creator']);
        $progress = $task->getProgressForUser();

        return view('frontend.mentors.tasks.show', compact('task', 'progress'));
    }

    public function edit(MenteeTask $task)
    {
        $this->tasks->assertCanManage(auth()->user(), $task);
        $mentees = $this->mentorMentees(auth()->id());

        return view('frontend.mentors.tasks.form', [
            'task'           => $task,
            'mentees'        => $mentees,
            'selectedMentee' => $task->mentee_id,
        ]);
    }

    public function update(Request $request, MenteeTask $task)
    {
        $data = $request->validate($this->tasks->validationRules(false));

        try {
            $this->tasks->update(auth()->user(), $task, $data, $request);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('mentor.tasks.show', $task)
            ->with('success', 'Task updated.');
    }

    public function destroy(MenteeTask $task)
    {
        $this->tasks->delete(auth()->user(), $task);

        return redirect()
            ->route('mentor.tasks.index')
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

        try {
            $this->tasks->review(
                auth()->user(),
                $record,
                $data['submission_status'],
                $data['mentor_feedback'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Submission '.$data['submission_status'].'.');
    }

    private function mentorMentees(int $mentorId)
    {
        $ids = MenteeEnrollment::where('mentor_id', $mentorId)->pluck('mentee_id')
            ->merge(User::where('role', 'mentee')->where('assigned_mentor_id', $mentorId)->pluck('id'))
            ->unique()
            ->values();

        return User::query()
            ->where('role', 'mentee')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'avatar_url']);
    }
}
