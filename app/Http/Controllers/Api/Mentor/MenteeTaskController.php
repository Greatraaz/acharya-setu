<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MenteeTask;
use App\Models\StudentCurriculumProgress;
use App\Services\MenteeTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MenteeTaskController extends Controller
{
    public function __construct(private readonly MenteeTaskService $tasks)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $menteeId = $request->integer('mentee_id') ?: null;

        $items = MenteeTask::query()
            ->with(['mentee:id,name,email,avatar_url'])
            ->where('mentor_id', $request->user()->id)
            ->when($menteeId, fn ($q) => $q->where('mentee_id', $menteeId))
            ->latest()
            ->paginate(20);

        $mapped = $items->getCollection()->map(fn (MenteeTask $task) => $task->toPublicArray($task->getProgressForUser()));

        return response()->json([
            'status'  => true,
            'message' => 'Tasks fetched.',
            'data'    => $mapped->values(),
            'meta'    => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->tasks->validationRules(true));

        try {
            $task = $this->tasks->create($request->user(), $data, $request);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Task created.',
            'data'    => $task->load(['mentee', 'mentor'])->toPublicArray(),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $task = MenteeTask::query()
            ->with(['mentee', 'mentor'])
            ->where('mentor_id', $request->user()->id)
            ->find($id);

        if (! $task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Task fetched.',
            'data'    => $task->toPublicArray($task->getProgressForUser()),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $task = MenteeTask::query()->where('mentor_id', $request->user()->id)->find($id);
        if (! $task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        $data = $request->validate($this->tasks->validationRules(false));

        try {
            $task = $this->tasks->update($request->user(), $task, $data, $request);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Task updated.',
            'data'    => $task->toPublicArray($task->getProgressForUser()),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $task = MenteeTask::query()->where('mentor_id', $request->user()->id)->find($id);
        if (! $task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        $this->tasks->delete($request->user(), $task);

        return response()->json(['status' => true, 'message' => 'Task deleted.']);
    }

    public function pending(Request $request): JsonResponse
    {
        $menteeId = $request->integer('mentee_id') ?: null;
        $rows = $this->tasks->pendingForMentor($request->user()->id, $menteeId)->paginate(20);

        $data = $rows->getCollection()->map(function (StudentCurriculumProgress $p) {
            $task = MenteeTask::with('mentee')->find($p->item_id);

            return [
                'progress_id'       => $p->id,
                'submission_status' => $p->submission_status,
                'submission_text'   => $p->submission_text,
                'submission_url'    => $p->submissionLink(),
                'submitted_at'      => $p->updated_at?->toDateTimeString(),
                'mentee'            => $p->user ? [
                    'id'         => $p->user->id,
                    'name'       => $p->user->name,
                    'email'      => $p->user->email,
                    'avatar_url' => $p->user->avatar_url,
                ] : null,
                'task'              => $task?->toPublicArray($p),
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Pending task submissions.',
            'data'    => $data->values(),
            'meta'    => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'total'        => $rows->total(),
            ],
        ]);
    }

    public function review(Request $request, int $progress): JsonResponse
    {
        $data = $request->validate([
            'submission_status' => 'required|in:approved,rejected',
            'mentor_feedback'   => 'nullable|string|max:5000',
        ]);

        $record = StudentCurriculumProgress::query()
            ->where('item_type', MenteeTask::ITEM_TYPE)
            ->find($progress);

        if (! $record) {
            return response()->json(['status' => false, 'message' => 'Submission not found.'], 404);
        }

        try {
            $updated = $this->tasks->review(
                $request->user(),
                $record,
                $data['submission_status'],
                $data['mentor_feedback'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        $task = MenteeTask::find($updated->item_id);

        return response()->json([
            'status'  => true,
            'message' => 'Submission '.$data['submission_status'].'.',
            'data'    => [
                'progress_id'       => $updated->id,
                'submission_status' => $updated->submission_status,
                'mentor_feedback'   => $updated->mentor_feedback,
                'task'              => $task?->toPublicArray($updated),
            ],
        ]);
    }
}
