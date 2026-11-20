<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $status = trim((string) $request->input('status', ''));

        $quizzes = Quiz::query()
            ->with('creator:id,name')
            ->withCount(['questions', 'attempts'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->when($status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($status === 'draft', fn ($q) => $q->where('is_published', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'     => Quiz::query()->count(),
            'published' => Quiz::query()->where('is_published', true)->count(),
            'draft'     => Quiz::query()->where('is_published', false)->count(),
            'attempts'  => QuizAttempt::query()->whereNotNull('completed_at')->count(),
        ];

        return view('admin.quizzes.index', compact('quizzes', 'search', 'status', 'stats'));
    }

    public function create()
    {
        return view('admin.quizzes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'                            => 'required|string|max:200',
            'description'                      => 'nullable|string',
            'time_limit'                       => 'nullable|integer|min:1',
            'pass_score'                       => 'required|integer|min:1|max:100',
            'questions'                        => 'required|array|min:1',
            'questions.*.question'             => 'required|string',
            'questions.*.type'                 => 'required|in:mcq,true_false,short_answer',
            'questions.*.marks'                => 'required|integer|min:1',
            'questions.*.options'              => 'nullable|array',
            'questions.*.options.*.text'       => 'required_with:questions.*.options|string',
            'questions.*.options.*.is_correct' => 'nullable',
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

                if (! empty($q['options'])) {
                    foreach ($q['options'] as $j => $opt) {
                        QuizOption::create([
                            'question_id' => $question->id,
                            'option_text' => $opt['text'],
                            'is_correct'  => filter_var($opt['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'order'       => $j,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created.');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['questions.options', 'creator:id,name']);
        $attempt = $quiz->userAttempt(Auth::user());
        $attemptStats = [
            'total'   => $quiz->attempts()->whereNotNull('completed_at')->count(),
            'passed'  => $quiz->attempts()->whereNotNull('completed_at')->where('passed', true)->count(),
            'avg'     => (int) round((float) $quiz->attempts()->whereNotNull('completed_at')->avg('percentage')),
        ];

        return view('admin.quizzes.show', compact('quiz', 'attempt', 'attemptStats'));
    }

    public function togglePublish(Quiz $quiz)
    {
        $quiz->update(['is_published' => ! $quiz->is_published]);

        return back()->with(
            'success',
            $quiz->is_published ? 'Quiz published for mentees.' : 'Quiz moved to draft.'
        );
    }

    public function attempt(Quiz $quiz)
    {
        $quiz->load('questions.options');

        QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->whereNull('completed_at')
            ->delete();

        $attempt = QuizAttempt::create([
            'quiz_id'    => $quiz->id,
            'user_id'    => Auth::id(),
            'started_at' => now(),
        ]);

        return view('admin.quizzes.attempt', compact('quiz', 'attempt'));
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
                        'attempt_id'  => $attempt->id,
                        'question_id' => $question->id,
                        'text_answer' => $answer,
                        'is_correct'  => false,
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

        return redirect()->route('admin.quizzes.result', [$quiz, $attempt])
            ->with('success', 'Quiz submitted.');
    }

    public function result(Quiz $quiz, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id() && $quiz->show_results, 403);
        abort_unless($attempt->quiz_id === $quiz->id, 404);
        $attempt->load(['answers.question.options', 'answers.option']);
        $quiz->load('questions.options');

        return view('admin.quizzes.result', compact('quiz', 'attempt'));
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted.');
    }
}
