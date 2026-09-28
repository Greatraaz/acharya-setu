<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\MenteeTask;
use App\Services\MenteeTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class MenteeTaskController extends Controller
{
    public function __construct(private readonly MenteeTaskService $tasks)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $items = MenteeTask::query()
            ->with('mentor:id,name,avatar_url,email')
            ->where('mentee_id', $request->user()->id)
            ->where('is_active', true)
            ->latest()
            ->paginate(20);

        $mapped = $items->getCollection()->map(function (MenteeTask $task) {
            return $task->toPublicArray($task->getProgressForUser());
        });

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

    public function show(Request $request, int $id): JsonResponse
    {
        $task = MenteeTask::query()
            ->with('mentor:id,name,avatar_url,email')
            ->where('mentee_id', $request->user()->id)
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

    public function submit(Request $request, int $id): JsonResponse
    {
        $task = MenteeTask::query()
            ->where('mentee_id', $request->user()->id)
            ->find($id);

        if (! $task) {
            return response()->json(['status' => false, 'message' => 'Task not found.'], 404);
        }

        try {
            $progress = $this->tasks->submit($request->user(), $task, $request);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => false,
                'message' => collect($e->errors())->flatten()->first() ?: $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 422);
        }

        $awaiting = $progress->submission_status === 'submitted' && ! $progress->is_completed;

        return response()->json([
            'status'  => true,
            'message' => $awaiting
                ? 'Submission received. Awaiting mentor review.'
                : 'Task completed!',
            'data'    => [
                'completed'         => (bool) $progress->is_completed,
                'awaiting_review'   => $awaiting,
                'submission_status' => $progress->submission_status,
                'task'              => $task->fresh(['mentor'])->toPublicArray($progress),
            ],
        ]);
    }
}
