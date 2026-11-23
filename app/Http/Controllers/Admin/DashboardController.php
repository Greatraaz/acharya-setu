<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ConsultationSession;
use App\Models\EducationStream;
use App\Models\MenteeEnrollment;
use App\Models\MentorPendingChange;
use App\Models\Offer;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WeeklyCheckin;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $prevMonth = $now->copy()->subMonth();

        // ── Core people stats ─────────────────────────────────
        $totalMentees = User::mentees()->count();
        $activeMentees = User::mentees()->where('is_active', true)->count();
        $menteesThisMonth = User::mentees()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $menteesPrevMonth = User::mentees()->whereMonth('created_at', $prevMonth->month)->whereYear('created_at', $prevMonth->year)->count();

        $totalMentors = User::mentors()->count();
        $approvedMentors = User::mentors()->where('mentor_status', 'approved')->count();
        $pendingMentors = User::mentors()->where('mentor_status', 'pending')->count();
        $rejectedMentors = User::mentors()->where('mentor_status', 'rejected')->count();
        $suspendedMentors = User::mentors()->where('mentor_status', 'suspended')->count();
        $mentorsThisMonth = User::mentors()->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();
        $mentorsPrevMonth = User::mentors()->whereMonth('created_at', $prevMonth->month)->whereYear('created_at', $prevMonth->year)->count();

        $totalExperts = User::query()->where('role', 'expert')->count();

        // ── Sessions ──────────────────────────────────────────
        $totalSessions = ConsultationSession::count();
        $completedSessions = ConsultationSession::where('status', 'completed')->count();
        $sessionsToday = ConsultationSession::whereDate('scheduled_at', today())->count();
        $ongoingSessions = ConsultationSession::where('status', 'upcoming')
            ->where('scheduled_at', '<=', now())
            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_minutes, 0) MINUTE) >= ?', [now()])
            ->count();
        $upcomingSessions = ConsultationSession::where('status', 'upcoming')
            ->where('scheduled_at', '>', now())
            ->count();
        $cancelledSessions = ConsultationSession::where('status', 'cancelled')->count();
        $sessionsThisMonth = ConsultationSession::whereMonth('scheduled_at', $now->month)->whereYear('scheduled_at', $now->year)->count();
        $sessionsPrevMonth = ConsultationSession::whereMonth('scheduled_at', $prevMonth->month)->whereYear('scheduled_at', $prevMonth->year)->count();

        // ── Revenue ───────────────────────────────────────────
        $monthRevenue = (float) (ConsultationSession::where('status', 'completed')
            ->whereMonth('ended_at', $now->month)
            ->whereYear('ended_at', $now->year)
            ->sum('amount') ?? 0);
        $prevMonthRevenue = (float) (ConsultationSession::where('status', 'completed')
            ->whereMonth('ended_at', $prevMonth->month)
            ->whereYear('ended_at', $prevMonth->year)
            ->sum('amount') ?? 0);
        $lifetimeRevenue = (float) (ConsultationSession::where('status', 'completed')->sum('amount') ?? 0);

        $avgRating = (float) (User::mentors()->where('rating', '>', 0)->avg('rating') ?? 0);
        $pendingChanges = MentorPendingChange::where('status', 'pending')->count();

        // Check-ins
        $totalCheckins = WeeklyCheckin::whereNotNull('submitted_at')->count();
        $answeredCheckins = WeeklyCheckin::whereNotNull('submitted_at')->whereNotNull('mentor_response')->count();

        // Finance extras
        $pendingWithdrawals = Schema::hasTable('withdrawal_requests')
            ? WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->count()
            : 0;
        $pendingWithdrawalAmount = Schema::hasTable('withdrawal_requests')
            ? (float) WithdrawalRequest::where('status', WithdrawalRequest::STATUS_PENDING)->sum('amount')
            : 0;
        $walletCreditsMonth = Schema::hasTable('wallet_transactions')
            ? (float) WalletTransaction::where('type', 'credit')
                ->where('status', 'completed')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('amount')
            : 0;
        $activeOffers = Schema::hasTable('offers')
            ? Offer::where('is_active', true)->count()
            : 0;
        $activeJourneys = EducationStream::query()
            ->where('is_template', false)
            ->whereNotNull('mentee_id')
            ->where('is_active', true)
            ->count();

        // ── Session chart (last 6 months) ─────────────────────
        $sessionChart = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);

            return [
                'month' => $month->format('M'),
                'label' => $month->format('M Y'),
                'sessions' => ConsultationSession::whereYear('scheduled_at', $month->year)
                    ->whereMonth('scheduled_at', $month->month)
                    ->count(),
                'revenue' => (int) (ConsultationSession::where('status', 'completed')
                    ->whereYear('ended_at', $month->year)
                    ->whereMonth('ended_at', $month->month)
                    ->sum('amount') ?? 0),
            ];
        })->values();

        // ── Daily sessions (last 14 days) ─────────────────────
        $dailySessions = collect(range(13, 0))->map(function ($i) {
            $day = now()->subDays($i);

            return [
                'label' => $day->format('d M'),
                'count' => ConsultationSession::whereDate('scheduled_at', $day->toDateString())->count(),
            ];
        })->values();

        // ── Status breakdown ──────────────────────────────────
        $statusBreakdown = [
            'completed' => $completedSessions,
            'upcoming' => ConsultationSession::where('status', 'upcoming')->count(),
            'cancelled' => $cancelledSessions,
            'confirmed' => ConsultationSession::where('status', 'confirmed')->count(),
            'pending' => ConsultationSession::where('status', 'pending')->count(),
        ];

        // ── Recent sessions ───────────────────────────────────
        $recentSessions = ConsultationSession::with(['mentor', 'mentee'])
            ->latest('scheduled_at')
            ->limit(8)
            ->get();

        // ── Recent activity logs ──────────────────────────────
        $recentLogs = ActivityLog::latest('logged_at')->limit(6)->get();

        // ── Enrollment stats by stream ────────────────────────
        $enrollmentStats = collect();
        if (Schema::hasTable('mentee_enrollments')) {
            $enrollmentStats = MenteeEnrollment::query()
                ->select('stream_id', DB::raw('count(*) as count'))
                ->where('status', 'active')
                ->groupBy('stream_id')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(function ($e) {
                    $stream = EducationStream::find($e->stream_id);

                    return (object) [
                        'stream_name' => $stream->name ?? 'Unknown',
                        'count' => (int) $e->count,
                    ];
                });
        }

        $stats = [
            'totalMentees' => $totalMentees,
            'activeMentees' => $activeMentees,
            'menteesGrowth' => $this->growthPct($menteesThisMonth, $menteesPrevMonth),
            'totalMentors' => $totalMentors,
            'approvedMentors' => $approvedMentors,
            'pendingMentors' => $pendingMentors,
            'rejectedMentors' => $rejectedMentors,
            'suspendedMentors' => $suspendedMentors,
            'mentorsGrowth' => $this->growthPct($mentorsThisMonth, $mentorsPrevMonth),
            'totalExperts' => $totalExperts,
            'totalSessions' => $totalSessions,
            'completedSessions' => $completedSessions,
            'sessionsToday' => $sessionsToday,
            'ongoingSessions' => $ongoingSessions,
            'upcomingSessions' => $upcomingSessions,
            'cancelledSessions' => $cancelledSessions,
            'sessionsGrowth' => $this->growthPct($sessionsThisMonth, $sessionsPrevMonth),
            'monthRevenue' => $monthRevenue,
            'prevMonthRevenue' => $prevMonthRevenue,
            'revenueGrowth' => $this->growthPct($monthRevenue, $prevMonthRevenue),
            'lifetimeRevenue' => $lifetimeRevenue,
            'avgRating' => $avgRating,
            'pendingChanges' => $pendingChanges,
            'totalCheckins' => $totalCheckins,
            'answeredCheckins' => $answeredCheckins,
            'pendingWithdrawals' => $pendingWithdrawals,
            'pendingWithdrawalAmount' => $pendingWithdrawalAmount,
            'walletCreditsMonth' => $walletCreditsMonth,
            'activeOffers' => $activeOffers,
            'activeJourneys' => $activeJourneys,
            'completionRate' => $totalSessions > 0 ? (int) round($completedSessions / $totalSessions * 100) : 0,
            'activeMenteePct' => $totalMentees > 0 ? (int) round($activeMentees / $totalMentees * 100) : 0,
            'approvedMentorPct' => $totalMentors > 0 ? (int) round($approvedMentors / $totalMentors * 100) : 0,
        ];

        return view('admin.dashboard', compact(
            'stats',
            'sessionChart',
            'dailySessions',
            'statusBreakdown',
            'recentSessions',
            'recentLogs',
            'enrollmentStats'
        ));
    }

    private function growthPct(float|int $current, float|int $previous): ?int
    {
        if ((float) $previous <= 0) {
            return $current > 0 ? 100 : null;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
