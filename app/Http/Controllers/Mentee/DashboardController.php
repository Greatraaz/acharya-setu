<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Channel;
use App\Models\ConsultationSession;
use App\Models\JobListing;
use App\Models\MenteeEnrollment;
use App\Models\MentorRequest;
use App\Models\Message;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $mentee = auth()->user();
        $menteeId = $mentee->id;
        $now = now();

        $upcomingSessions = ConsultationSession::where('mentee_id', $menteeId)
            ->with('mentor')
            ->where('status', 'upcoming')
            ->where('scheduled_at', '>', $now)
            ->orderBy('scheduled_at')
            ->limit(3)
            ->get();

        $upcomingCount = ConsultationSession::where('mentee_id', $menteeId)
            ->where('status', 'upcoming')
            ->where('scheduled_at', '>', $now)
            ->count();

        $sessionsNext24h = ConsultationSession::where('mentee_id', $menteeId)
            ->with('mentor')
            ->where('status', 'upcoming')
            ->whereBetween('scheduled_at', [$now, $now->copy()->addDay()])
            ->orderBy('scheduled_at')
            ->get();

        $enrollment = MenteeEnrollment::where('mentee_id', $menteeId)
            ->with(['stream', 'mentor'])
            ->where('status', 'active')
            ->first();

        $weekTasks = $this->loadWeekTasks($mentee, $enrollment);

        $recommendedMentors = User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->where('is_active', true)
            ->orderByDesc('rating')
            ->limit(3)
            ->get();

        $thisMonthStart = $now->copy()->startOfMonth();

        $stats = [
            'sessions'            => ConsultationSession::where('mentee_id', $menteeId)->where('status', 'completed')->count(),
            'this_month_sessions' => ConsultationSession::where('mentee_id', $menteeId)
                ->where('status', 'completed')
                ->where('updated_at', '>=', $thisMonthStart)
                ->count(),
            'minutes'             => ConsultationSession::where('mentee_id', $menteeId)->where('status', 'completed')->sum('duration_minutes'),
            'progress'            => $enrollment ? (int) (($enrollment->current_month / 6) * 100) : 0,
            'balance'             => (float) ($mentee->wallet_balance ?? 0),
        ];

        $canViewProgress = $mentee->canAccessProgressReport();
        $planAllowance = $mentee->planSessionAllowance();
        $assignedMentor = $mentee->assignedMentor;

        $pendingMentorRequests = MentorRequest::where('mentee_id', $menteeId)
            ->where('status', MentorRequest::STATUS_PENDING)
            ->with('mentor')
            ->latest()
            ->get();

        if (! $canViewProgress) {
            $weekTasks = collect($weekTasks)->map(function ($task) {
                $task->is_completed = false;

                return $task;
            })->all();
            $stats['progress'] = null;
        }

        $pendingAssessments = $this->loadPendingAssessments($menteeId);
        $availableQuizzes = $this->loadAvailableQuizzes($menteeId);
        $recentJobs = $this->loadRecentJobs();
        $communityData = $this->loadCommunityData($mentee);
        $agendaItems = $this->buildAgendaItems($sessionsNext24h, $pendingMentorRequests, $pendingAssessments, $availableQuizzes);

        return view('frontend.mentee.dashboard', compact(
            'upcomingSessions',
            'upcomingCount',
            'enrollment',
            'weekTasks',
            'recommendedMentors',
            'stats',
            'canViewProgress',
            'planAllowance',
            'assignedMentor',
            'pendingMentorRequests',
            'sessionsNext24h',
            'pendingAssessments',
            'availableQuizzes',
            'recentJobs',
            'agendaItems',
        ) + $communityData);
    }

    private function loadWeekTasks(User $mentee, ?MenteeEnrollment $enrollment): array
    {
        if (! $enrollment) {
            return [];
        }

        try {
            $week = \DB::table('curriculum_weeks')
                ->join('curriculum_months', 'curriculum_weeks.month_id', '=', 'curriculum_months.id')
                ->where('curriculum_months.stream_id', $enrollment->stream_id)
                ->where('curriculum_months.month_number', $enrollment->current_month)
                ->where('curriculum_weeks.week_number', $enrollment->current_week)
                ->select('curriculum_weeks.id')
                ->first();

            if (! $week) {
                return [];
            }

            $completedTaskIds = \DB::table('student_curriculum_progress')
                ->where('user_id', $mentee->id)
                ->where('item_type', 'task')
                ->where('is_completed', true)
                ->pluck('item_id')
                ->toArray();

            return \DB::table('curriculum_tasks')
                ->where('week_id', $week->id)
                ->orderBy('order_index')
                ->get()
                ->map(fn ($t) => (object) [
                    'title'        => $t->title,
                    'is_completed' => in_array($t->id, $completedTaskIds),
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function loadPendingAssessments(int $menteeId): Collection
    {
        if (! Schema::hasTable('assessments')) {
            return collect();
        }

        try {
            $query = Assessment::query()
                ->where('status', 'active')
                ->has('questions')
                ->latest();

            if (Schema::hasTable('assessment_progress')) {
                $query->whereDoesntHave('progress', function ($p) use ($menteeId) {
                    $p->where('user_id', $menteeId)->whereNotNull('completed_at');
                });
            }

            return $query->limit(4)->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function loadAvailableQuizzes(int $menteeId): Collection
    {
        if (! Schema::hasTable('quizzes')) {
            return collect();
        }

        try {
            return Quiz::query()
                ->where('is_published', true)
                ->has('questions')
                ->whereDoesntHave('attempts', function ($a) use ($menteeId) {
                    $a->where('user_id', $menteeId)->whereNotNull('completed_at');
                })
                ->withCount('questions')
                ->latest()
                ->limit(4)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function loadRecentJobs(): Collection
    {
        if (! Schema::hasTable('job_listings')) {
            return collect();
        }

        try {
            return JobListing::query()
                ->active()
                ->latest('published_at')
                ->limit(4)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function loadCommunityData(User $mentee): array
    {
        $communityUnread = 0;
        $recentCommunityMessages = collect();

        try {
            $channels = Channel::visibleTo($mentee)
                ->whereHas('members', fn ($q) => $q->where('user_id', $mentee->id))
                ->get();

            $communityUnread = (int) $channels->sum(
                fn (Channel $channel) => $channel->unreadCountFor($mentee)
            );

            $channelIds = $channels->pluck('id');

            if ($channelIds->isNotEmpty()) {
                $recentCommunityMessages = Message::query()
                    ->whereIn('channel_id', $channelIds)
                    ->with(['user:id,name', 'channel:id,name,slug,icon'])
                    ->latest()
                    ->limit(4)
                    ->get();
            }
        } catch (\Throwable) {
            // Community is optional on dashboard.
        }

        return compact('communityUnread', 'recentCommunityMessages');
    }

    private function buildAgendaItems(
        Collection $sessionsNext24h,
        Collection $pendingMentorRequests,
        Collection $pendingAssessments,
        Collection $availableQuizzes
    ): Collection {
        $items = collect();

        foreach ($sessionsNext24h as $session) {
            $items->push([
                'type'    => 'session',
                'time'    => $session->scheduled_at,
                'label'   => $session->title ?: 'Session with '.$session->mentor?->name,
                'meta'    => $session->mentor?->name,
                'url'     => route('mentee.sessions.show', $session->id),
                'cta'     => $session->canJoinCall() ? 'Join' : 'View',
                'cta_url' => $session->canJoinCall()
                    ? route('sessions.call', $session->id)
                    : route('mentee.sessions.show', $session->id),
            ]);
        }

        foreach ($pendingMentorRequests->take(2) as $request) {
            $items->push([
                'type'    => 'request',
                'time'    => $request->created_at,
                'label'   => 'Mentor request: '.$request->mentor?->name,
                'meta'    => 'Awaiting mentor response',
                'url'     => route('mentee.mentor.change'),
                'cta'     => 'View',
                'cta_url' => route('mentee.mentor.change'),
            ]);
        }

        foreach ($pendingAssessments->take(2) as $assessment) {
            $items->push([
                'type'    => 'assessment',
                'time'    => $assessment->created_at ?? now(),
                'label'   => 'Take assessment: '.$assessment->title,
                'meta'    => 'Self-check',
                'url'     => route('mentee.assessments.show', $assessment->id),
                'cta'     => 'Start',
                'cta_url' => route('mentee.assessments.show', $assessment->id),
            ]);
        }

        foreach ($availableQuizzes->take(1) as $quiz) {
            $items->push([
                'type'    => 'quiz',
                'time'    => $quiz->created_at ?? now(),
                'label'   => 'Try quiz: '.$quiz->title,
                'meta'    => ($quiz->questions_count ?? 0).' questions',
                'url'     => route('mentee.quizzes.show', $quiz),
                'cta'     => 'Start',
                'cta_url' => route('mentee.quizzes.show', $quiz),
            ]);
        }

        return $items->sortBy('time')->values()->take(8);
    }
}
