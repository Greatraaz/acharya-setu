<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{User, ConsultationSession, ActivityLog, MentorPendingChange, MenteeEnrollment, WeeklyCheckin};
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // ── Core stats ────────────────────────────────────────
        $totalMentees    = User::mentees()->count();
        $activeMentees   = User::mentees()->where('is_active', true)->count();
        $totalMentors    = User::mentors()->count();
        $approvedMentors = User::mentors()->where('mentor_status', 'approved')->count();
        $pendingMentors  = User::mentors()->where('mentor_status', 'pending')->count();
        $rejectedMentors = User::mentors()->where('mentor_status', 'rejected')->count();
        $suspendedMentors= User::mentors()->where('mentor_status', 'suspended')->count();
 
        $totalSessions    = ConsultationSession::count();
        $completedSessions= ConsultationSession::where('status', 'completed')->count();
        $sessionsToday    = ConsultationSession::whereDate('scheduled_at', today())->count();
        $ongoingSessions  = ConsultationSession::where('status', 'ongoing')->count();
 
        $monthRevenue = ConsultationSession::where('status', 'completed')
            ->whereMonth('ended_at', now()->month)
            ->whereYear('ended_at',  now()->year)
            ->sum('amount') ?? 0;
 
        $avgRating = User::mentors()->where('rating', '>', 0)->avg('rating') ?? 0;
 
        $pendingChanges = MentorPendingChange::where('status', 'pending')->count();
 
        // Check-in stats
        $totalCheckins   = WeeklyCheckin::whereNotNull('submitted_at')->count();
        $answeredCheckins= WeeklyCheckin::whereNotNull('submitted_at')->whereNotNull('mentor_response')->count();
 
        // ── Session chart (last 6 months) ─────────────────────
        $sessionChart = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            return [
                'month'    => $month->format('M'),
                'sessions' => ConsultationSession::whereYear('scheduled_at', $month->year)
                    ->whereMonth('scheduled_at', $month->month)
                    ->count(),
                'revenue'  => (int)(ConsultationSession::where('status', 'completed')
                    ->whereYear('ended_at', $month->year)
                    ->whereMonth('ended_at', $month->month)
                    ->sum('amount') ?? 0),
            ];
        });
 
        // ── Recent sessions ───────────────────────────────────
        $recentSessions = ConsultationSession::with(['mentor', 'mentee'])
            ->latest('scheduled_at')
            ->limit(5)
            ->get();
 
        // ── Recent activity logs ──────────────────────────────
        $recentLogs = ActivityLog::latest('logged_at')->limit(5)->get();
 
        // ── Enrollment stats by stream ────────────────────────
        $enrollmentStats = MenteeEnrollment::select('stream_id', DB::raw('count(*) as count'))
            ->where('status', 'active')
            ->groupBy('stream_id')
            ->with('stream:id,name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn($e) => (object)[
                'stream_name' => $e->stream->name ?? 'Unknown',
                'count'       => $e->count,
            ]);
 
        $stats = compact(
            'totalMentees', 'activeMentees',
            'totalMentors', 'approvedMentors', 'pendingMentors',
            'rejectedMentors', 'suspendedMentors',
            'totalSessions', 'completedSessions',
            'sessionsToday', 'ongoingSessions',
            'monthRevenue', 'avgRating', 'pendingChanges',
            'totalCheckins', 'answeredCheckins'
        );
 
        return view('admin.dashboard', compact(
            'stats', 'sessionChart', 'recentSessions',
            'recentLogs', 'enrollmentStats'
        ));
    }
}
