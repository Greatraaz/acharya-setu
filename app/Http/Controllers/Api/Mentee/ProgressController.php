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

    // POST /mentee/curriculum/mcqs/{mcq}/answer
    public function answerMcq(Request $request, int $mcq): JsonResponse
    {
        $menteeId = $request->user()->id;
        $mcqModel = CurriculumMcq::where('id', $mcq)
            ->where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->firstOrFail();

        $data = $request->validate([
            'selected_index'  => 'nullable|integer|min:0|max:5',
            'selected_option' => 'nullable|integer|min:1|max:6',
        ]);

        if (! isset($data['selected_index']) && ! isset($data['selected_option'])) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'Provide selected_index (0-based) or selected_option (1-based).',
            ], 422);
        }

        if ($mcqModel->isAnsweredCorrectlyByUser($menteeId)) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'Already answered correctly.',
            ], 422);
        }

        $selectedIndex = isset($data['selected_index'])
            ? (int) $data['selected_index']
            : (int) $data['selected_option'] - 1;

        $correct = $selectedIndex === (int) $mcqModel->correct_index;
        $points  = $correct ? $mcqModel->points : 0;

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
        }

        $summary = StudentCurriculumProgress::getMenteeProgressSummary($menteeId);

        return response()->json([
            'status'         => true,
            'statuscode'     => 200,
            'correct'        => $correct,
            'points_earned'  => $points,
            'explanation'    => $mcqModel->explanation,
            'summary'        => $summary,
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
}
