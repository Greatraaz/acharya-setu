@extends('frontend.layouts.app')
@section('title', 'Mentor Dashboard — Vedrix')

@section('content')
@php
    $user = auth()->user();
    $rating = (float) ($user->rating ?? 0);
    $filledStars = (int) round(max(0, min(5, $rating)));
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = $user->first_name ?: explode(' ', $user->name)[0];

    $checks = [
        ['Photo uploaded',  (bool) $user->avatar_url],
        ['Bio written',     strlen(trim((string) ($user->bio ?? ''))) >= 50],
        ['Expertise added', ! empty($user->expertise)],
        ['Designation set', filled($user->designation)],
        ['Rate configured', (float) ($user->rate_per_minute ?? 0) > 0],
        ['LinkedIn linked', filled($user->linkedin)],
        ['Availability set', (bool) ($availability['has_schedule'] ?? false)],
    ];
    $completedCount = collect($checks)->filter(fn ($c) => (bool) $c[1])->count();
    $totalChecks    = count($checks);
    $pct            = $totalChecks > 0
        ? (int) max(0, min(100, round(($completedCount / $totalChecks) * 100)))
        : 0;

    $sessionTrend = ($stats['this_month_sessions'] ?? 0) - ($stats['last_month_sessions'] ?? 0);
    $earningsTrend = ($stats['this_month_earnings'] ?? 0) - ($stats['last_month_earnings'] ?? 0);
@endphp

<div class="dash-layout mentor-dash">
    @include('frontend.mentors.partials.sidebar', ['pendingCount' => $stats['pending_sessions'] ?? 0])

    <div class="dash-content">

        {{-- Header --}}
        <div class="dash-header dash-header--actions flex-between mentor-dash__header">
            <div class="dash-header__main">
                <div class="dash-title">{{ $greeting }}, {{ $firstName }} 👋</div>
                <div class="dash-subtitle mentor-dash__subtitle">
                    @if($user->mentor_status === 'approved')
                        <span class="badge badge-success">✓ Active Mentor</span>
                    @elseif($user->mentor_status === 'pending')
                        <span class="badge badge-muted">⏳ Pending Approval</span>
                    @else
                        <span class="badge badge-error">Profile needs attention</span>
                    @endif
                    @if(($availability['is_live'] ?? false))
                        <span class="badge badge-success mentor-dash__live-badge">● Live</span>
                    @else
                        <span class="badge badge-muted">Offline</span>
                    @endif
                </div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.profile.edit') }}" class="btn btn-primary btn-sm">✏️ Edit Profile</a>
            </div>
        </div>

        @if($user->mentor_status === 'pending')
        <div class="alert alert-info mentor-dash__alert">
            <span class="alert-icon">⏳</span>
            <div>
                <strong>Profile under review</strong>
                <p>Your mentor profile is being reviewed by our team. We'll notify you within 24–48 hours.</p>
            </div>
        </div>
        @endif

        {{-- Stats --}}
        <div class="stats-grid mentor-dash__stats">
            <div class="stat-card">
                <div class="stat-card-icon">📅</div>
                <div class="stat-card-label">Total Sessions</div>
                <div class="stat-card-value">{{ number_format($stats['total_sessions'] ?? 0) }}</div>
                <div class="stat-card-delta {{ $sessionTrend >= 0 ? '' : 'down' }}">
                    {{ $sessionTrend >= 0 ? '+' : '' }}{{ $sessionTrend }} vs last month
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">💰</div>
                <div class="stat-card-label">Total Earnings</div>
                <div class="stat-card-value">₹{{ number_format($stats['total_earnings'] ?? 0, 0) }}</div>
                <div class="stat-card-delta {{ $earningsTrend >= 0 ? '' : 'down' }}">
                    ₹{{ number_format($stats['this_month_earnings'] ?? 0, 0) }} this month
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">⭐</div>
                <div class="stat-card-label">Average Rating</div>
                <div class="stat-card-value">{{ number_format($rating, 1) }}</div>
                <div class="stars mentor-dash__stars" aria-label="{{ number_format($rating, 1) }} out of 5">
                    {{ str_repeat('★', $filledStars) }}{{ str_repeat('☆', 5 - $filledStars) }}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">🎓</div>
                <div class="stat-card-label">Active Mentees</div>
                <div class="stat-card-value">{{ $stats['active_mentees'] ?? 0 }}</div>
                <div class="stat-card-delta">{{ $stats['pending_sessions'] ?? 0 }} upcoming sessions</div>
            </div>
        </div>

        {{-- Wallet + Today's focus --}}
        <div class="mentor-dash__panels mentor-dash__panels--2">
            <div class="card mentor-dash__wallet-card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Wallet</h3>
                    <a href="{{ route('mentor.wallet') }}" class="mentor-dash__card-link">View earnings →</a>
                </div>
                <div class="mentor-dash__wallet-grid">
                    <div class="mentor-dash__wallet-stat">
                        <span class="mentor-dash__wallet-label">Available</span>
                        <span class="mentor-dash__wallet-value">₹{{ number_format($stats['available'] ?? 0, 0) }}</span>
                    </div>
                    <div class="mentor-dash__wallet-stat">
                        <span class="mentor-dash__wallet-label">Balance</span>
                        <span class="mentor-dash__wallet-value mentor-dash__wallet-value--muted">₹{{ number_format($stats['balance'] ?? 0, 0) }}</span>
                    </div>
                    <div class="mentor-dash__wallet-stat">
                        <span class="mentor-dash__wallet-label">On hold</span>
                        <span class="mentor-dash__wallet-value mentor-dash__wallet-value--muted">₹{{ number_format($stats['pending_hold'] ?? 0, 0) }}</span>
                    </div>
                </div>
                <div class="mentor-dash__wallet-actions">
                    <a href="{{ route('mentor.wallet') }}" class="btn btn-primary btn-sm">Withdraw</a>
                    <span class="mentor-dash__wallet-hint">₹{{ number_format($stats['this_month_earnings'] ?? 0, 0) }} earned this month</span>
                </div>
            </div>

            <div class="card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Today's focus</h3>
                </div>
                @if(($agendaItems ?? collect())->isNotEmpty())
                <ul class="mentor-dash__agenda">
                    @foreach($agendaItems as $item)
                    <li class="mentor-dash__agenda-item mentor-dash__agenda-item--{{ $item['type'] }}">
                        <div class="mentor-dash__agenda-main">
                            <span class="mentor-dash__agenda-type">
                                @if($item['type'] === 'session') 🎥
                                @elseif($item['type'] === 'request') 📨
                                @else 📝
                                @endif
                            </span>
                            <div class="mentor-dash__agenda-text">
                                <a href="{{ $item['url'] }}" class="mentor-dash__agenda-label">{{ $item['label'] }}</a>
                                <span class="mentor-dash__agenda-meta">{{ $item['meta'] }}</span>
                            </div>
                        </div>
                        @if(!empty($item['cta_url']))
                        <a href="{{ $item['cta_url'] }}" class="btn {{ $item['type'] === 'session' && ($item['cta'] ?? '') === 'Join' ? 'btn-primary' : 'btn-outline' }} btn-sm">{{ $item['cta'] }}</a>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="mentor-dash__empty">
                    <div class="mentor-dash__empty-icon">✅</div>
                    <p>You're all caught up for now.</p>
                </div>
                @endif
            </div>
        </div>

        @if(($pendingMentorRequests ?? collect())->isNotEmpty())
        <div class="card mentor-dash__section">
            <div class="mentor-dash__card-head">
                <h3 class="mentor-dash__card-title">Mentee requests</h3>
                <a href="{{ route('mentor.requests') }}" class="mentor-dash__card-link">View all →</a>
            </div>
            @foreach($pendingMentorRequests as $req)
            <div class="mentor-dash__request-row">
                <div class="mentor-dash__request-main">
                    <div class="mentor-dash__request-name">{{ $req->mentee?->name }}</div>
                    <div class="mentor-dash__request-meta">{{ $req->created_at?->diffForHumans() }}</div>
                </div>
                <div class="mentor-dash__request-actions">
                    <form method="POST" action="{{ route('mentor.requests.accept', $req->id) }}">@csrf<button type="submit" class="btn btn-success btn-sm">Accept</button></form>
                    <form method="POST" action="{{ route('mentor.requests.reject', $req->id) }}">@csrf<button type="submit" class="btn btn-ghost btn-sm">Decline</button></form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Availability + Mentee progress --}}
        <div class="mentor-dash__panels mentor-dash__panels--2">
            <div class="card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Availability</h3>
                    <a href="{{ route('mentor.availability') }}" class="mentor-dash__card-link">Manage →</a>
                </div>
                <div class="mentor-dash__availability">
                    <div class="mentor-dash__availability-status">
                        <span class="mentor-dash__availability-dot {{ ($availability['is_live'] ?? false) ? 'is-live' : '' }}"></span>
                        <span>{{ ($availability['is_live'] ?? false) ? 'You are live — mentees can book' : 'You are offline' }}</span>
                    </div>
                    @if(!empty($availability['next_slot_date']))
                    <p class="mentor-dash__availability-next">
                        Next open slot:
                        <strong>{{ \Carbon\Carbon::parse($availability['next_slot_date'])->format('D, d M') }} · {{ $availability['next_slot'] }}</strong>
                    </p>
                    @else
                    <p class="mentor-dash__availability-next">No open slots in the next 2 weeks. <a href="{{ route('mentor.availability') }}">Set your schedule</a>.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Mentee progress</h3>
                    <a href="{{ route('mentor.journey') }}" class="mentor-dash__card-link">Progress tracker →</a>
                </div>
                @forelse($menteeProgress ?? [] as $enrollment)
                @php $progress = $enrollment->progress_data ?? ['percent' => 0]; @endphp
                <div class="mentor-dash__progress-row">
                    <div class="mentor-dash__progress-main">
                        <a href="{{ route('mentor.journey.show', $enrollment->mentee_id) }}" class="mentor-dash__progress-name">{{ $enrollment->mentee?->name }}</a>
                        <span class="mentor-dash__progress-track">{{ $enrollment->stream?->name ?? 'Curriculum track' }}</span>
                    </div>
                    <div class="mentor-dash__progress-bar-wrap">
                        <div class="progress-bar mentor-dash__progress-bar">
                            <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? 0) }}%;"></div>
                        </div>
                        <span class="mentor-dash__progress-pct">{{ (int) ($progress['percent'] ?? 0) }}%</span>
                    </div>
                </div>
                @empty
                <div class="mentor-dash__empty">
                    <div class="mentor-dash__empty-icon">📈</div>
                    <p>No active enrollments yet.</p>
                    <a href="{{ route('mentor.curriculum.tracks') }}" class="btn btn-outline btn-sm">Create a track</a>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Sessions + Reviews --}}
        <div class="mentor-dash__panels mentor-dash__panels--2">
            <div class="card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Upcoming sessions</h3>
                    <a href="{{ route('mentor.sessions') }}" class="mentor-dash__card-link">View all →</a>
                </div>
                @forelse($upcomingSessions ?? [] as $session)
                <div class="session-card mentor-dash__session-card">
                    <div class="session-card-icon">🎥</div>
                    <div class="mentor-dash__session-main">
                        <div class="mentor-dash__session-title">{{ $session->title }}</div>
                        <div class="mentor-dash__session-meta">with {{ $session->mentee->name ?? 'Mentee' }}</div>
                        <div class="mentor-dash__session-meta">📅 {{ $session->scheduled_at->format('D, d M · g:i A') }}</div>
                    </div>
                    <div class="mentor-dash__session-actions">
                        <span class="session-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                        @if($session->canJoinCall())
                        <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary btn-sm">Join</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="mentor-dash__empty">
                    <div class="mentor-dash__empty-icon">📅</div>
                    <p>No upcoming sessions.</p>
                </div>
                @endforelse
            </div>

            <div class="card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Recent reviews</h3>
                </div>
                @forelse($recentReviews ?? [] as $review)
                <div class="testimonial-card mentor-dash__review">
                    <div class="stars mentor-dash__review-stars">{{ str_repeat('★', (int) $review->overall_rating) }}</div>
                    <p class="testimonial-text mentor-dash__review-text">{{ Str::limit($review->review_text, 100) }}</p>
                    <div class="testimonial-author">
                        <div class="author-avatar mentor-dash__review-avatar">{{ strtoupper(substr($review->reviewer->name ?? '?', 0, 1)) }}</div>
                        <div>
                            <div class="author-name">{{ $review->reviewer->name ?? 'Mentee' }}</div>
                            <div class="author-role">{{ $review->submitted_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="mentor-dash__empty">
                    <div class="mentor-dash__empty-icon">⭐</div>
                    <p>No reviews yet. Feedback from mentees will appear here.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Community + Assessments --}}
        <div class="mentor-dash__panels mentor-dash__panels--2">
            <div class="card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Community</h3>
                    <a href="{{ route('mentor.community') }}" class="mentor-dash__card-link">
                        @if(($communityUnread ?? 0) > 0)
                            {{ $communityUnread }} unread →
                        @else
                            Open community →
                        @endif
                    </a>
                </div>
                @forelse($recentCommunityMessages ?? [] as $message)
                <a href="{{ route('mentor.community.show', $message->channel?->slug) }}" class="mentor-dash__community-row">
                    <span class="mentor-dash__community-icon">{{ $message->channel?->icon ?? '💬' }}</span>
                    <div class="mentor-dash__community-main">
                        <span class="mentor-dash__community-channel">{{ $message->channel?->name }}</span>
                        <span class="mentor-dash__community-preview">{{ Str::limit($message->body ?: '📎 Attachment', 60) }}</span>
                    </div>
                    <span class="mentor-dash__community-time">{{ $message->created_at?->diffForHumans(short: true) }}</span>
                </a>
                @empty
                <div class="mentor-dash__empty">
                    <div class="mentor-dash__empty-icon">💬</div>
                    <p>No community activity yet.</p>
                    <a href="{{ route('mentor.community.create') }}" class="btn btn-outline btn-sm">Create a channel</a>
                </div>
                @endforelse
            </div>

            <div class="card">
                <div class="mentor-dash__card-head">
                    <h3 class="mentor-dash__card-title">Assessments</h3>
                    <a href="{{ route('mentor.assessments.index') }}" class="mentor-dash__card-link">Manage →</a>
                </div>
                <div class="mentor-dash__insight-grid">
                    <div class="mentor-dash__insight">
                        <span class="mentor-dash__insight-value">{{ $assessmentStats['total'] ?? 0 }}</span>
                        <span class="mentor-dash__insight-label">Total</span>
                    </div>
                    <div class="mentor-dash__insight">
                        <span class="mentor-dash__insight-value">{{ $assessmentStats['active'] ?? 0 }}</span>
                        <span class="mentor-dash__insight-label">Active</span>
                    </div>
                    <div class="mentor-dash__insight">
                        <span class="mentor-dash__insight-value">{{ $assessmentStats['completions'] ?? 0 }}</span>
                        <span class="mentor-dash__insight-label">Completions</span>
                    </div>
                </div>
                @if(($assessmentStats['without_questions'] ?? 0) > 0)
                <p class="mentor-dash__insight-hint">
                    {{ $assessmentStats['without_questions'] }} assessment{{ $assessmentStats['without_questions'] === 1 ? '' : 's' }} need questions.
                    <a href="{{ route('mentor.assessment-questions.create') }}">Add questions →</a>
                </p>
                @else
                <p class="mentor-dash__insight-hint">
                    <a href="{{ route('mentor.assessment-questions.index') }}">View questions →</a>
                </p>
                @endif
            </div>
        </div>

        @if(($sessionsWithoutNotes ?? collect())->isNotEmpty())
        <div class="card mentor-dash__section">
            <div class="mentor-dash__card-head">
                <h3 class="mentor-dash__card-title">Sessions needing notes</h3>
                <a href="{{ route('mentor.notes') }}" class="mentor-dash__card-link">All notes →</a>
            </div>
            @foreach($sessionsWithoutNotes as $session)
            <div class="mentor-dash__notes-row">
                <div class="mentor-dash__notes-main">
                    <div class="mentor-dash__notes-title">{{ $session->mentee?->name }} — {{ $session->title ?: 'Session' }}</div>
                    <div class="mentor-dash__notes-meta">{{ $session->scheduled_at?->format('d M Y') }}</div>
                </div>
                <a href="{{ route('mentor.sessions.show', $session->id) }}" class="btn btn-outline btn-sm">Add notes</a>
            </div>
            @endforeach
        </div>
        @endif

        @if($pct < 100)
        <div class="card mentor-dash__section">
            <div class="mentor-dash__card-head">
                <div>
                    <h3 class="mentor-dash__card-title">Profile completeness</h3>
                    <p class="mentor-dash__card-sub">{{ $completedCount }} of {{ $totalChecks }} items complete</p>
                </div>
                <div class="mentor-dash__profile-pct-wrap">
                    <span class="mentor-dash__profile-pct">{{ $pct }}%</span>
                    <a href="{{ route('mentor.profile.edit') }}" class="btn btn-primary btn-sm">Complete profile</a>
                </div>
            </div>
            <div class="progress-bar mentor-dash__profile-bar">
                <div class="progress-fill" style="width:{{ $pct }}%;"></div>
            </div>
            <div class="mentor-dash__checklist">
                @foreach($checks as [$label, $isDone])
                <div class="mentor-dash__check-item {{ $isDone ? 'is-done' : '' }}">
                    <span>{{ $isDone ? '✅' : '⬜' }}</span> {{ $label }}
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
