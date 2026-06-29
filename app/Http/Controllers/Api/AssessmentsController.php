<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Assessment, AssessmentProgress};
use Illuminate\Http\{Request, JsonResponse};


class AssessmentsController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $u    = $request->user();
        $list = Assessment::orderBy('month')->get()->map(function ($a) use ($u) {
            $p = AssessmentProgress::where('user_id', $u->id)->where('assessment_id', $a->id)->first();
            return [
                'id'            => $a->id,
                'month'         => $a->month,
                'title'         => $a->title,
                'description'   => $a->description,
                'questionCount' => count($a->questions ?? []),
                'completed'     => !!$p?->completed_at,
                'score'         => $p?->score,
                'lastQuestion'  => $p?->last_question ?? 0,
            ];
        });
        return response()->json(['assessments' => $list]);
    }

   
    public function show(int $id): JsonResponse
    {
        $a = Assessment::findOrFail($id);
        return response()->json([
            'assessment' => $a,
            'questions'  => collect($a->questions ?? [])->map(fn($q, $i) => array_merge($q, ['id' => $i])),
        ]);
    }

    
    public function submit(Request $request, int $id): JsonResponse
    {
        $d       = $request->validate(['answers' => 'required|array']);
        $a       = Assessment::findOrFail($id);
        $qs      = $a->questions ?? [];
        $correct = 0;

        foreach ($d['answers'] as $idx => $ans) {
            if (isset($qs[$idx]) && ($qs[$idx]['correct_index'] ?? -1) == $ans) {
                $correct++;
            }
        }

        $score = count($qs) ? round($correct / count($qs) * 100, 2) : 0;
        $p     = AssessmentProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'assessment_id' => $id],
            ['answers' => $d['answers'], 'score' => $score, 'completed_at' => now(), 'last_question' => count($d['answers']) - 1]
        );

        return response()->json([
            'result'   => ['score' => $score, 'correct' => $correct, 'total' => count($qs)],
            'progress' => $p,
        ]);
    }
}
