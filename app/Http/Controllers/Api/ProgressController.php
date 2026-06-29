<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Task, Session, AssessmentProgress, QuizResult};
use Illuminate\Http\{Request, JsonResponse};

class ProgressController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $u  = $request->user();
        $tt = Task::where('user_id', $u->id)->count();
        $ct = Task::where('user_id', $u->id)->where('status', 'completed')->count();
        $ts = Session::where('mentee_id', $u->id)->count();
        $cs = Session::where('mentee_id', $u->id)->where('status', 'completed')->count();
        $ap = AssessmentProgress::where('user_id', $u->id)->with('assessment:id,title,month')->get();
        $cm = $ap->filter(fn($a) => !$a->completed_at)->first()?->assessment?->month ?? 1;

        return response()->json([
            'progress' => [
                'totalTasks'         => $tt,
                'completedTasks'     => $ct,
                'taskCompletionRate' => $tt ? round($ct / $tt * 100) : 0,
                'totalSessions'      => $ts,
                'completedSessions'  => $cs,
                'assessments'        => $ap,
                'currentMonth'       => $cm,
                'quizzesTaken'       => QuizResult::where('user_id', $u->id)->count(),
                'avgQuizScore'       => round(QuizResult::where('user_id', $u->id)->avg('score') ?? 0, 1),
            ],
        ]);
    }
}
