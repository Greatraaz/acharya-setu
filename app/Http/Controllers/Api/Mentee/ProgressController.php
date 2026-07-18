<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\{
    CurriculumMcq,
    CurriculumTask,
    EducationStream,
    McqAttempt,
    MentorVideoFile,
    MentorVideoWatch,
    StudentCurriculumProgress,
    TaskSupportingMaterial,
};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Storage;

class ProgressController extends Controller
{
    // GET /mentee/progress
    public function index(Request $request): JsonResponse
    {
        $menteeId = $request->user()->id;
        $summary  = StudentCurriculumProgress::getMenteeProgressSummary($menteeId);

        $tracks = EducationStream::where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (EducationStream $track) => [
                'id'       => $track->id,
                'name'     => $track->name,
                'slug'     => $track->slug,
                'progress' => StudentCurriculumProgress::getOverallProgress($menteeId, $track->id),
            ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mentee_id'  => $menteeId,
            'summary'    => $summary,
            'tracks'     => $tracks,
        ]);
    }

    // POST /mentee/curriculum/tasks/{task}/complete
    public function completeTask(Request $request, int $task): JsonResponse
    {
        $menteeId  = $request->user()->id;
        $taskModel = CurriculumTask::where('id', $task)
            ->where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->firstOrFail();

        $extra    = [];
        $complete = true;

        if ($taskModel->submission_type && $taskModel->submission_type !== 'none') {
            $request->validate([
                'submission_text' => 'nullable|string|max:5000',
                'submission_url'  => 'nullable|url|max:2000',
                'submission_file' => 'nullable|file|max:' . CurriculumTask::ATTACHMENT_MAX_KB,
            ]);

            $extra['submission_status'] = 'submitted';
            $complete                   = false;

            if ($request->hasFile('submission_file')) {
                $path = $request->file('submission_file')->store("submissions/{$menteeId}", 'public');
                $extra['submission_url'] = Storage::disk('public')->url($path);
            }

            if ($request->filled('submission_text')) {
                $extra['submission_text'] = $request->submission_text;
            }

            if ($request->filled('submission_url')) {
                $extra['submission_url'] = $request->submission_url;
            }
        }

        $progress = StudentCurriculumProgress::markComplete(
            $menteeId,
            'task',
            $taskModel->id,
            array_merge($extra, ['is_completed' => $complete])
        );

        $summary = StudentCurriculumProgress::getMenteeProgressSummary($menteeId);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => $complete ? 'Task marked complete.' : 'Submission received. Awaiting mentor review.',
            'completed'  => $complete,
            'progress'   => $progress,
            'summary'    => $summary,
        ]);
    }

    // POST /mentee/curriculum/mcqs/answer
    // Body: { "answers": [ { "mcq_id": 1, "selected_option": 2 }, ... ] }
    // selected_option is 1-based. Re-answers are allowed.
    public function answerMcq(Request $request, ?int $mcq = null): JsonResponse
    {
        $menteeId = $request->user()->id;

        // Legacy single-MCQ URL: /mcqs/{mcq}/answer
        if ($mcq !== null && ! $request->has('answers')) {
            $request->merge([
                'answers' => [[
                    'mcq_id'          => $mcq,
                    'selected_option' => $request->input('selected_option'),
                    'selected_index'  => $request->input('selected_index'),
                ]],
            ]);
        }

        $data = $request->validate([
            'answers'                   => 'required|array|min:1',
            'answers.*.mcq_id'          => 'required|integer|distinct',
            'answers.*.selected_option' => 'nullable|integer|min:1|max:6',
            'answers.*.selected_index'  => 'nullable|integer|min:0|max:5',
        ]);

        $mcqIds = collect($data['answers'])->pluck('mcq_id')->all();
        $mcqs = CurriculumMcq::whereIn('id', $mcqIds)
            ->where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $results = [];

        foreach ($data['answers'] as $row) {
            $mcqId = (int) $row['mcq_id'];
            $mcqModel = $mcqs->get($mcqId);

            if (! $mcqModel) {
                $results[] = [
                    'mcq_id'  => $mcqId,
                    'status'  => false,
                    'message' => 'MCQ not found.',
                ];
                continue;
            }

            if (! isset($row['selected_index']) && ! isset($row['selected_option'])) {
                $results[] = [
                    'mcq_id'  => $mcqId,
                    'status'  => false,
                    'message' => 'Provide selected_option (1-based) or selected_index (0-based).',
                ];
                continue;
            }

            $selectedIndex = isset($row['selected_index'])
                ? (int) $row['selected_index']
                : (int) $row['selected_option'] - 1;

            $options = $mcqModel->options ?? [];
            if ($selectedIndex < 0 || $selectedIndex >= count($options)) {
                $results[] = [
                    'mcq_id'  => $mcqId,
                    'status'  => false,
                    'message' => 'selected_option is out of range for this MCQ.',
                ];
                continue;
            }

            $correct = $selectedIndex === (int) $mcqModel->correct_index;
            $points  = $correct ? (int) $mcqModel->points : 0;

            // Always allow re-answer — store a new attempt each time
            McqAttempt::create([
                'user_id'        => $menteeId,
                'mcq_id'         => $mcqModel->id,
                'selected_index' => $selectedIndex,
                'is_correct'     => $correct,
                'points_earned'  => $points,
                'attempted_at'   => now(),
            ]);

            if ($correct) {
                StudentCurriculumProgress::markComplete($menteeId, 'mcq', $mcqModel->id);
            } else {
                StudentCurriculumProgress::where('user_id', $menteeId)
                    ->where('item_type', 'mcq')
                    ->where('item_id', $mcqModel->id)
                    ->delete();
            }

            $results[] = [
                'mcq_id'         => $mcqModel->id,
                'status'         => true,
                'correct'        => $correct,
                'selected_index' => $selectedIndex,
                'correct_index'  => (int) $mcqModel->correct_index,
                'correct_answer' => $options[(int) $mcqModel->correct_index] ?? null,
                'points_earned'  => $points,
                'explanation'    => $mcqModel->explanation,
            ];
        }

        $summary = StudentCurriculumProgress::getMenteeProgressSummary($menteeId);
        $answered = collect($results)->where('status', true);
        $correctCount = $answered->where('correct', true)->count();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'MCQ answers submitted.',
            'summary'    => [
                'submitted' => count($data['answers']),
                'answered'  => $answered->count(),
                'correct'   => $correctCount,
                'incorrect' => $answered->count() - $correctCount,
            ],
            'results'    => $results,
            'progress'   => $summary,
        ]);
    }

    // POST /mentee/mentor-videos/files/{file}/watched
    public function markVideoWatched(Request $request, int $file): JsonResponse
    {
        $menteeId = $request->user()->id;

        $videoFile = MentorVideoFile::where('id', $file)
            ->whereHas('mentorVideo', fn ($q) => $q->where('is_active', true))
            ->firstOrFail();

        MentorVideoWatch::updateOrCreate(
            [
                'mentee_id'            => $menteeId,
                'mentor_video_file_id' => $videoFile->id,
            ],
            ['watched_at' => now()]
        );

        $summary = StudentCurriculumProgress::getMenteeProgressSummary($menteeId);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Video marked as watched.',
            'file_id'    => $videoFile->id,
            'summary'    => $summary,
        ]);
    }

    // POST /mentee/curriculum/materials/{material}/complete
    public function completeMaterial(Request $request, int $material): JsonResponse
    {
        $menteeId = $request->user()->id;

        $materialModel = TaskSupportingMaterial::where('id', $material)
            ->where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->firstOrFail();

        $progress = StudentCurriculumProgress::markComplete(
            $menteeId,
            'material',
            $materialModel->id
        );

        $summary = StudentCurriculumProgress::getMenteeProgressSummary($menteeId);

        return response()->json([
            'status'      => true,
            'statuscode'  => 200,
            'message'     => 'Supporting material marked complete.',
            'material_id' => $materialModel->id,
            'progress'    => $progress,
            'summary'     => $summary,
        ]);
    }
}
