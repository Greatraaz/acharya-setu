<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentQuestionController extends Controller
{
    /**
     * Get questions for an assessment
     */
    public function index(int $assessmentId): JsonResponse
    {
        $assessment = Assessment::findOrFail($assessmentId);

        $questions = $assessment->questions()
            ->get()
            ->map(function (AssessmentQuestion $question) {
                return [
                    'id'       => $question->id,
                    'question' => $question->question,
                    'options'  => collect($question->optionLabels())
                        ->map(fn ($text, $score) => [
                            'score' => (int) $score,
                            'text'  => $text,
                        ])
                        ->values(),
                    'sort_order' => $question->sort_order,
                ];
            })
            ->values();

        return response()->json([
            'status'      => true,
            'statuscode'  => 200,
            'assessment_id' => $assessment->id,
            'count'       => $questions->count(),
            'questions'   => $questions,
        ]);
    }

    /**
     * Create question
     */
    public function store(Request $request, int $assessmentId): JsonResponse
    {
        $assessment = Assessment::findOrFail($assessmentId);

        $data = $request->validate([
            'question'   => 'required|string|max:5000',
            'options'    => 'required|array|size:4',
            'options.0'  => 'required|string|max:200',
            'options.1'  => 'required|string|max:200',
            'options.2'  => 'required|string|max:200',
            'options.3'  => 'required|string|max:200',
        ]);

        $max = (int) AssessmentQuestion::where(
            'assessment_id',
            $assessment->id
        )->max('sort_order');

        $question = AssessmentQuestion::create([
            'assessment_id' => $assessment->id,
            'category_id'   => null,
            'question'      => $data['question'],
            'options'       => array_values($data['options']),
            'sort_order'    => $max + 1,
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Question created successfully.',
            'question'  => $this->formatQuestion($question),
        ], 201);
    }

    /**
     * Get one question
     */
    public function show(int $assessmentId, int $questionId): JsonResponse
    {
        $question = AssessmentQuestion::where('assessment_id', $assessmentId)
            ->findOrFail($questionId);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'question'   => $this->formatQuestion($question),
        ]);
    }

    /**
     * Update question
     */
    public function update(
        Request $request,
        int $assessmentId,
        int $questionId
    ): JsonResponse {
        $question = AssessmentQuestion::where('assessment_id', $assessmentId)
            ->findOrFail($questionId);

        $data = $request->validate([
            'question'   => 'required|string|max:5000',
            'options'    => 'required|array|size:4',
            'options.0'  => 'required|string|max:200',
            'options.1'  => 'required|string|max:200',
            'options.2'  => 'required|string|max:200',
            'options.3'  => 'required|string|max:200',
        ]);

        $question->update([
            'question' => $data['question'],
            'options'  => array_values($data['options']),
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Question updated successfully.',
            'question'   => $this->formatQuestion($question->fresh()),
        ]);
    }

    /**
     * Delete question
     */
    public function destroy(
        int $assessmentId,
        int $questionId
    ): JsonResponse {
        $question = AssessmentQuestion::where('assessment_id', $assessmentId)
            ->findOrFail($questionId);

        $question->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Question deleted successfully.',
        ]);
    }

    private function formatQuestion(AssessmentQuestion $question): array
    {
        return [
            'id'         => $question->id,
            'assessment_id' => $question->assessment_id,
            'question'   => $question->question,
            'options'    => collect($question->optionLabels())
                ->map(fn ($text, $score) => [
                    'score' => (int) $score,
                    'text'  => $text,
                ])
                ->values()
                ->all(),
            'sort_order' => $question->sort_order,
            'created_at' => $question->created_at,
            'updated_at' => $question->updated_at,
        ];
    }
}