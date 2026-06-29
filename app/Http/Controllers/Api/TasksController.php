<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CurriculumTask, Notification};
use Illuminate\Http\{Request, JsonResponse};

class  TasksController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        $f = $u->role === 'mentor' ? 'assigned_by' : 'user_id';
        $tasks = CurriculumTask::where($f, $u->id)
            ->with(['assignedTo:id,name', 'assignedBy:id,name'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['status' => true,'statuscode' => 200,'tasks' => $tasks,], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $d = $request->validate([
            'user_id'     => 'required|numeric|exists:users,id',
            'title'       => 'required|string|max:200',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'priority'    => 'sometimes|in:low,medium,high',
            'month'       => 'nullable|integer',
            'week_id'     => 'nullable|integer',
        ]);

        try {
            $t = CurriculumTask::create(array_merge($d, ['assigned_by' => $request->user()->id, 'status' => 'pending']));
            Notification::create([
                'user_id' => $d['user_id'],
                'type'    => 'task_assigned',
                'title'   => 'New Task',
                'body'    => "New task: {$d['title']}",
            ]);
            return response()->json([
                'status'     => true,
                'statuscode' => 201,
                'task'       => $t,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'     => false,
                'statuscode' => 500,
                'message'    => 'Failed to create task.',
                'error'      => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $t = CurriculumTask::find($id);
        if (!$t) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Task not found.',
            ], 404);
        }
        $t->update($request->validate([
            'status'      => 'sometimes|in:pending,in_progress,completed',
            'priority'    => 'sometimes|in:low,medium,high',
            'title'       => 'sometimes|string',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
        ]));
        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'task'       => $t,
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $task = CurriculumTask::find($id);
        if (!$task) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Task not found.',
            ], 404);
        }
        $task->delete();
        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Deleted',
        ], 200);
    }
}
