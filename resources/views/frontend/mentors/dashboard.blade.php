@extends('frontend.layouts.app')
@section('title', (auth()->user()?->isExpert() ? 'Expert' : 'Mentor').' Dashboard — Vedrix')

@section('content')
@include('frontend.partials.dash-premium-ui')
@php
    $user = auth()->user();
    $hostLabel = $user->hostLabel();
    $isExpert = $user->isExpert();
    $firstName = $user->first_name ?: explode(' ', $user->name)[0];
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $rating = (float) ($stats['avg_rating'] ?? $user->rating ?? 0);

    $growthBadge = function (?int $pct) {
        if ($pct === null) return ['—', 'growth-flat'];
        if ($pct > 0) return ['↑ '.$pct.'%', 'growth-up'];
        if ($pct < 0) return ['↓ '.abs($pct).'%', 'growth-down'];
        return ['0%', 'growth-flat'];
    };
    [$gSess, $gSessCls] = $growthBadge($stats['sessions_growth'] ?? null);
    [$gEarn, $gEarnCls] = $growthBadge($stats['earnings_growth'] ?? null);

    $checks = [
        ['Photo uploaded',  (bool) $user->avatar_url],
        ['Bio written',     mb_strlen($user->bio_text ?? '') >= 50],
        ['Expertise added', ! empty($user->expertise)],
        ['Designation set', filled($user->designation)],
        ['Rate configured', (float) ($user->rate_per_minute ?? 0) > 0],
        ['LinkedIn linked', filled($user->linkedin)],
        ['Availability set', (bool) ($availability['has_schedule'] ?? false)],
    ];
    $completedCount = collect($checks)->filter(fn ($c) => (bool) $c[1])->count();
    $totalChecks = count($checks);
    $pct = $totalChecks > 0 ? (int) round(($completedCount / $totalChecks) * 100) : 0;
@endphp

<div class="dash-layout mentor-dash">
    @include('frontend.mentors.partials.sidebar', ['pendingCount' => $stats['pending_sessions'] ?? 0])

    <div class="dash-content">
        <div class="dash-page" style="display:flex;flex-direction:column;gap:1.1rem;">

            <div class="learn-intro">
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start;justify-content:space-between;">
                    <div>
                        <span class="learn-eyebrow">Overview · {{ $hostLabel }} dashboard</span>
                        <h2>{{ $greeting }}, {{ $firstName }}</h2>
                        <p>Live snapshot of sessions, earnings, and {{ $isExpert ? 'client' : 'mentee' }} activity — built for a clear day ahead.</p>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                        <a href="{{ route('mentor.sessions') }}" class="learn-btn-ghost">Sessions</a>
                        <a href="{{ route('mentor.wallet') }}" class="learn-btn-ghost">Wallet</a>
                        <a href="{{ route('mentor.availability') }}" class="learn-btn-primary">Availability</a>
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:.45rem;margin-top:1rem;">
                    @if($user->mentor_status === 'approved' && $user->onboarding_completed)
                        <span class="growth-pill growth-up">✓ Active {{ $hostLabel }}</span>
                    @elseif($user->mentor_status === 'approved')
                        <span class="growth-pill growth-flat">Complete profile</span>
                    @elseif($user->mentor_status === 'pending')
                        <span class="growth-pill growth-flat">Pending approval</span>
                    @endif
                    @if($availability['is_live'] ?? false)
                        <span class="growth-pill growth-up">● Live for bookings</span>
                    @else
                        <span class="growth-pill growth-flat">Offline</span>
                    @endif
                    <span class="growth-pill growth-flat">⭐ {{ number_format($rating, 1) }}/5</span>
                </div>
            </div>

            @if($user->mentor_status === 'pending' && $user->onboarding_completed)
            <div class="alert-premium">
                <span>⏳</span>
                <div>
                    <strong>Profile under review</strong>
                    <p>Your {{ strtolower($hostLabel) }} profile is being reviewed. We’ll notify you within 24–48 hours.</p>
                </div>
            </div>
            @elseif($user->mentor_status === 'approved' && ! $user->onboarding_completed)
            <div class="alert-premium">
                <span>📝</span>
                <div>
                    <strong>Complete your profile</strong>
                    <p>Finish your profile to go live and take sessions — no further approval needed.</p>
                    <a href="{{ route('mentor.onboarding', ['step' => 1]) }}" class="learn-btn-primary" style="margin-top:.75rem;">Complete profile</a>
                </div>
            </div>
            @endif

            {{-- KPI row --}}
            <div class="dash-grid dash-grid-4">
                <div class="learn-stat fu">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ number_format($stats['total_sessions'] ?? 0) }}</div>
                            <div class="learn-stat__label">Total sessions</div>
                            <div style="margin-top:.45rem;font-size:11px;color:var(--text-3);">{{ $stats['completion_rate'] ?? 0 }}% completion · {{ $stats['sessions_today'] ?? 0 }} today</div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.45rem;">
                            <span class="learn-stat__icon">📅</span>
                            <span class="growth-pill {{ $gSessCls }}">{{ $gSess }}</span>
                        </div>
                    </div>
                    <div class="progress-track" style="margin-top:.75rem;"><span style="width:{{ min(100, $stats['completion_rate'] ?? 0) }}%"></span></div>
                </div>

                <div class="revenue-card fu">
                    <div style="position:relative;z-index:1;display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div style="font-family:var(--font-head);font-size:1.5rem;font-weight:800;">₹{{ number_format($stats['this_month_earnings'] ?? 0) }}</div>
                            <div style="font-size:.75rem;font-weight:600;opacity:.9;margin-top:.25rem;">Earnings this month</div>
                            <div style="font-size:11px;opacity:.75;margin-top:.35rem;">Lifetime ₹{{ number_format($stats['total_earnings'] ?? 0) }}</div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.45rem;">
                            <span style="width:2.25rem;height:2.25rem;border-radius:8px;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;">💰</span>
                            <span class="growth-pill" style="background:rgba(255,255,255,.2);color:#fff;">{{ $gEarn }}</span>
                        </div>
                    </div>
                </div>

                <div class="learn-stat fu">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">₹{{ number_format($stats['available'] ?? 0) }}</div>
                            <div class="learn-stat__label">Available to withdraw</div>
                            <div style="margin-top:.45rem;font-size:11px;color:var(--text-3);">Balance ₹{{ number_format($stats['balance'] ?? 0) }} · Hold ₹{{ number_format($stats['pending_hold'] ?? 0) }}</div>
                        </div>
                        <span class="learn-stat__icon">🏦</span>
                    </div>
                </div>

                <div class="learn-stat fu">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ $isExpert ? number_format($stats['pending_sessions'] ?? 0) : number_format($stats['active_mentees'] ?? 0) }}</div>
                            <div class="learn-stat__label">{{ $isExpert ? 'Upcoming sessions' : 'Active mentees' }}</div>
                            <div style="margin-top:.45rem;font-size:11px;color:var(--text-3);">
                                @if($isExpert)
                                    {{ $stats['pending_requests'] ?? 0 }} open actions
                                @else
                                    {{ $stats['pending_requests'] ?? 0 }} pending requests · {{ $stats['pending_sessions'] ?? 0 }} upcoming
                                @endif
                            </div>
                        </div>
                        <span class="learn-stat__icon">{{ $isExpert ? '📝' : '🎓' }}</span>
                    </div>
                </div>
            </div>

            {{-- Secondary metrics --}}
            <div class="dash-grid dash-grid-6">
                <div class="mini-metric">
                    <div class="mini-metric__value">{{ number_format($stats['sessions_today'] ?? 0) }}</div>
                    <div class="mini-metric__label">Today</div>
                </div>
                <div class="mini-metric">
                    <div class="mini-metric__value">{{ number_format($stats['this_month_sessions'] ?? 0) }}</div>
                    <div class="mini-metric__label">This month</div>
                </div>
                <div class="mini-metric">
                    <div class="mini-metric__value">{{ number_format($rating, 1) }}</div>
                    <div class="mini-metric__label">Avg rating</div>
                </div>
                <div class="mini-metric">
                    <div class="mini-metric__value">{{ number_format($assessmentStats['active'] ?? 0) }}</div>
                    <div class="mini-metric__label">Active assessments</div>
                </div>
                <div class="mini-metric">
                    <div class="mini-metric__value">{{ number_format($communityUnread ?? 0) }}</div>
                    <div class="mini-metric__label">Unread community</div>
                </div>
                <div class="mini-metric">
                    <div class="mini-metric__value">{{ number_format(($sessionsWithoutNotes ?? collect())->count()) }}</div>
                    <div class="mini-metric__label">Notes pending</div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="dash-grid dash-grid-3">
                <div class="learn-panel panel-pad" style="grid-column:1/-1;">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Sessions &amp; earnings trend</h3>
                            <p class="dash-section-sub">Last 6 months — bookings vs wallet credits</p>
                        </div>
                        <div style="display:flex;gap:.85rem;font-size:11px;font-weight:600;color:var(--text-3);">
                            <span>● Sessions</span>
                            <span style="opacity:.8;">● Earnings (₹)</span>
                        </div>
                    </div>
                    <div class="chart-wrap"><canvas id="hostChartTrend" height="110"></canvas></div>
                </div>
            </div>

            <div class="dash-grid dash-grid-3">
                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Daily session volume</h3>
                            <p class="dash-section-sub">Last 14 days</p>
                        </div>
                        <a href="{{ route('mentor.sessions') }}" class="dash-link">View all →</a>
                    </div>
                    <div class="chart-wrap" style="min-height:200px;"><canvas id="hostChartDaily"></canvas></div>
                </div>
                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Session status mix</h3>
                            <p class="dash-section-sub">All-time booking split</p>
                        </div>
                    </div>
                    <div class="chart-wrap" style="min-height:200px;"><canvas id="hostChartStatus"></canvas></div>
                </div>
            </div>

            {{-- Agenda + Wallet --}}
            <div class="dash-grid dash-grid-2">
                <div class="learn-panel">
                    <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                        <div>
                            <h3 class="dash-section-title">Today’s focus</h3>
                            <p class="dash-section-sub">Next 24 hours and open actions</p>
                        </div>
                    </div>
                    @forelse($agendaItems ?? [] as $item)
                    <div class="dash-row">
                        <span class="dash-avatar">
                            @if($item['type'] === 'session') 🎥
                            @elseif($item['type'] === 'request') 📨
                            @else 📝
                            @endif
                        </span>
                        <div style="flex:1;min-width:0;">
                            <a href="{{ $item['url'] }}" style="font-weight:700;font-size:.85rem;color:var(--text);text-decoration:none;">{{ $item['label'] }}</a>
                            <div style="font-size:11px;color:var(--text-3);">{{ $item['meta'] }}</div>
                        </div>
                        @if(!empty($item['cta_url']))
                        <a href="{{ $item['cta_url'] }}" class="{{ ($item['cta'] ?? '') === 'Join' ? 'learn-btn-primary' : 'learn-btn-ghost' }}" style="padding:.4rem .7rem;font-size:.72rem;">{{ $item['cta'] }}</a>
                        @endif
                    </div>
                    @empty
                    <div class="empty-block"><div class="icon">✅</div>You’re all caught up for now.</div>
                    @endforelse
                </div>

                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Wallet snapshot</h3>
                            <p class="dash-section-sub">Ready to withdraw or review earnings</p>
                        </div>
                        <a href="{{ route('mentor.wallet') }}" class="dash-link">Open wallet →</a>
                    </div>
                    <div class="dash-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem;">
                        <div class="mini-metric">
                            <div class="mini-metric__value">₹{{ number_format($stats['available'] ?? 0) }}</div>
                            <div class="mini-metric__label">Available</div>
                        </div>
                        <div class="mini-metric">
                            <div class="mini-metric__value">₹{{ number_format($stats['balance'] ?? 0) }}</div>
                            <div class="mini-metric__label">Balance</div>
                        </div>
                        <div class="mini-metric">
                            <div class="mini-metric__value">₹{{ number_format($stats['pending_hold'] ?? 0) }}</div>
                            <div class="mini-metric__label">On hold</div>
                        </div>
                    </div>
                    <div style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;">
                        <a href="{{ route('mentor.wallet') }}" class="learn-btn-primary">Withdraw</a>
                        <span style="font-size:12px;color:var(--text-3);">₹{{ number_format($stats['this_month_earnings'] ?? 0) }} earned this month</span>
                    </div>
                    <div style="margin-top:1.1rem;">
                        <h4 class="dash-section-title" style="font-size:.8rem;margin-bottom:.65rem;">Availability</h4>
                        <div style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;">
                            <span class="growth-pill {{ ($availability['is_live'] ?? false) ? 'growth-up' : 'growth-flat' }}">
                                {{ ($availability['is_live'] ?? false) ? '● Live' : 'Offline' }}
                            </span>
                            <span style="color:var(--text-2);font-size:.8rem;">
                                @if(!empty($availability['next_slot_date']))
                                    Next slot {{ \Carbon\Carbon::parse($availability['next_slot_date'])->format('D, d M') }} · {{ $availability['next_slot'] }}
                                @else
                                    No open slots in 2 weeks
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if(! $isExpert && ($pendingMentorRequests ?? collect())->isNotEmpty())
            <div class="learn-panel">
                <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                    <div>
                        <h3 class="dash-section-title">Mentee requests</h3>
                        <p class="dash-section-sub">People waiting for your response</p>
                    </div>
                    <a href="{{ route('mentor.requests') }}" class="dash-link">View all →</a>
                </div>
                @foreach($pendingMentorRequests as $req)
                <div class="dash-row">
                    <span class="dash-avatar">{{ strtoupper(substr($req->mentee?->name ?? 'M', 0, 2)) }}</span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-weight:700;font-size:.85rem;">{{ $req->mentee?->name }}</div>
                        <div style="font-size:11px;color:var(--text-3);">{{ $req->created_at?->diffForHumans() }}</div>
                    </div>
                    <form method="POST" action="{{ route('mentor.requests.accept', $req->id) }}" style="margin:0;">@csrf<button type="submit" class="learn-btn-primary" style="padding:.4rem .7rem;font-size:.72rem;">Accept</button></form>
                    <form method="POST" action="{{ route('mentor.requests.reject', $req->id) }}" style="margin:0;">@csrf<button type="submit" class="learn-btn-ghost" style="padding:.4rem .7rem;font-size:.72rem;">Decline</button></form>
                </div>
                @endforeach
            </div>
            @endif

            {{-- Recent sessions + reviews / progress --}}
            <div class="dash-grid dash-grid-3">
                <div class="learn-panel" style="grid-column: span 1 / span 1;">
                    <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                        <div>
                            <h3 class="dash-section-title">Recent sessions</h3>
                            <p class="dash-section-sub">Latest bookings on your calendar</p>
                        </div>
                        <a href="{{ route('mentor.sessions') }}" class="dash-link">All →</a>
                    </div>
                    @forelse($recentSessions ?? [] as $session)
                    <div class="dash-row">
                        <span class="dash-avatar">
                            @if($session->mentee?->avatar_url)
                                <img src="{{ $session->mentee->avatar_url }}" alt="">
                            @else
                                {{ strtoupper(substr($session->mentee?->name ?? 'M', 0, 2)) }}
                            @endif
                        </span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $session->mentee?->name ?? 'Mentee' }}</div>
                            <div style="font-size:11px;color:var(--text-3);">{{ optional($session->scheduled_at)->format('d M · g:i A') }}</div>
                        </div>
                        <span class="dash-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                    </div>
                    @empty
                    <div class="empty-block"><div class="icon">📅</div>No sessions yet.</div>
                    @endforelse
                </div>

                <div class="learn-panel">
                    <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                        <div>
                            <h3 class="dash-section-title">Upcoming</h3>
                            <p class="dash-section-sub">Ready to join soon</p>
                        </div>
                    </div>
                    @forelse($upcomingSessions ?? [] as $session)
                    <div class="dash-row">
                        <span class="dash-avatar">🎥</span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.85rem;">{{ $session->title ?: 'Session' }}</div>
                            <div style="font-size:11px;color:var(--text-3);">{{ $session->mentee?->name }} · {{ $session->scheduled_at->format('D, g:i A') }}</div>
                        </div>
                        @if($session->canJoinCall())
                        <a href="{{ route('sessions.call', $session->id) }}" class="learn-btn-primary" style="padding:.4rem .7rem;font-size:.72rem;">Join</a>
                        @endif
                    </div>
                    @empty
                    <div class="empty-block"><div class="icon">🗓️</div>No upcoming sessions.</div>
                    @endforelse
                </div>

                <div class="learn-panel">
                    <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                        <div>
                            <h3 class="dash-section-title">{{ $isExpert ? 'Recent reviews' : 'Mentee progress' }}</h3>
                            <p class="dash-section-sub">{{ $isExpert ? 'Public feedback from mentees' : 'Active curriculum enrollments' }}</p>
                        </div>
                        <a href="{{ $isExpert ? route('mentor.notes') : route('mentor.journey') }}" class="dash-link">Open →</a>
                    </div>
                    @if($isExpert)
                        @forelse($recentReviews ?? [] as $review)
                        <div class="dash-row">
                            <span class="dash-avatar">{{ strtoupper(substr($review->reviewer->name ?? '?', 0, 1)) }}</span>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;font-size:.85rem;">{{ str_repeat('★', (int) $review->overall_rating) }}</div>
                                <div style="font-size:11px;color:var(--text-3);">{{ Str::limit($review->review_text, 70) }}</div>
                            </div>
                        </div>
                        @empty
                        <div class="empty-block"><div class="icon">⭐</div>No reviews yet.</div>
                        @endforelse
                    @else
                        @forelse($menteeProgress ?? [] as $enrollment)
                        @php $progress = $enrollment->progress_data ?? ['percent' => 0]; @endphp
                        <div class="dash-row" style="flex-wrap:wrap;">
                            <div style="flex:1;min-width:140px;">
                                <a href="{{ route('mentor.journey.show', $enrollment->mentee_id) }}" style="font-weight:700;font-size:.85rem;color:var(--text);text-decoration:none;">{{ $enrollment->mentee?->name }}</a>
                                <div style="font-size:11px;color:var(--text-3);">{{ $enrollment->stream?->name ?? 'Track' }}</div>
                            </div>
                            <div style="width:110px;">
                                <div class="progress-track"><span style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></span></div>
                                <div style="font-size:10px;color:var(--text-3);margin-top:.25rem;text-align:right;">{{ (int) ($progress['percent'] ?? 0) }}%</div>
                            </div>
                        </div>
                        @empty
                        <div class="empty-block"><div class="icon">📈</div>No active enrollments.</div>
                        @endforelse
                    @endif
                </div>
            </div>

            {{-- Quick actions + community + assessments --}}
            <div class="dash-grid dash-grid-2">
                <div class="learn-panel panel-pad">
                    <h3 class="dash-section-title" style="margin-bottom:.85rem;">Quick actions</h3>
                    <div class="dash-grid" style="gap:.55rem;">
                        @foreach(array_filter([
                            [route('mentor.sessions'), 'Manage sessions', '📅', $stats['pending_sessions'] ?? null],
                            [route('mentor.wallet'), 'Withdraw earnings', '💰', null],
                            [route('mentor.availability'), 'Update availability', '🗓️', null],
                            ! $isExpert ? [route('mentor.requests'), 'Mentee requests', '📨', $stats['pending_requests'] ?? null] : null,
                            [route('mentor.assessments.index'), 'Assessments', '📝', $assessmentStats['active'] ?? null],
                            [route('mentor.community'), 'Community', '💬', $communityUnread ?? null],
                            [route('mentor.notes'), 'Session notes', '🗂️', ($sessionsWithoutNotes ?? collect())->count() ?: null],
                            [route('mentor.profile.edit'), 'Edit profile', '👤', null],
                        ]) as [$href, $label, $icon, $badge])
                        <a href="{{ $href }}" class="qa-link">
                            <span class="dash-avatar">{{ $icon }}</span>
                            <span style="flex:1;font-size:.8rem;font-weight:600;">{{ $label }}</span>
                            @if($badge)
                            <span class="growth-pill" style="background:var(--ln);color:#fff;">{{ $badge }}</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>

                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Community &amp; learning</h3>
                            <p class="dash-section-sub">Channels and assessment health</p>
                        </div>
                    </div>
                    <div class="dash-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem;margin-bottom:1rem;">
                        <div class="mini-metric">
                            <div class="mini-metric__value">{{ $assessmentStats['total'] ?? 0 }}</div>
                            <div class="mini-metric__label">Assessments</div>
                        </div>
                        <div class="mini-metric">
                            <div class="mini-metric__value">{{ $assessmentStats['completions'] ?? 0 }}</div>
                            <div class="mini-metric__label">Completions</div>
                        </div>
                        <div class="mini-metric">
                            <div class="mini-metric__value">{{ $communityUnread ?? 0 }}</div>
                            <div class="mini-metric__label">Unread</div>
                        </div>
                    </div>
                    @forelse(($recentCommunityMessages ?? collect())->take(3) as $message)
                    <a href="{{ route('mentor.community.show', $message->channel?->slug) }}" class="dash-row" style="text-decoration:none;color:inherit;padding-left:0;padding-right:0;">
                        <span class="dash-avatar">{{ $message->channel?->icon ?? '💬' }}</span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.8rem;">{{ $message->channel?->name }}</div>
                            <div style="font-size:11px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($message->body ?: 'Attachment', 55) }}</div>
                        </div>
                    </a>
                    @empty
                    <div class="empty-block" style="padding:1rem;"><div class="icon">💬</div>No community activity yet.</div>
                    @endforelse
                </div>
            </div>

            @if($pct < 100)
            <div class="learn-panel panel-pad">
                <div class="panel-head">
                    <div>
                        <h3 class="dash-section-title">Profile completeness</h3>
                        <p class="dash-section-sub">{{ $completedCount }} of {{ $totalChecks }} items complete</p>
                    </div>
                    <div style="display:flex;align-items:center;gap:.65rem;">
                        <span style="font-family:var(--font-head);font-weight:800;color:var(--ln-dark);">{{ $pct }}%</span>
                        <a href="{{ route('mentor.profile.edit') }}" class="learn-btn-primary">Complete profile</a>
                    </div>
                </div>
                <div class="progress-track" style="margin-bottom:1rem;"><span style="width:{{ $pct }}%"></span></div>
                <div class="dash-grid" style="grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.5rem;">
                    @foreach($checks as [$label, $isDone])
                    <div style="font-size:.8rem;color:{{ $isDone ? 'var(--text-2)' : 'var(--text-3)' }};">
                        {{ $isDone ? '✅' : '⬜' }} {{ $label }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    const amber = '#F59E0B';
    const amberDark = '#D97706';
    const ink = getComputedStyle(document.documentElement).getPropertyValue('--text').trim() || '#fafafa';
    const muted = getComputedStyle(document.documentElement).getPropertyValue('--text-3').trim() || '#71717a';
    const grid = 'rgba(148,163,184,.18)';

    const trendLabels = @json($sessionChart->pluck('month'));
    const trendSessions = @json($sessionChart->pluck('sessions'));
    const trendEarnings = @json($sessionChart->pluck('earnings'));
    const dailyLabels = @json($dailySessions->pluck('label'));
    const dailyCounts = @json($dailySessions->pluck('count'));
    const statusData = @json(array_values($statusBreakdown));
    const statusLabels = @json(array_map('ucfirst', array_keys($statusBreakdown)));

    Chart.defaults.font.family = 'DM Sans, ui-sans-serif, system-ui, sans-serif';
    Chart.defaults.color = muted;

    new Chart(document.getElementById('hostChartTrend'), {
        type: 'bar',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Sessions',
                    data: trendSessions,
                    backgroundColor: 'rgba(245,158,11,.85)',
                    borderRadius: 8,
                    borderSkipped: false,
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Earnings (₹)',
                    data: trendEarnings,
                    borderColor: ink,
                    borderWidth: 2.5,
                    tension: .35,
                    pointRadius: 3,
                    pointBackgroundColor: ink,
                    yAxisID: 'y1',
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8 } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { callback: (v) => '₹' + Number(v).toLocaleString('en-IN') }
                }
            }
        }
    });

    new Chart(document.getElementById('hostChartDaily'), {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                data: dailyCounts,
                borderColor: amber,
                backgroundColor: 'rgba(245,158,11,.15)',
                fill: true,
                tension: .4,
                borderWidth: 2.5,
                pointRadius: 2.5,
                pointBackgroundColor: amberDark,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 7, font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } }
            }
        }
    });

    new Chart(document.getElementById('hostChartStatus'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: [amber, '#38BDF8', '#F87171'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, usePointStyle: true, pointStyle: 'circle', padding: 14, font: { size: 11, weight: '600' } }
                }
            }
        }
    });
})();
</script>
@endpush
