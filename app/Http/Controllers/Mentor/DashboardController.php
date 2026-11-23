<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Channel;
use App\Models\ConsultationSession;
use App\Models\MenteeEnrollment;
use App\Models\Message;
use App\Models\MentorRequest;
use App\Models\SessionReview;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Services\AssessmentService;
use App\Services\MentorAvailabilityService;
use App\Support\MentorMenteesQuery;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index(MentorAvailabilityService $availabilityService, AssessmentService $assessmentService)
    {
        $mentor = auth()->user();
        $mentorId = $mentor->id;
        $now = now();

        ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', ConsultationSession::STATUS_COMPLETED)
            ->where('payment_status', 'paid')
            ->where('amount', '>', 0)
            ->orderBy('id')
            ->each(fn (ConsultationSession $session) => $session->settleMentorPayout());

        $upcomingSessions = ConsultationSession::where('mentor_id', $mentorId)
            ->with('mentee')
            ->upcoming()
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $sessionsNext24h = ConsultationSession::where('mentor_id', $mentorId)
            ->with('mentee')
            ->upcoming()
            ->where('scheduled_at', '<=', $now->copy()->addDay())
            ->orderBy('scheduled_at')
            ->get();

        $pendingMentorRequests = MentorRequest::where('mentor_id', $mentorId)
            ->where('status', MentorRequest::STATUS_PENDING)
            ->with('mentee')
            ->latest()
            ->limit(5)
            ->get();

        $recentReviews = SessionReview::where('reviewee_id', $mentorId)
            ->where('reviewer_role', 'mentee')
            ->where('is_public', true)
            ->with('reviewer:id,name,avatar_url')
            ->latest('submitted_at')
            ->limit(3)
            ->get();

        $earningsQuery = WalletTransaction::where('user_id', $mentorId)
            ->whereIn('type', ['credit', 'transfer_in', 'refund'])
            ->where('status', 'completed');

        $pendingHold = (float) WithdrawalRequest::where('user_id', $mentorId)
            ->where('status', WithdrawalRequest::STATUS_PENDING)
            ->sum('amount');

        $walletBalance = (float) ($mentor->fresh()->wallet_balance ?? 0);
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $thisMonthSessions = ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', ConsultationSession::STATUS_COMPLETED)
            ->where('updated_at', '>=', $thisMonthStart)
            ->count();
        $lastMonthSessions = ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', ConsultationSession::STATUS_COMPLETED)
            ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        $thisMonthEarnings = (float) (clone $earningsQuery)
            ->where('created_at', '>=', $thisMonthStart)
            ->sum('amount');
        $lastMonthEarnings = (float) (clone $earningsQuery)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');
        $completedSessions = ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', ConsultationSession::STATUS_COMPLETED)
            ->count();
        $cancelledSessions = ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', ConsultationSession::STATUS_CANCELLED)
            ->count();
        $totalBookings = ConsultationSession::where('mentor_id', $mentorId)->count();

        $stats = [
            'total_sessions'      => (int) ($mentor->total_sessions ?? $completedSessions),
            'completed_sessions'  => $completedSessions,
            'cancelled_sessions'  => $cancelledSessions,
            'this_month_sessions' => $thisMonthSessions,
            'last_month_sessions' => $lastMonthSessions,
            'sessions_growth'     => $this->growthPct($thisMonthSessions, $lastMonthSessions),
            'total_earnings'      => (float) (clone $earningsQuery)->sum('amount'),
            'this_month_earnings' => $thisMonthEarnings,
            'last_month_earnings' => $lastMonthEarnings,
            'earnings_growth'     => $this->growthPct($thisMonthEarnings, $lastMonthEarnings),
            'active_mentees'      => MentorMenteesQuery::for($mentorId)->count(),
            'pending_sessions'    => ConsultationSession::where('mentor_id', $mentorId)
                ->upcoming()
                ->count(),
            'pending_requests'    => MentorRequest::where('mentor_id', $mentorId)
                ->where('status', MentorRequest::STATUS_PENDING)
                ->count(),
            'sessions_today'      => ConsultationSession::where('mentor_id', $mentorId)
                ->whereDate('scheduled_at', today())
                ->count(),
            'completion_rate'     => $totalBookings > 0 ? (int) round($completedSessions / $totalBookings * 100) : 0,
            'balance'             => $walletBalance,
            'pending_hold'        => $pendingHold,
            'available'           => max(0, $walletBalance - $pendingHold),
            'avg_rating'          => (float) ($mentor->rating ?? 0),
        ];

        $sessionChart = collect(range(5, 0))->map(function ($i) use ($mentorId, $earningsQuery) {
            $month = now()->subMonths($i);

            return [
                'month' => $month->format('M'),
                'sessions' => ConsultationSession::where('mentor_id', $mentorId)
                    ->whereYear('scheduled_at', $month->year)
                    ->whereMonth('scheduled_at', $month->month)
                    ->count(),
                'earnings' => (int) (clone $earningsQuery)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->sum('amount'),
            ];
        })->values();

        $dailySessions = collect(range(13, 0))->map(function ($i) use ($mentorId) {
            $day = now()->subDays($i);

            return [
                'label' => $day->format('d M'),
                'count' => ConsultationSession::where('mentor_id', $mentorId)
                    ->whereDate('scheduled_at', $day->toDateString())
                    ->count(),
            ];
        })->values();

        $statusBreakdown = [
            'completed' => $completedSessions,
            'upcoming' => ConsultationSession::where('mentor_id', $mentorId)
                ->where('status', ConsultationSession::STATUS_UPCOMING)
                ->count(),
            'cancelled' => $cancelledSessions,
        ];

        $recentSessions = ConsultationSession::where('mentor_id', $mentorId)
            ->with('mentee:id,name,avatar_url')
            ->latest('scheduled_at')
            ->limit(6)
            ->get();

        $sessionsWithoutNotes = ConsultationSession::where('mentor_id', $mentorId)
            ->where('status', ConsultationSession::STATUS_COMPLETED)
            ->whereDoesntHave('notes')
            ->with('mentee')
            ->latest('scheduled_at')
            ->limit(5)
            ->get();

        $menteeProgress = MenteeEnrollment::where('mentor_id', $mentorId)
            ->where('status', 'active')
            ->with(['mentee', 'stream'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(function (MenteeEnrollment $enrollment) {
                $enrollment->progress_data = $enrollment->progress;

                return $enrollment;
            });

        $communityChannels = Channel::visibleTo($mentor)
            ->whereHas('members', fn ($q) => $q->where('user_id', $mentorId))
            ->get();

        $communityUnread = (int) $communityChannels->sum(
            fn (Channel $channel) => $channel->unreadCountFor($mentor)
        );

        $channelIds = $communityChannels->pluck('id');

        $recentCommunityMessages = $channelIds->isEmpty()
            ? collect()
            : Message::query()
                ->whereIn('channel_id', $channelIds)
                ->with(['user:id,name', 'channel:id,name,slug,icon'])
                ->latest()
                ->limit(4)
                ->get();

        $assessmentStats = [
            'total'             => 0,
            'active'            => 0,
            'completions'       => 0,
            'without_questions' => 0,
        ];

        if ($assessmentService->tableExists()) {
            $assessments = Assessment::query()
                ->ownedByMentor($mentor)
                ->withCount('questions')
                ->withCount([
                    'progress as completion_count' => fn ($q) => $q->whereNotNull('completed_at'),
                ])
                ->get();

            $assessmentStats = [
                'total'             => $assessments->count(),
                'active'            => $assessments->filter(fn (Assessment $a) => $a->isActive())->count(),
                'completions'       => (int) $assessments->sum('completion_count'),
                'without_questions' => $assessments->where('questions_count', 0)->count(),
            ];
        }

        $availability = $this->resolveNextSlot($availabilityService, $mentor);

        $agendaItems = $this->buildAgendaItems($sessionsNext24h, $pendingMentorRequests, $sessionsWithoutNotes);

        return view('frontend.mentors.dashboard', compact(
            'upcomingSessions',
            'recentReviews',
            'stats',
            'pendingMentorRequests',
            'sessionsWithoutNotes',
            'menteeProgress',
            'communityUnread',
            'recentCommunityMessages',
            'assessmentStats',
            'availability',
            'agendaItems',
            'sessionsNext24h',
            'sessionChart',
            'dailySessions',
            'statusBreakdown',
            'recentSessions',
        ));
    }

    private function growthPct(float|int $current, float|int $previous): ?int
    {
        if ((float) $previous <= 0) {
            return $current > 0 ? 100 : null;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }

    private function resolveNextSlot(MentorAvailabilityService $availabilityService, $mentor): array
    {
        $isLive = $availabilityService->isAcceptingBookings($mentor);
        $nextSlot = null;
        $nextSlotDate = null;
        $hasSchedule = false;

        try {
            $overview = $availabilityService->weekOverview($mentor, Carbon::now('Asia/Kolkata'), 14);
            $hasSchedule = (bool) ($overview['has_schedule'] ?? false);

            foreach ($overview['days'] as $day) {
                if (! ($day['available'] ?? false) || ($day['slot_count'] ?? 0) < 1) {
                    continue;
                }

                $slots = $availabilityService->slotsForDate($mentor, $day['date']);
                if ($slots === []) {
                    continue;
                }

                $nextSlotDate = $day['date'];
                $nextSlot = $slots[0];
                break;
            }
        } catch (\Throwable) {
            // Availability is optional on dashboard.
        }

        return [
            'is_live'        => $isLive,
            'has_schedule'   => $hasSchedule,
            'next_slot'      => $nextSlot,
            'next_slot_date' => $nextSlotDate,
        ];
    }

    private function buildAgendaItems(
        Collection $sessionsNext24h,
        Collection $pendingMentorRequests,
        Collection $sessionsWithoutNotes
    ): Collection {
        $items = collect();

        foreach ($sessionsNext24h as $session) {
            $items->push([
                'type'  => 'session',
                'time'  => $session->scheduled_at,
                'label' => $session->title ?: 'Session with '.$session->mentee?->name,
                'meta'  => $session->mentee?->name,
                'url'   => route('mentor.sessions.show', $session->id),
                'cta'   => $session->canJoinCall() ? 'Join' : null,
                'cta_url' => $session->canJoinCall() ? route('sessions.call', $session->id) : null,
            ]);
        }

        foreach ($pendingMentorRequests->take(3) as $request) {
            $items->push([
                'type'  => 'request',
                'time'  => $request->created_at,
                'label' => 'Mentee request from '.$request->mentee?->name,
                'meta'  => 'Awaiting your response',
                'url'   => route('mentor.requests'),
                'cta'   => 'Review',
                'cta_url' => route('mentor.requests'),
            ]);
        }

        foreach ($sessionsWithoutNotes->take(3) as $session) {
            $items->push([
                'type'  => 'notes',
                'time'  => $session->scheduled_at ?? $session->updated_at,
                'label' => 'Add notes for '.$session->mentee?->name,
                'meta'  => $session->title ?: 'Completed session',
                'url'   => route('mentor.sessions.show', $session->id),
                'cta'   => 'Add notes',
                'cta_url' => route('mentor.sessions.show', $session->id),
            ]);
        }

        return $items->sortBy('time')->values()->take(8);
    }
}
