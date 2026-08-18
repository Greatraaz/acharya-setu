<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
    /**
     * Get all assessments for mentee.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            if (!Schema::hasTable('assessments')) {
                return response()->json([
                    'success' => true,
                    'message' => 'No assessments available.',
                    'data' => [],
                ]);
            }

            $assessments = Assessment::query()
                ->withCount('questions')
                ->latest()
                ->get()
                ->map(function (Assessment $assessment) use ($user) {

                    $progress = null;

                    if (Schema::hasTable('assessment_progress')) {
                        $progress = AssessmentProgress::where('user_id', $user->id)
                            ->where('assessment_id', $assessment->id)
                            ->first();
                    }

                    return [
                        'id' => $assessment->id,
                        'title' => $assessment->title ?? null,
                        'description' => $assessment->description ?? null,
                        'question_count' => (int) $assessment->questions_count,

                        'completed' => (bool) ($progress?->completed_at),
                        'score' => $progress?->score,

                        'progress' => $progress ? [
                            'completed_at' => $progress->completed_at,
                            'last_question' => $progress->last_question,
                        ] : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Assessments fetched successfully.',
                'data' => $assessments,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch assessments.',
            ], 500);
        }
    }


    /**
     * Get a single assessment with questions and user's progress.
     */
    public function show(Request $request, int $id)
    {
        $user = $request->user();

        $assessment = Assessment::with([
            'questions.category',
            'scoreBands'
        ])->find($id);

        if (!$assessment) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not found.',
            ], 404);
        }

        $progress = null;

        if (Schema::hasTable('assessment_progress')) {
            $progress = AssessmentProgress::where('user_id', $user->id)
                ->where('assessment_id', $id)
                ->first();
        }

        /*
         * Find score band if assessment is already completed.
         */
        $scoreBand = null;

        if ($progress && $progress->completed_at) {
            $scoreBand = $assessment->scoreBands->first(
                fn ($band) =>
                    $progress->score >= $band->range_from &&
                    $progress->score <= $band->range_to
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Assessment fetched successfully.',

            'data' => [
                'id' => $assessment->id,
                'title' => $assessment->title ?? null,
                'description' => $assessment->description ?? null,

                'question_count' => $assessment->questions->count(),

                'questions' => $assessment->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'question' => $question->question ?? $question->title ?? null,

                        'category' => $question->category ? [
                            'id' => $question->category->id,
                            'name' => $question->category->name ?? null,
                        ] : null,

                        'options' => $question->options ?? null,
                    ];
                }),

                'progress' => $progress ? [
                    'answers' => $progress->answers,
                    'score' => $progress->score,
                    'completed' => (bool) $progress->completed_at,
                    'completed_at' => $progress->completed_at,
                    'last_question' => $progress->last_question,
                ] : null,

                'result' => $scoreBand ? [
                    'score' => $progress->score,
                    'heading' => $scoreBand->heading,
                    'from' => $scoreBand->range_from,
                    'to' => $scoreBand->range_to,
                    'description' => $scoreBand->description,
                ] : null,
            ],
        ]);
    }


    /**
     * Submit assessment answers.
     */
    public function submit(Request $request, int $id)
    {
        $user = $request->user();

        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|integer|min:0|max:3',
        ]);

        $assessment = Assessment::with([
            'questions',
            'scoreBands'
        ])->find($id);

        if (!$assessment) {
            return response()->json([
                'success' => false,
                'message' => 'Assessment not found.',
            ], 404);
        }

        /*
         * Calculate total score.
         *
         * Each answer has a value between 0 and 3.
         */
        $totalScore = collect($data['answers'])
            ->sum(fn ($value) => (int) $value);

        /*
         * Find matching score band.
         */
        $band = $assessment->scoreBands->first(
            fn ($band) =>
                $totalScore >= $band->range_from &&
                $totalScore <= $band->range_to
        );

        /*
         * Save / update user's assessment progress.
         */
        $progress = AssessmentProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'assessment_id' => $id,
            ],
            [
                'answers' => $data['answers'],
                'score' => $totalScore,
                'completed_at' => now(),
                'last_question' => max(
                    0,
                    count($data['answers']) - 1
                ),
            ]
        );

        $message = $band
            ? "Assessment submitted. Score {$totalScore} — {$band->heading}."
            : "Assessment submitted. Score: {$totalScore}";

        return response()->json([
            'success' => true,
            'message' => $message,

            'data' => [
                'assessment_id' => $assessment->id,

                'score' => $totalScore,

                'total_questions' => $assessment->questions->count(),

                'answers' => $data['answers'],

                'completed' => true,

                'completed_at' => $progress->completed_at,

                'result' => $band ? [
                    'heading' => $band->heading,
                    'from' => $band->range_from,
                    'to' => $band->range_to,

                    // HTML is returned because your DB currently stores
                    // formatted HTML descriptions.
                    'description' => $band->description,
                ] : null,
            ],
        ]);
    }
}