<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        $assessments = collect();
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $status = $request->input('status', 'all');

        try {
            if (Schema::hasTable('assessments')) {
                $userId = auth()->id();

                $query = Assessment::query()
                    ->withCount('questions')
                    ->when($search !== '', function ($q) use ($search) {
                        $q->where(function ($inner) use ($search) {
                            $inner->where('title', 'like', '%'.$search.'%')
                                ->orWhere('description', 'like', '%'.$search.'%');
                        });
                    })
                    ->when($status === 'completed' && Schema::hasTable('assessment_progress'), function ($q) use ($userId) {
                        $q->whereHas('progress', fn ($p) => $p->where('user_id', $userId)->whereNotNull('completed_at'));
                    })
                    ->when($status === 'pending' && Schema::hasTable('assessment_progress'), function ($q) use ($userId) {
                        $q->whereDoesntHave('progress', fn ($p) => $p->where('user_id', $userId)->whereNotNull('completed_at'));
                    })
                    ->latest();

                $assessments = $query->paginate(15)->withQueryString()->through(function (Assessment $a) use ($userId) {
                    $progress = null;
                    if (Schema::hasTable('assessment_progress')) {
                        $progress = AssessmentProgress::where('user_id', $userId)
                            ->where('assessment_id', $a->id)
                            ->first();
                    }

                    $a->question_count = (int) $a->questions_count;
                    $a->progress = $progress;
                    $a->completed = (bool) ($progress?->completed_at);
                    $a->score = $progress?->score;

                    return $a;
                });
            }
        } catch (\Throwable) {
            $assessments = collect();
        }

        return view('frontend.mentee.assessments', compact('assessments', 'search', 'status'));
    }

    public function show(int $id)
    {
        $assessment = Assessment::with(['questions.category', 'scoreBands'])->findOrFail($id);
        $questions = $assessment->questions;
        $progress = Schema::hasTable('assessment_progress')
            ? AssessmentProgress::where('user_id', auth()->id())->where('assessment_id', $id)->first()
            : null;

        // Get score band feedback if assessment is completed
        $scoreBand = null;
        if ($progress && $progress->completed_at) {
            $scoreBand = $assessment->scoreBands->first(
                fn ($b) => $progress->score >= $b->range_from && $progress->score <= $b->range_to
            );
        }

        return view('frontend.mentee.assessment-show', compact('assessment', 'questions', 'progress', 'scoreBand'));
    }

    public function submit(Request $request, int $id)
    {
        $data = $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'nullable|integer|min:0|max:3',
        ]);

        $assessment = Assessment::with(['questions', 'scoreBands'])->findOrFail($id);
        $totalScore = collect($data['answers'])->sum(fn ($v) => (int) $v);
        $band = $assessment->scoreBands->first(
            fn ($b) => $totalScore >= $b->range_from && $totalScore <= $b->range_to
        );

        $progress = AssessmentProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'assessment_id' => $id],
            [
                'answers'       => $data['answers'],
                'score'         => $totalScore,
                'completed_at'  => now(),
                'last_question' => max(0, count($data['answers']) - 1),
            ]
        );

        $message = $band
            ? "Assessment submitted. Score {$totalScore} — {$band->heading}."
            : "Assessment submitted. Score: {$totalScore}";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message'  => $message,
                'result'   => [
                    'score'   => $totalScore,
                    'total'   => $assessment->questions->count(),
                    'band'    => $band ? [
                        'heading'     => $band->heading,
                        'from'        => $band->range_from,
                        'to'          => $band->range_to,
                        'description' => $band->description,
                    ] : null,
                ],
                'progress' => $progress,
                'redirect' => route('mentee.assessments.show', $id),
            ]);
        }

        return redirect()
            ->route('mentee.assessments.show', $id)
            ->with('success', $message);
    }
}
