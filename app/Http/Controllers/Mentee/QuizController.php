<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $status = $request->input('status', 'all');
        $userId = Auth::id();

        $query = Quiz::where('is_published', true)
            ->with('creator')
            ->withCount('questions')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($status === 'completed', fn ($q) => $q->whereHas('attempts', fn ($a) => $a->where('user_id', $userId)->whereNotNull('completed_at')))
            ->when($status === 'not_attempted', fn ($q) => $q->whereDoesntHave('attempts', fn ($a) => $a->where('user_id', $userId)->whereNotNull('completed_at')))
            ->when($status === 'passed', fn ($q) => $q->whereHas('attempts', fn ($a) => $a->where('user_id', $userId)->where('passed', true)))
            ->when($status === 'failed', fn ($q) => $q->whereHas('attempts', fn ($a) => $a->where('user_id', $userId)->whereNotNull('completed_at')->where('passed', false)))
            ->latest();

        $quizzes = $query->paginate(12)->withQueryString();

        $myAttempts = QuizAttempt::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->get()
            ->keyBy('quiz_id');

        return view('frontend.mentee.quizzes', compact('quizzes', 'myAttempts', 'search', 'status'));
    }

    public function show(Quiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $quiz->load('questions.options');
        $attempt = $quiz->userAttempt(Auth::user());

        return view('frontend.mentee.quiz-show', compact('quiz', 'attempt'));
    }

    public function attempt(Quiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $quiz->load('questions.options');

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'started_at' => now(),
        ]);

        return view('frontend.mentee.quiz-attempt', compact('quiz', 'attempt'));
    }

    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id() && ! $attempt->completed_at, 403);
        abort_unless($attempt->quiz_id === $quiz->id, 404);

        $quiz->load('questions.options');

        $score = 0;
        $totalMarks = 0;

        DB::transaction(function () use ($request, $quiz, $attempt, &$score, &$totalMarks) {
            foreach ($quiz->questions as $question) {
                $totalMarks += $question->marks;
                $answer = $request->input("answers.{$question->id}");
                $isCorrect = false;
                $optionId = null;

                if ($question->type === 'short_answer') {
                    QuizAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'text_answer' => $answer,
                        'is_correct' => false,
                    ]);
                    continue;
                }

                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($answer && $correctOption && (int) $answer === (int) $correctOption->id) {
                    $isCorrect = true;
                    $score += $question->marks;
                    $optionId = (int) $answer;
                } elseif ($answer) {
                    $optionId = (int) $answer;
                }

                QuizAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'option_id' => $optionId,
                    'is_correct' => $isCorrect,
                ]);
            }

            $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100) : 0;

            $attempt->update([
                'score' => $score,
                'total_marks' => $totalMarks,
                'percentage' => $percentage,
                'passed' => $percentage >= $quiz->pass_score,
                'completed_at' => now(),
            ]);
        });

        return redirect()
            ->route('mentee.quizzes.result', [$quiz, $attempt])
            ->with('success', 'Quiz submitted!');
    }

    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);
        abort_unless($attempt->quiz_id === $quiz->id, 404);
        abort_unless($quiz->show_results, 403);

        $attempt->load(['answers.question.options', 'answers.option']);
        $quiz->load('questions.options');

        return view('frontend.mentee.quiz-result', compact('quiz', 'attempt'));
    }
}
