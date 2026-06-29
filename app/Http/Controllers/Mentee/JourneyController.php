<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    public function index()
    {
        $mentee     = auth()->user();
        $enrollment = \App\Models\MenteeEnrollment::where('mentee_id', $mentee->id)
            ->with('stream')->where('status','active')->first();

        $streams = collect();
        try {
            $streams = \DB::table('education_streams')->where('is_active',true)->orderBy('sort_order')->get();
        } catch (\Throwable) {}

        return view('mentee.journey', compact('enrollment','streams'));
    }

    public function month($month)
    {
        $monthRecord = \DB::table('curriculum_months')->find($month);
        $weeks = \DB::table('curriculum_weeks')->where('month_id',$month)->orderBy('week_number')->get();
        return view('mentee.journey-month', compact('monthRecord','weeks'));
    }

    public function week($week)
    {
        $weekRecord = \DB::table('curriculum_weeks')->find($week);
        $tasks = \DB::table('curriculum_tasks')->where('week_id',$week)->orderBy('order_index')->get();
        $mcqs  = \DB::table('curriculum_mcqs')->where('week_id',$week)->orderBy('order_index')->get();

        $completedTaskIds = \DB::table('student_curriculum_progress')
            ->where('user_id',auth()->id())->where('item_type','task')->where('is_completed',true)
            ->pluck('item_id')->toArray();

        $answeredMcqIds = \DB::table('mcq_attempts')
            ->where('user_id',auth()->id())->pluck('mcq_id')->toArray();

        return view('mentee.journey-week', compact('weekRecord','tasks','mcqs','completedTaskIds','answeredMcqIds'));
    }

    public function completeTask($taskId, Request $request)
    {
        $request->validate(['submission_text' => 'nullable|string', 'submission_url' => 'nullable|url']);

        \DB::table('student_curriculum_progress')->updateOrInsert(
            ['user_id'=>auth()->id(),'item_type'=>'task','item_id'=>$taskId],
            ['is_completed'=>true,'completed_at'=>now(),'submission_text'=>$request->submission_text,'submission_url'=>$request->submission_url,'submission_status'=>$request->submission_text || $request->submission_url ? 'submitted' : 'none','updated_at'=>now(),'created_at'=>now()]
        );

        return response()->json(['message'=>'Task completed!','completed'=>true]);
    }

    public function answerMcq($mcqId, Request $request)
    {
        $request->validate(['selected_index'=>'required|integer|between:0,3']);
        $mcq = \DB::table('curriculum_mcqs')->find($mcqId);
        if (!$mcq) return response()->json(['message'=>'Question not found.'],404);

        $isCorrect = $request->selected_index == $mcq->correct_index;
        $points    = $isCorrect ? $mcq->points : 0;

        \DB::table('mcq_attempts')->updateOrInsert(
            ['user_id'=>auth()->id(),'mcq_id'=>$mcqId],
            ['selected_index'=>$request->selected_index,'is_correct'=>$isCorrect,'points_earned'=>$points,'attempted_at'=>now(),'updated_at'=>now(),'created_at'=>now()]
        );

        return response()->json([
            'correct'       => $isCorrect,
            'correct_index' => (int)$mcq->correct_index,
            'explanation'   => $mcq->explanation,
            'points_earned' => $points,
        ]);
    }

    public function checkin($weekId, Request $request)
    {
        $request->validate(['mood_score'=>'nullable|integer|between:1,5','wins'=>'nullable|string','challenges'=>'nullable|string','questions'=>'nullable|string']);

        \DB::table('weekly_checkins')->updateOrInsert(
            ['mentee_id'=>auth()->id(),'week_id'=>$weekId],
            array_merge($request->only('mood_score','wins','challenges','questions'), ['submitted_at'=>now(),'updated_at'=>now(),'created_at'=>now()])
        );

        return response()->json(['message'=>'Check-in submitted! Your mentor will respond soon.']);
    }
}