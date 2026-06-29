<?php
// ── Mentee DashboardController ────────────────────────────────
namespace App\Http\Controllers\Mentee;
use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\MenteeEnrollment;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $mentee = auth()->user();

        $upcomingSessions = ConsultationSession::where('mentee_id', $mentee->id)
            ->with('mentor')
            ->whereIn('status', ['pending','confirmed'])
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get();

        $upcomingCount = $upcomingSessions->count();

        $enrollment = MenteeEnrollment::where('mentee_id', $mentee->id)
            ->with(['stream', 'mentor'])
            ->where('status', 'active')
            ->first();

        $weekTasks = [];
        if ($enrollment) {
            try {
                $week = \DB::table('curriculum_weeks')
                    ->join('curriculum_months', 'curriculum_weeks.month_id', '=', 'curriculum_months.id')
                    ->where('curriculum_months.stream_id', $enrollment->stream_id)
                    ->where('curriculum_months.month_number', $enrollment->current_month)
                    ->where('curriculum_weeks.week_number', $enrollment->current_week)
                    ->select('curriculum_weeks.id')
                    ->first();

                if ($week) {
                    $completedTaskIds = \DB::table('student_curriculum_progress')
                        ->where('user_id', $mentee->id)->where('item_type','task')->where('is_completed',true)
                        ->pluck('item_id')->toArray();

                    $weekTasks = \DB::table('curriculum_tasks')
                        ->where('week_id', $week->id)->orderBy('order_index')
                        ->get()->map(fn($t) => (object)['title'=>$t->title,'is_completed'=>in_array($t->id,$completedTaskIds)]);
                }
            } catch (\Throwable) {}
        }

        $recommendedMentors = User::where('role','mentor')
            ->where('mentor_status','approved')->where('is_active',true)
            ->orderByDesc('rating')->limit(3)->get();

        $stats = [
            'sessions' => ConsultationSession::where('mentee_id',$mentee->id)->where('status','completed')->count(),
            'minutes'  => ConsultationSession::where('mentee_id',$mentee->id)->where('status','completed')->sum('duration_minutes'),
            'progress' => $enrollment ? (int)(($enrollment->current_month / 6) * 100) : 0,
        ];

        return view('frontend.mentee.dashboard', compact('upcomingSessions','upcomingCount','enrollment','weekTasks','recommendedMentors','stats'));
    }
}