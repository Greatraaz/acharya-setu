<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Quiz, QuizResult};
use Illuminate\Http\{Request, JsonResponse};

class QuizzesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $quizzes = Quiz::where('is_active', true)
            ->withCount('questions')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'quizzes'    => $quizzes->items(),
            'pagination' => [
                'total'        => $quizzes->total(),
                'per_page'     => $quizzes->perPage(),
                'current_page' => $quizzes->currentPage(),
                'last_page'    => $quizzes->lastPage(),
                'from'         => $quizzes->firstItem(),
                'to'           => $quizzes->lastItem(),
            ],
        ], 200);
    }

    public function show(int $id): JsonResponse
    {
        $q = Quiz::with('questions')->find($id);

        if (!$q) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Quiz not found',
            ], 404);
        }

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'quiz'       => $q,
            'questions'  => $q->questions->map(fn($q) => $q->makeHidden('correct_index')),
        ], 200);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $d    = $request->validate([
            'answers'            => 'required|array',
            'time_taken_seconds' => 'nullable|integer'
        ]);
        $quiz = Quiz::with('questions')->find($id);

        if (!$quiz) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Quiz not found',
            ], 404);
        }

        $correct = 0;
        foreach ($d['answers'] as $qid => $ans) {
            $q = $quiz->questions->firstWhere('id', $qid);
            if ($q && $q->correct_index == $ans) $correct++;
        }
        $r = QuizResult::create([
            'quiz_id'            => $id,
            'user_id'            => $request->user()->id,
            'score'              => $correct,
            'total_questions'    => $quiz->questions->count(),
            'answers'            => $d['answers'],
            'time_taken_seconds' => $d['time_taken_seconds'] ?? null,
        ]);
        $totalQuestions = $quiz->questions->count();
        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'result'     => $r,
            'score'      => $correct,
            'total'      => $totalQuestions,
            'percentage' => $totalQuestions ? round($correct / $totalQuestions * 100) : 0,
        ], 201);
    }

    public function myResults(Request $request): JsonResponse
    {
        $results = QuizResult::where('user_id', $request->user()->id)
            ->with('quiz:id,title,category')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'results'    => $results,
        ], 200);
    }
}
