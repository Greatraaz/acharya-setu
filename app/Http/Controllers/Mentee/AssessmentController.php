<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
    public function index()
    {
        $assessments = collect();

        try {
            if (Schema::hasTable('assessments')) {
                $assessments = Assessment::query()
                    ->orderBy('month')
                    ->get()
                    ->map(function (Assessment $a) {
                        $questions = $a->questions ?? [];
                        $progress = null;
                        if (Schema::hasTable('assessment_progress')) {
                            $progress = AssessmentProgress::where('user_id', auth()->id())
                                ->where('assessment_id', $a->id)
                                ->first();
                        }

                        $a->question_count = is_array($questions) ? count($questions) : 0;
                        $a->progress = $progress;
                        $a->completed = (bool) ($progress?->completed_at);
                        $a->score = $progress?->score;

                        return $a;
                    });
            }
        } catch (\Throwable) {
            $assessments = collect();
        }

        return view('frontend.mentee.assessments', compact('assessments'));
    }

    public function show(int $id)
    {
        $assessment = Assessment::findOrFail($id);
        $questions = collect($assessment->questions ?? [])->values();
        $progress = Schema::hasTable('assessment_progress')
            ? AssessmentProgress::where('user_id', auth()->id())->where('assessment_id', $id)->first()
            : null;

        return view('frontend.mentee.assessment-show', compact('assessment', 'questions', 'progress'));
    }

    public function submit(Request $request, int $id)
    {
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|integer|min:0',
        ]);

        $assessment = Assessment::findOrFail($id);
        $questions = $assessment->questions ?? [];
        $correct = 0;

        foreach ($data['answers'] as $idx => $ans) {
            if (isset($questions[$idx]) && (int) ($questions[$idx]['correct_index'] ?? -1) === (int) $ans) {
                $correct++;
            }
        }

        $total = count($questions);
        $score = $total ? round($correct / $total * 100, 2) : 0;

        $progress = AssessmentProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'assessment_id' => $id],
            [
                'answers' => $data['answers'],
                'score' => $score,
                'completed_at' => now(),
                'last_question' => max(0, count($data['answers']) - 1),
            ]
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Assessment submitted!',
                'result' => [
                    'score' => $score,
                    'correct' => $correct,
                    'total' => $total,
                ],
                'progress' => $progress,
                'redirect' => route('mentee.assessments.show', $id),
            ]);
        }

        return redirect()
            ->route('mentee.assessments.show', $id)
            ->with('success', "Assessment submitted! Score: {$score}% ({$correct}/{$total})");
    }
}
