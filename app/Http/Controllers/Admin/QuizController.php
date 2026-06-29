<?php

namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
 
class QuizController extends Controller
{
 
    public function index()
    {
        $quizzes = Quiz::where('is_published', true)
            ->with('creator')
            ->withCount('questions')
            ->latest()
            ->get();
 
        $myAttempts = QuizAttempt::where('user_id', Auth::id())
            ->pluck('quiz_id')
            ->toArray();
 
        return view('admin.quizzes.index', compact('quizzes', 'myAttempts'));
    }
 
    public function create()
    {
        return view('admin.quizzes.create');
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'title'                           => 'required|string|max:200',
            'description'                     => 'nullable|string',
            'time_limit'                      => 'nullable|integer|min:1',
            'pass_score'                      => 'required|integer|min:1|max:100',
            'questions'                       => 'required|array|min:1',
            'questions.*.question'            => 'required|string',
            'questions.*.type'                => 'required|in:mcq,true_false,short_answer',
            'questions.*.marks'               => 'required|integer|min:1',
            'questions.*.options'             => 'nullable|array',
            'questions.*.options.*.text'      => 'required_with:questions.*.options|string',
            'questions.*.options.*.is_correct' => 'nullable|boolean',
        ]);
 
        DB::transaction(function () use ($request) {
            $quiz = Quiz::create([
                'title'        => $request->title,
                'description'  => $request->description,
                'time_limit'   => $request->time_limit,
                'pass_score'   => $request->pass_score,
                'is_published' => $request->boolean('is_published'),
                'show_results' => true,
                'created_by'   => Auth::id(),
            ]);
 
            foreach ($request->questions as $i => $q) {
                $question = QuizQuestion::create([
                    'quiz_id'     => $quiz->id,
                    'question'    => $q['question'],
                    'type'        => $q['type'],
                    'marks'       => $q['marks'],
                    'order'       => $i,
                    'explanation' => $q['explanation'] ?? null,
                ]);
 
                if (!empty($q['options'])) {
                    foreach ($q['options'] as $j => $opt) {
                        QuizOption::create([
                            'question_id' => $question->id,
                            'option_text' => $opt['text'],
                            'is_correct'  => isset($opt['is_correct']) ? (bool)$opt['is_correct'] : false,
                            'order'       => $j,
                        ]);
                    }
                }
            }
        });
 
        return redirect()->route('quizzes.index')->with('success', 'Quiz created!');
    }
 
    public function show(Quiz $quiz)
    {
        abort_unless($quiz->is_published, 403);
        $quiz->load('questions.options');
        $attempt = $quiz->userAttempt(Auth::user());
 
        return view('quizzes.show', compact('quiz', 'attempt'));
    }
 
    public function attempt(Quiz $quiz)
    {
        abort_unless($quiz->is_published, 403);
        $quiz->load('questions.options');
 
        $attempt = QuizAttempt::create([
            'quiz_id'    => $quiz->id,
            'user_id'    => Auth::id(),
            'started_at' => now(),
        ]);
 
        return view('quizzes.attempt', compact('quiz', 'attempt'));
    }
 
    public function submit(Request $request, Quiz $quiz, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id() && !$attempt->completed_at, 403);
 
        $quiz->load('questions.options');
 
        $score       = 0;
        $totalMarks  = 0;
 
        DB::transaction(function () use ($request, $quiz, $attempt, &$score, &$totalMarks) {
            foreach ($quiz->questions as $question) {
                $totalMarks += $question->marks;
                $answer      = $request->input("answers.{$question->id}");
                $isCorrect   = false;
                $optionId    = null;
 
                if ($question->type === 'short_answer') {
                    QuizAnswer::create([
                        'attempt_id'  => $attempt->id,
                        'question_id' => $question->id,
                        'text_answer' => $answer,
                        'is_correct'  => false,
                    ]);
                    continue;
                }
 
                $correctOption = $question->options->firstWhere('is_correct', true);
                if ($answer && $correctOption && (int)$answer === $correctOption->id) {
                    $isCorrect = true;
                    $score    += $question->marks;
                    $optionId  = (int)$answer;
                } elseif ($answer) {
                    $optionId = (int)$answer;
                }
 
                QuizAnswer::create([
                    'attempt_id'  => $attempt->id,
                    'question_id' => $question->id,
                    'option_id'   => $optionId,
                    'is_correct'  => $isCorrect,
                ]);
            }
 
            $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100) : 0;
 
            $attempt->update([
                'score'        => $score,
                'total_marks'  => $totalMarks,
                'percentage'   => $percentage,
                'passed'       => $percentage >= $quiz->pass_score,
                'completed_at' => now(),
            ]);
        });
 
        return redirect()->route('quizzes.result', [$quiz, $attempt])
            ->with('success', 'Quiz submitted!');
    }
 
    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id() && $quiz->show_results, 403);
        $attempt->load(['answers.question.options', 'answers.option']);
        $quiz->load('questions.options');
 
        return view('quizzes.result', compact('quiz', 'attempt'));
    }
 
    public function destroy(Quiz $quiz)
    {
        abort_unless($quiz->created_by === Auth::id(), 403);
        $quiz->delete();
        return redirect()->route('quizzes.index')->with('success', 'Quiz deleted.');
    }
}