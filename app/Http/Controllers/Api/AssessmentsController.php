<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentProgress;
use App\Services\AssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssessmentsController extends Controller
{
    public function __construct(private readonly AssessmentService $assessments)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        $list = Assessment::query()
            ->visibleToMentee($u)
            ->withCount('questions')
            ->latest()
            ->get()
            ->map(function ($a) use ($u) {
            $p = AssessmentProgress::where('user_id', $u->id)->where('assessment_id', $a->id)->first();

            return [
                'id'            => $a->id,
                'title'         => $a->title,
                'description'   => $a->description,
                'instructions'  => $a->instructions,
                'image'         => $a->imageUrl(),
                'icon'          => $a->iconUrl(),
                'questionCount' => (int) $a->questions_count,
                'completed'     => (bool) $p?->completed_at,
                'score'         => $p?->score,
                'lastQuestion'  => $p?->last_question ?? 0,
            ];
        });

        return response()->json(['assessments' => $list]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $a = Assessment::with(['questions', 'scoreBands'])->findOrFail($id);
        abort_unless($a->isVisibleToMentee($request->user()), 404);

        return response()->json([
            'assessment' => $this->assessments->formatForApi($a, true),
        ]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $d = $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'nullable|integer|min:0|max:3',
        ]);

        $a = Assessment::with(['questions', 'scoreBands'])->findOrFail($id);
        abort_unless($a->isVisibleToMentee($request->user()), 404);
        $totalScore = collect($d['answers'])->sum(fn ($v) => (int) $v);
        $band = $a->scoreBands->first(fn ($b) => $totalScore >= $b->range_from && $totalScore <= $b->range_to);

        $p = AssessmentProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'assessment_id' => $id],
            [
                'answers'       => $d['answers'],
                'score'         => $totalScore,
                'completed_at'  => now(),
                'last_question' => max(0, count($d['answers']) - 1),
            ]
        );

        return response()->json([
            'result' => [
                'score' => $totalScore,
                'total' => $a->questions->count(),
                'band'  => $band ? [
                    'heading'     => $band->heading,
                    'from'        => $band->range_from,
                    'to'          => $band->range_to,
                    'description' => $band->description,
                ] : null,
            ],
            'progress' => $p,
        ]);
    }
}
