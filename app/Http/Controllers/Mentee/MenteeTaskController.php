<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\MenteeTask;
use App\Services\MenteeTaskService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class MenteeTaskController extends Controller
{
    public function __construct(private readonly MenteeTaskService $tasks)
    {
    }

    public function index()
    {
        $items = MenteeTask::query()
            ->with('mentor:id,name,avatar_url')
            ->where('mentee_id', auth()->id())
            ->where('is_active', true)
            ->latest()
            ->paginate(20);

        return view('frontend.mentee.tasks.index', compact('items'));
    }

    public function show(MenteeTask $task)
    {
        abort_unless((int) $task->mentee_id === (int) auth()->id(), 404);
        $task->load('mentor:id,name,email,avatar_url');
        $progress = $task->getProgressForUser();

        return view('frontend.mentee.tasks.show', compact('task', 'progress'));
    }

    public function submit(Request $request, MenteeTask $task)
    {
        abort_unless((int) $task->mentee_id === (int) auth()->id(), 404);

        try {
            $progress = $this->tasks->submit(auth()->user(), $task, $request);
        } catch (ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        $awaiting = $progress->submission_status === 'submitted' && ! $progress->is_completed;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $awaiting
                    ? 'Submission received. Awaiting mentor review.'
                    : 'Task completed!',
                'completed' => (bool) $progress->is_completed,
                'awaiting_review' => $awaiting,
                'submission_status' => $progress->submission_status,
            ]);
        }

        return redirect()
            ->route('mentee.tasks.show', $task)
            ->with('success', $awaiting
                ? 'Submission received. Awaiting mentor review.'
                : 'Task completed!');
    }
}
