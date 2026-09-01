@extends('frontend.layouts.app')
@section('title', 'My Dashboard — Vedrix')

@section('content')
@php
    $firstName = auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0];
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');
    $monthSessions = (int) ($stats['this_month_sessions'] ?? 0);
    $plan = $planAllowance ?? [];
    $hasPlan = filled($plan['plan_name'] ?? null);
@endphp
<div class="dash-layout mentee-dash">

    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">

        <div class="dash-header dash-header--actions flex-between mentee-dash__header">
            <div class="dash-header__main">
                <div class="dash-title">Good {{ $greeting }}, {{ $firstName }}! 👋</div>
                <div class="dash-subtitle">Here's what's happening with your learning journey.</div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-sm">🔍 Find a Mentor</a>
            </div>
        </div>

        <div class="stats-grid mentee-dash__stats">
            <div class="stat-card">
                <div class="stat-card-icon">📅</div>
                <div class="stat-card-label">Sessions Completed</div>
                <div class="stat-card-value">{{ $stats['sessions'] ?? 0 }}</div>
                <div class="stat-card-delta">
                    @if($monthSessions > 0)
                        +{{ $monthSessions }} this month
                    @else
                        No sessions this month yet
                    @endif
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">⏱️</div>
                <div class="stat-card-label">Total Hours</div>
                <div class="stat-card-value">{{ number_format(($stats['minutes'] ?? 0) / 60, 1) }}</div>
                <div class="stat-card-delta">hrs of mentoring</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">💰</div>
                <div class="stat-card-label">Wallet Balance</div>
                <div class="stat-card-value">₹{{ number_format($stats['balance'] ?? 0, 0) }}</div>
                <a href="{{ route('mentee.wallet') }}" class="mentee-dash__stat-link">Add Money →</a>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">📈</div>
                <div class="stat-card-label">Journey Progress</div>
                @if($canViewProgress ?? false)
                <div class="stat-card-value">{{ $stats['progress'] ?? 0 }}%</div>
                <div class="progress-bar mentee-dash__stat-progress">
                    <div class="progress-fill" style="width:{{ $stats['progress'] ?? 0 }}%"></div>
                </div>
                @else
                <div class="stat-card-value mentee-dash__stat-locked">Locked</div>
                <a href="{{ route('mentee.plans') }}" class="mentee-dash__stat-link">Upgrade plan →</a>
                @endif
            </div>
        </div>

        <div class="mentee-dash__panels mentee-dash__panels--2">
            <div class="card mentee-dash__plan-card">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">Your plan</h3>
                    <a href="{{ route('mentee.plans') }}" class="mentee-dash__card-link">
                        {{ $hasPlan ? 'Manage →' : 'View plans →' }}
                    </a>
                </div>
                @if($hasPlan)
                <div class="mentee-dash__plan-name">{{ $plan['plan_name'] }}</div>
                <div class="mentee-dash__plan-grid">
                    <div class="mentee-dash__plan-stat">
                        <span class="mentee-dash__plan-label">Sessions used</span>
                        <span class="mentee-dash__plan-value">{{ $plan['used'] ?? 0 }}</span>
                    </div>
                    <div class="mentee-dash__plan-stat">
                        <span class="mentee-dash__plan-label">Remaining</span>
                        <span class="mentee-dash__plan-value">
                            @if(!empty($plan['unlimited']))
                                Unlimited
                            @elseif(isset($plan['remaining']))
                                {{ $plan['remaining'] }}
                            @else
                                —
                            @endif
                        </span>
                    </div>
                    @if(!empty($plan['limit']) && (int) $plan['limit'] > 0)
                    <div class="mentee-dash__plan-stat">
                        <span class="mentee-dash__plan-label">Monthly limit</span>
                        <span class="mentee-dash__plan-value">{{ $plan['limit'] }}</span>
                    </div>
                    @endif
                </div>
                @if(($plan['covered'] ?? false) && !empty($plan['unlimited']))
                <p class="mentee-dash__plan-hint">Included sessions are unlimited on your current plan.</p>
                @elseif(($plan['covered'] ?? false) && ($plan['remaining'] ?? 0) > 0)
                <p class="mentee-dash__plan-hint">Book sessions without extra cost while allowance lasts.</p>
                @elseif(($plan['covered'] ?? false))
                <p class="mentee-dash__plan-hint">Monthly allowance used — wallet or pay-per-session applies.</p>
                @endif
                @else
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">📦</div>
                    <p>No active subscription</p>
                    <a href="{{ route('mentee.plans') }}" class="btn btn-primary btn-sm">Choose a plan</a>
                </div>
                @endif
            </div>

            <div class="card">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">Today's focus</h3>
                    @if(($upcomingCount ?? 0) > 0)
                    <a href="{{ route('mentee.sessions') }}" class="mentee-dash__card-link">{{ $upcomingCount }} upcoming →</a>
                    @endif
                </div>
                @if(($agendaItems ?? collect())->isNotEmpty())
                <ul class="mentee-dash__agenda">
                    @foreach($agendaItems as $item)
                    <li class="mentee-dash__agenda-item mentee-dash__agenda-item--{{ $item['type'] }}">
                        <div class="mentee-dash__agenda-main">
                            <span class="mentee-dash__agenda-type">
                                @if($item['type'] === 'session') 🎥
                                @elseif($item['type'] === 'request') 📨
                                @elseif($item['type'] === 'assessment') 📝
                                @elseif($item['type'] === 'quiz') 🧠
                                @else 📌
                                @endif
                            </span>
                            <div class="mentee-dash__agenda-text">
                                <a href="{{ $item['url'] }}" class="mentee-dash__agenda-label">{{ $item['label'] }}</a>
                                <span class="mentee-dash__agenda-meta">{{ $item['meta'] }}</span>
                            </div>
                        </div>
                        @if(!empty($item['cta_url']))
                        <a href="{{ $item['cta_url'] }}" class="btn {{ $item['type'] === 'session' && ($item['cta'] ?? '') === 'Join' ? 'btn-primary' : 'btn-outline' }} btn-sm">{{ $item['cta'] }}</a>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">✅</div>
                    <p>You're all caught up for now.</p>
                    <a href="{{ route('mentors.search') }}" class="btn btn-outline btn-sm">Book a session</a>
                </div>
                @endif
            </div>
        </div>

        <div class="card mentee-dash__mentor-card">
            <div class="mentee-dash__card-head">
                <h3 class="mentee-dash__card-title">My Mentor</h3>
                <a href="{{ route('mentee.mentor.change') }}" class="btn btn-outline btn-sm">
                    {{ ($assignedMentor ?? null) ? 'Change mentor' : 'Choose mentor' }}
                </a>
            </div>

            @if($assignedMentor ?? null)
            <div class="mentee-dash__mentor-row">
                <a href="{{ $assignedMentor->profile_url }}" class="mentee-dash__mentor-profile">
                    <div class="mentor-avatar-lg mentee-dash__mentor-avatar">
                        @if($assignedMentor->avatar_url)
                            <img src="{{ $assignedMentor->avatar_url }}" alt="">
                        @else
                            {{ strtoupper(substr($assignedMentor->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="mentee-dash__mentor-info">
                        <div class="mentee-dash__mentor-name">{{ $assignedMentor->name }}</div>
                        <div class="mentee-dash__mentor-role">
                            {{ $assignedMentor->designation }}{{ $assignedMentor->company ? ' · '.$assignedMentor->company : '' }}
                        </div>
                        <div class="mentee-dash__mentor-meta">
                            ⭐ {{ number_format((float) ($assignedMentor->rating ?? 0), 1) }}
                            · ₹{{ $assignedMentor->rate_per_minute }}/min
                        </div>
                    </div>
                </a>
                <div class="mentee-dash__mentor-actions">
                    <a href="{{ $assignedMentor->profile_url }}" class="btn btn-primary btn-sm">Book session</a>
                    <a href="{{ route('mentee.mentor.change') }}" class="btn btn-ghost btn-sm">Change</a>
                </div>
            </div>
            @else
            <div class="mentee-dash__empty">
                <div class="mentee-dash__empty-icon">🎓</div>
                <p>You don't have an assigned mentor yet</p>
                <a href="{{ route('mentee.mentor.change') }}" class="btn btn-primary btn-sm">Find & request a mentor</a>
            </div>
            @endif

            @if(($pendingMentorRequests ?? collect())->isNotEmpty())
            <div class="mentee-dash__pending">
                <div class="mentee-dash__pending-label">Pending requests</div>
                @foreach($pendingMentorRequests as $req)
                <div class="mentee-dash__pending-row">
                    <div class="mentee-dash__pending-main">
                        <strong>{{ $req->mentor?->name }}</strong>
                        <span> · {{ $req->created_at?->diffForHumans() }}</span>
                    </div>
                    <span class="badge badge-muted">Pending</span>
                    <form method="POST" action="{{ route('mentee.mentor-requests.destroy', $req->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-ghost btn-sm">Cancel</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="mentee-dash__panels">
            <div class="card mentee-dash__panel">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">Upcoming Sessions</h3>
                    <a href="{{ route('mentee.sessions') }}" class="mentee-dash__card-link">View all →</a>
                </div>

                @forelse($upcomingSessions ?? [] as $session)
                <div class="session-card mentee-dash__session-card">
                    <div class="session-card-icon">🎥</div>
                    <div class="mentee-dash__session-main">
                        <div class="mentee-dash__session-title">{{ $session->title }}</div>
                        <div class="mentee-dash__session-meta">with {{ $session->mentor->name }}</div>
                        <div class="mentee-dash__session-meta">📅 {{ $session->scheduled_at->format('D, d M Y · g:i A') }}</div>
                    </div>
                    <div class="mentee-dash__session-actions">
                        <span class="session-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                        @if($session->canJoinCall())
                        <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary btn-sm">Join</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">📅</div>
                    <p>No upcoming sessions yet</p>
                    <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-sm">Book Now</a>
                </div>
                @endforelse
            </div>

            <div class="card mentee-dash__panel">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">6-Month Journey</h3>
                    <a href="{{ route('mentee.journey.index') }}" class="mentee-dash__card-link">Continue →</a>
                </div>

                @if($enrollment ?? false)
                <div class="mentee-dash__journey-head">
                    <div class="mentee-dash__journey-stream">{{ $enrollment->stream->name ?? 'Engineering' }}</div>
                    <div class="mentee-dash__journey-week">Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }}</div>
                    @if($canViewProgress ?? false)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:{{ ($enrollment->current_month/6)*100 }}%"></div>
                    </div>
                    @else
                    <div class="mentee-dash__journey-upgrade">
                        Task list shown without scores.
                        <a href="{{ route('mentee.plans') }}">Upgrade for progress report →</a>
                    </div>
                    @endif
                </div>
                @forelse($weekTasks ?? [] as $task)
                <div class="mentee-dash__task-row">
                    @if($canViewProgress ?? false)
                    <span class="mentee-dash__task-icon">{{ $task->is_completed ? '✅' : '⬜' }}</span>
                    <span class="mentee-dash__task-title {{ $task->is_completed ? 'is-done' : '' }}">{{ $task->title }}</span>
                    @else
                    <span class="mentee-dash__task-icon">⬜</span>
                    <span class="mentee-dash__task-title">{{ $task->title }}</span>
                    @endif
                </div>
                @empty
                <div class="mentee-dash__task-empty">No tasks for this week yet.</div>
                @endforelse
                @else
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">🗺️</div>
                    <p>You haven't enrolled in a journey yet</p>
                    <a href="{{ route('mentee.journey.index') }}" class="btn btn-primary btn-sm">Start Journey</a>
                </div>
                @endif
            </div>
        </div>

        <div class="mentee-dash__panels mentee-dash__panels--2">
            <div class="card mentee-dash__panel">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">Assessments</h3>
                    <a href="{{ route('mentee.assessments.index') }}" class="mentee-dash__card-link">View all →</a>
                </div>
                @forelse($pendingAssessments ?? [] as $assessment)
                <div class="mentee-dash__activity-row">
                    <div class="mentee-dash__activity-main">
                        <div class="mentee-dash__activity-title">{{ $assessment->title }}</div>
                        <div class="mentee-dash__activity-meta">Self-assessment · not completed</div>
                    </div>
                    <a href="{{ route('mentee.assessments.show', $assessment->id) }}" class="btn btn-primary btn-sm">Start</a>
                </div>
                @empty
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">📝</div>
                    <p>No pending assessments.</p>
                    <a href="{{ route('mentee.assessments.index') }}" class="btn btn-outline btn-sm">Browse assessments</a>
                </div>
                @endforelse
            </div>

            <div class="card mentee-dash__panel">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">Quizzes</h3>
                    <a href="{{ route('mentee.quizzes.index') }}" class="mentee-dash__card-link">View all →</a>
                </div>
                @forelse($availableQuizzes ?? [] as $quiz)
                <div class="mentee-dash__activity-row">
                    <div class="mentee-dash__activity-main">
                        <div class="mentee-dash__activity-title">{{ $quiz->title }}</div>
                        <div class="mentee-dash__activity-meta">{{ $quiz->questions_count ?? 0 }} questions</div>
                    </div>
                    <a href="{{ route('mentee.quizzes.show', $quiz) }}" class="btn btn-outline btn-sm">Try</a>
                </div>
                @empty
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">🧠</div>
                    <p>No new quizzes right now.</p>
                    <a href="{{ route('mentee.quizzes.index') }}" class="btn btn-outline btn-sm">Browse quizzes</a>
                </div>
                @endforelse
            </div>
        </div>

        <div class="mentee-dash__panels mentee-dash__panels--2">
            <div class="card mentee-dash__panel">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">Community</h3>
                    <a href="{{ route('mentee.community.index') }}" class="mentee-dash__card-link">
                        @if(($communityUnread ?? 0) > 0)
                            {{ $communityUnread }} unread →
                        @else
                            Open community →
                        @endif
                    </a>
                </div>
                @forelse($recentCommunityMessages ?? [] as $message)
                <a href="{{ route('mentee.community.show', $message->channel?->slug) }}" class="mentee-dash__community-row">
                    <span class="mentee-dash__community-icon">{{ $message->channel?->icon ?? '💬' }}</span>
                    <div class="mentee-dash__community-main">
                        <span class="mentee-dash__community-channel">{{ $message->channel?->name }}</span>
                        <span class="mentee-dash__community-preview">{{ Str::limit($message->body ?: '📎 Attachment', 60) }}</span>
                    </div>
                    <span class="mentee-dash__community-time">{{ $message->created_at?->diffForHumans(short: true) }}</span>
                </a>
                @empty
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">💬</div>
                    <p>No community activity yet.</p>
                    <a href="{{ route('mentee.community.index') }}" class="btn btn-outline btn-sm">Explore channels</a>
                </div>
                @endforelse
            </div>

            <div class="card mentee-dash__panel">
                <div class="mentee-dash__card-head">
                    <h3 class="mentee-dash__card-title">Job opportunities</h3>
                    <a href="{{ route('mentee.jobs') }}" class="mentee-dash__card-link">View all →</a>
                </div>
                @forelse($recentJobs ?? [] as $job)
                <a href="{{ route('mentee.jobs.show', $job->id) }}" class="mentee-dash__job-row">
                    <div class="mentee-dash__job-main">
                        <div class="mentee-dash__job-title">{{ $job->title }}</div>
                        <div class="mentee-dash__job-meta">
                            {{ $job->department ?: 'General' }}
                            @if($job->location) · {{ $job->location }} @endif
                            · {{ $job->job_type_label }}
                        </div>
                    </div>
                    <span class="mentee-dash__job-salary">{{ $job->salary_range }}</span>
                </a>
                @empty
                <div class="mentee-dash__empty mentee-dash__empty--compact">
                    <div class="mentee-dash__empty-icon">💼</div>
                    <p>No open roles at the moment.</p>
                    <a href="{{ route('mentee.jobs') }}" class="btn btn-outline btn-sm">Check jobs</a>
                </div>
                @endforelse
            </div>
        </div>

        <div class="card mentee-dash__recommended">
            <div class="mentee-dash__card-head">
                <h3 class="mentee-dash__card-title">Recommended Mentors for You</h3>
                <a href="{{ route('mentors.search') }}" class="mentee-dash__card-link">Browse all →</a>
            </div>
            <div class="mentee-dash__mentors-grid">
                @forelse($recommendedMentors ?? [] as $mentor)
                <div class="mentor-card mentee-dash__mentor-card-item" role="link" tabindex="0" onclick="window.location='{{ $mentor->profile_url }}'" onkeydown="if(event.key==='Enter')window.location='{{ $mentor->profile_url }}'">
                    <div class="mentor-card-head">
                        <div class="mentor-avatar-lg">{{ strtoupper(substr($mentor->name, 0, 1)) }}</div>
                        <div class="mentor-card-info">
                            <div class="mentor-card-name">{{ $mentor->name }}</div>
                            <div class="mentor-card-role">{{ $mentor->designation }}</div>
                        </div>
                    </div>
                    <div class="mentor-card-meta">
                        <span class="mentor-rate">₹{{ $mentor->rate_per_minute }}/min</span>
                        <span class="mentor-rating">⭐ {{ $mentor->rating }}</span>
                    </div>
                </div>
                @empty
                @foreach([
                    ['R','Rohit S.','Senior PM · Google','₹12/min','4.9'],
                    ['P','Priya N.','SDE-2 · Microsoft','₹15/min','4.8'],
                    ['A','Ananya G.','Consultant · McKinsey','₹10/min','5.0'],
                ] as [$i,$n,$r,$rate,$rating])
                <div class="mentor-card mentee-dash__mentor-card-item">
                    <div class="mentor-card-head">
                        <div class="mentor-avatar-lg">{{ $i }}</div>
                        <div class="mentor-card-info">
                            <div class="mentor-card-name">{{ $n }}</div>
                            <div class="mentor-card-role">{{ $r }}</div>
                        </div>
                    </div>
                    <div class="mentor-card-meta">
                        <span class="mentor-rate">{{ $rate }}</span>
                        <span class="mentor-rating">⭐ {{ $rating }}</span>
                    </div>
                    <div class="mentor-card-actions">
                        <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-sm">Book</a>
                    </div>
                </div>
                @endforeach
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
