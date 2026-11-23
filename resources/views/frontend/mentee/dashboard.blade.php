@extends('frontend.layouts.app')
@section('title', 'My Dashboard — Vedrix')

@section('content')
@include('frontend.partials.dash-premium-ui')
@php
    $firstName = auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0];
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $plan = $planAllowance ?? [];
    $hasPlan = filled($plan['plan_name'] ?? null);

    $growthBadge = function (?int $pct) {
        if ($pct === null) return ['—', 'growth-flat'];
        if ($pct > 0) return ['↑ '.$pct.'%', 'growth-up'];
        if ($pct < 0) return ['↓ '.abs($pct).'%', 'growth-down'];
        return ['0%', 'growth-flat'];
    };
    [$gSess, $gSessCls] = $growthBadge($stats['sessions_growth'] ?? null);
@endphp

<div class="dash-layout mentee-dash">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page" style="display:flex;flex-direction:column;gap:1.1rem;">

            <div class="learn-intro">
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-start;justify-content:space-between;">
                    <div>
                        <span class="learn-eyebrow">Overview · Mentee dashboard</span>
                        <h2>{{ $greeting }}, {{ $firstName }}</h2>
                        <p>Your learning command centre — sessions, journey progress, wallet, and what’s next today.</p>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem;">
                        <a href="{{ route('mentee.sessions') }}" class="learn-btn-ghost">Sessions</a>
                        <a href="{{ route('mentee.wallet') }}" class="learn-btn-ghost">Wallet</a>
                        <a href="{{ route('mentors.search') }}" class="learn-btn-primary">Book session</a>
                    </div>
                </div>
            </div>

            {{-- Mentor strip --}}
            <div class="learn-panel panel-pad">
                <div class="panel-head">
                    <div>
                        <h3 class="dash-section-title">Your mentor</h3>
                        <p class="dash-section-sub">Primary guide for your journey and sessions</p>
                    </div>
                    <a href="{{ route('mentee.mentor.change') }}" class="learn-btn-ghost">{{ ($assignedMentor ?? null) ? 'Change' : 'Choose mentor' }}</a>
                </div>
                @if($assignedMentor ?? null)
                <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;">
                    <a href="{{ $assignedMentor->profile_url }}" style="display:flex;align-items:center;gap:.85rem;text-decoration:none;color:inherit;min-width:0;">
                        <span class="dash-avatar" style="width:3rem;height:3rem;border-radius:1rem;font-size:.9rem;">
                            @if($assignedMentor->avatar_url)
                                <img src="{{ $assignedMentor->avatar_url }}" alt="">
                            @else
                                {{ strtoupper(substr($assignedMentor->name, 0, 1)) }}
                            @endif
                        </span>
                        <div style="min-width:0;">
                            <div style="font-family:var(--font-head);font-weight:800;font-size:1rem;">{{ $assignedMentor->name }}</div>
                            <div style="font-size:.8rem;color:var(--text-2);">{{ $assignedMentor->designation }}{{ $assignedMentor->company ? ' · '.$assignedMentor->company : '' }}</div>
                            <div style="font-size:11px;color:var(--text-3);margin-top:.2rem;">⭐ {{ number_format((float) ($assignedMentor->rating ?? 0), 1) }} · ₹{{ $assignedMentor->rate_per_minute }}/min</div>
                        </div>
                    </a>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <a href="{{ $assignedMentor->profile_url }}" class="learn-btn-primary">Book session</a>
                        <a href="{{ $assignedMentor->profile_url }}" class="learn-btn-ghost">Profile</a>
                    </div>
                </div>
                @else
                <div class="empty-block" style="padding:1rem;">
                    <div class="icon">🎓</div>
                    <p>You don’t have an assigned mentor yet.</p>
                    <a href="{{ route('mentee.mentor.change') }}" class="learn-btn-primary" style="margin-top:.75rem;">Find &amp; request a mentor</a>
                </div>
                @endif

                @if(($pendingMentorRequests ?? collect())->isNotEmpty())
                <div style="margin-top:1rem;border-top:1px solid var(--ln-line);padding-top:.85rem;">
                    <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.5rem;">Pending requests</div>
                    @foreach($pendingMentorRequests as $req)
                    <div class="dash-row" style="padding-left:0;padding-right:0;">
                        <div style="flex:1;">
                            <strong style="font-size:.85rem;">{{ $req->mentor?->name }}</strong>
                            <span style="font-size:11px;color:var(--text-3);"> · {{ $req->created_at?->diffForHumans() }}</span>
                        </div>
                        <span class="dash-status pending">Pending</span>
                        <form method="POST" action="{{ route('mentee.mentor-requests.destroy', $req->id) }}" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="learn-btn-ghost" style="padding:.35rem .65rem;font-size:.72rem;">Cancel</button>
                        </form>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- KPIs --}}
            <div class="dash-grid dash-grid-4">
                <div class="learn-stat fu">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ number_format($stats['sessions'] ?? 0) }}</div>
                            <div class="learn-stat__label">Sessions completed</div>
                            <div style="margin-top:.45rem;font-size:11px;color:var(--text-3);">{{ $stats['this_month_sessions'] ?? 0 }} this month · {{ $stats['completion_rate'] ?? 0 }}% rate</div>
                        </div>
                        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.45rem;">
                            <span class="learn-stat__icon">📅</span>
                            <span class="growth-pill {{ $gSessCls }}">{{ $gSess }}</span>
                        </div>
                    </div>
                </div>

                <div class="learn-stat fu">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ number_format($stats['hours'] ?? (($stats['minutes'] ?? 0) / 60), 1) }}</div>
                            <div class="learn-stat__label">Mentoring hours</div>
                            <div style="margin-top:.45rem;font-size:11px;color:var(--text-3);">{{ number_format($stats['minutes'] ?? 0) }} minutes total</div>
                        </div>
                        <span class="learn-stat__icon">⏱️</span>
                    </div>
                </div>

                <div class="revenue-card fu">
                    <div style="position:relative;z-index:1;display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div style="font-family:var(--font-head);font-size:1.5rem;font-weight:800;">₹{{ number_format($stats['balance'] ?? 0) }}</div>
                            <div style="font-size:.75rem;font-weight:600;opacity:.9;margin-top:.25rem;">Wallet balance</div>
                            <div style="font-size:11px;opacity:.75;margin-top:.35rem;">{{ $stats['upcoming'] ?? 0 }} upcoming · {{ $stats['sessions_today'] ?? 0 }} today</div>
                        </div>
                        <a href="{{ route('mentee.wallet') }}" style="align-self:flex-start;background:rgba(255,255,255,.2);color:#fff;font-size:11px;font-weight:700;padding:.35rem .65rem;border-radius:999px;text-decoration:none;">Add money</a>
                    </div>
                </div>

                <div class="learn-stat fu">
                    <div style="display:flex;justify-content:space-between;gap:.75rem;">
                        <div>
                            @if($canViewProgress ?? false)
                            <div class="learn-stat__value">{{ $stats['progress'] ?? 0 }}%</div>
                            <div class="learn-stat__label">Journey progress</div>
                            <div class="progress-track" style="margin-top:.75rem;"><span style="width:{{ min(100, $stats['progress'] ?? 0) }}%"></span></div>
                            @else
                            <div class="learn-stat__value" style="font-size:1.15rem;">Locked</div>
                            <div class="learn-stat__label">Journey progress</div>
                            <a href="{{ route('mentee.plans') }}" class="dash-link" style="display:inline-block;margin-top:.5rem;">Upgrade plan →</a>
                            @endif
                        </div>
                        <span class="learn-stat__icon">📈</span>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="dash-grid dash-grid-3">
                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Session activity</h3>
                            <p class="dash-section-sub">Last 6 months — booked vs completed</p>
                        </div>
                        <div style="display:flex;gap:.75rem;font-size:11px;font-weight:600;color:var(--text-3);">
                            <span>● Booked</span>
                            <span>● Completed</span>
                        </div>
                    </div>
                    <div class="chart-wrap"><canvas id="menteeChartTrend" height="110"></canvas></div>
                </div>
                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Status mix</h3>
                            <p class="dash-section-sub">Your booking distribution</p>
                        </div>
                    </div>
                    <div class="chart-wrap" style="min-height:200px;"><canvas id="menteeChartStatus"></canvas></div>
                </div>
            </div>

            <div class="dash-grid dash-grid-3">
                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Daily volume</h3>
                            <p class="dash-section-sub">Last 14 days</p>
                        </div>
                        <a href="{{ route('mentee.sessions') }}" class="dash-link">Sessions →</a>
                    </div>
                    <div class="chart-wrap" style="min-height:190px;"><canvas id="menteeChartDaily"></canvas></div>
                </div>

                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">Your plan</h3>
                            <p class="dash-section-sub">Benefits and allowances</p>
                        </div>
                        <a href="{{ route('mentee.plans') }}" class="dash-link">{{ $hasPlan ? 'Manage →' : 'View plans →' }}</a>
                    </div>
                    @if($hasPlan)
                    <div style="font-family:var(--font-head);font-weight:800;font-size:1.1rem;margin-bottom:.65rem;">{{ $plan['plan_name'] }}</div>
                    @include('frontend.mentee.partials.benefit-balance', ['benefitBalance' => $benefitBalance ?? []])
                    @else
                    <div class="empty-block" style="padding:1rem;">
                        <div class="icon">📦</div>
                        <p>No active subscription</p>
                        <a href="{{ route('mentee.plans') }}" class="learn-btn-primary" style="margin-top:.75rem;">Choose a plan</a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Focus + Upcoming --}}
            <div class="dash-grid dash-grid-2">
                <div class="learn-panel">
                    <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                        <div>
                            <h3 class="dash-section-title">Today’s focus</h3>
                            <p class="dash-section-sub">Sessions, assessments, and quizzes</p>
                        </div>
                        @if(($upcomingCount ?? 0) > 0)
                        <a href="{{ route('mentee.sessions') }}" class="dash-link">{{ $upcomingCount }} upcoming →</a>
                        @endif
                    </div>
                    @forelse($agendaItems ?? [] as $item)
                    <div class="dash-row">
                        <span class="dash-avatar">
                            @if($item['type'] === 'session') 🎥
                            @elseif($item['type'] === 'request') 📨
                            @elseif($item['type'] === 'assessment') 📝
                            @elseif($item['type'] === 'quiz') 🧠
                            @else 📌
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
                    <div class="empty-block"><div class="icon">✅</div>You’re all caught up. <a href="{{ route('mentors.search') }}" class="dash-link">Book a session →</a></div>
                    @endforelse
                </div>

                <div class="learn-panel">
                    <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                        <div>
                            <h3 class="dash-section-title">Upcoming sessions</h3>
                            <p class="dash-section-sub">Next on your calendar</p>
                        </div>
                        <a href="{{ route('mentee.sessions') }}" class="dash-link">View all →</a>
                    </div>
                    @forelse($upcomingSessions ?? [] as $session)
                    <div class="dash-row">
                        <span class="dash-avatar">🎥</span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.85rem;">{{ $session->title ?: 'Session' }}</div>
                            <div style="font-size:11px;color:var(--text-3);">{{ $session->mentor?->name }} · {{ $session->scheduled_at->format('D, d M · g:i A') }}</div>
                        </div>
                        <span class="dash-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                        @if($session->canJoinCall())
                        <a href="{{ route('sessions.call', $session->id) }}" class="learn-btn-primary" style="padding:.4rem .7rem;font-size:.72rem;">Join</a>
                        @endif
                    </div>
                    @empty
                    <div class="empty-block"><div class="icon">📅</div>No upcoming sessions. <a href="{{ route('mentors.search') }}" class="dash-link">Book now →</a></div>
                    @endforelse
                </div>
            </div>

            {{-- Journey + recent sessions --}}
            <div class="dash-grid dash-grid-2">
                <div class="learn-panel panel-pad">
                    <div class="panel-head">
                        <div>
                            <h3 class="dash-section-title">6-month journey</h3>
                            <p class="dash-section-sub">This week’s curriculum tasks</p>
                        </div>
                        <a href="{{ route('mentee.journey.index') }}" class="dash-link">Continue →</a>
                    </div>
                    @if($enrollment ?? false)
                    <div style="margin-bottom:.85rem;">
                        <div style="font-family:var(--font-head);font-weight:700;">{{ $enrollment->stream->name ?? 'Engineering' }}</div>
                        <div style="font-size:12px;color:var(--text-3);margin:.25rem 0 .55rem;">Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }}</div>
                        @if($canViewProgress ?? false)
                        <div class="progress-track"><span style="width:{{ ($enrollment->current_month/6)*100 }}%"></span></div>
                        @else
                        <div style="font-size:12px;color:var(--text-3);">Task list shown without scores. <a href="{{ route('mentee.plans') }}" class="dash-link">Upgrade →</a></div>
                        @endif
                    </div>
                    @forelse($weekTasks ?? [] as $task)
                    <div style="display:flex;align-items:center;gap:.55rem;padding:.45rem 0;border-bottom:1px solid var(--ln-line);font-size:.85rem;">
                        <span>{{ ($canViewProgress ?? false) && $task->is_completed ? '✅' : '⬜' }}</span>
                        <span style="{{ (($canViewProgress ?? false) && $task->is_completed) ? 'text-decoration:line-through;color:var(--text-3);' : '' }}">{{ $task->title }}</span>
                    </div>
                    @empty
                    <div class="empty-block" style="padding:1rem;">No tasks for this week yet.</div>
                    @endforelse
                    @else
                    <div class="empty-block">
                        <div class="icon">🗺️</div>
                        <p>You haven’t enrolled in a journey yet</p>
                        <a href="{{ route('mentee.journey.index') }}" class="learn-btn-primary" style="margin-top:.75rem;">Start journey</a>
                    </div>
                    @endif
                </div>

                <div class="learn-panel">
                    <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                        <div>
                            <h3 class="dash-section-title">Recent sessions</h3>
                            <p class="dash-section-sub">Latest mentoring activity</p>
                        </div>
                    </div>
                    @forelse($recentSessions ?? [] as $session)
                    <div class="dash-row">
                        <span class="dash-avatar">
                            @if($session->mentor?->avatar_url)
                                <img src="{{ $session->mentor->avatar_url }}" alt="">
                            @else
                                {{ strtoupper(substr($session->mentor?->name ?? 'M', 0, 2)) }}
                            @endif
                        </span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.85rem;">{{ $session->mentor?->name ?? 'Mentor' }}</div>
                            <div style="font-size:11px;color:var(--text-3);">{{ optional($session->scheduled_at)->format('d M · g:i A') }}</div>
                        </div>
                        <span class="dash-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                    </div>
                    @empty
                    <div class="empty-block"><div class="icon">🗂️</div>No sessions yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Assessments / quizzes / community / jobs --}}
            <div class="dash-grid dash-grid-4" style="grid-template-columns:repeat(1,minmax(0,1fr));">
                <div class="dash-grid" style="grid-template-columns:repeat(1,minmax(0,1fr));gap:.85rem;">
                    <div class="dash-grid dash-grid-2">
                        <div class="learn-panel">
                            <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                                <div><h3 class="dash-section-title">Assessments</h3></div>
                                <a href="{{ route('mentee.assessments.index') }}" class="dash-link">All →</a>
                            </div>
                            @forelse(($pendingAssessments ?? collect())->take(3) as $assessment)
                            <div class="dash-row">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:700;font-size:.85rem;">{{ $assessment->title }}</div>
                                    <div style="font-size:11px;color:var(--text-3);">Self-assessment</div>
                                </div>
                                <a href="{{ route('mentee.assessments.show', $assessment->id) }}" class="learn-btn-primary" style="padding:.4rem .7rem;font-size:.72rem;">Start</a>
                            </div>
                            @empty
                            <div class="empty-block" style="padding:1.2rem;">No pending assessments.</div>
                            @endforelse
                        </div>
                        <div class="learn-panel">
                            <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                                <div><h3 class="dash-section-title">Quizzes</h3></div>
                                <a href="{{ route('mentee.quizzes.index') }}" class="dash-link">All →</a>
                            </div>
                            @forelse(($availableQuizzes ?? collect())->take(3) as $quiz)
                            <div class="dash-row">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:700;font-size:.85rem;">{{ $quiz->title }}</div>
                                    <div style="font-size:11px;color:var(--text-3);">{{ $quiz->questions_count ?? 0 }} questions</div>
                                </div>
                                <a href="{{ route('mentee.quizzes.show', $quiz) }}" class="learn-btn-ghost" style="padding:.4rem .7rem;font-size:.72rem;">Try</a>
                            </div>
                            @empty
                            <div class="empty-block" style="padding:1.2rem;">No new quizzes.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="dash-grid dash-grid-2">
                        <div class="learn-panel">
                            <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                                <div><h3 class="dash-section-title">Community</h3></div>
                                <a href="{{ route('mentee.community.index') }}" class="dash-link">{{ ($communityUnread ?? 0) > 0 ? ($communityUnread.' unread →') : 'Open →' }}</a>
                            </div>
                            @forelse(($recentCommunityMessages ?? collect())->take(3) as $message)
                            <a href="{{ route('mentee.community.show', $message->channel?->slug) }}" class="dash-row" style="text-decoration:none;color:inherit;">
                                <span class="dash-avatar">{{ $message->channel?->icon ?? '💬' }}</span>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:700;font-size:.8rem;">{{ $message->channel?->name }}</div>
                                    <div style="font-size:11px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($message->body ?: 'Attachment', 50) }}</div>
                                </div>
                            </a>
                            @empty
                            <div class="empty-block" style="padding:1.2rem;">No community activity yet.</div>
                            @endforelse
                        </div>
                        <div class="learn-panel">
                            <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                                <div><h3 class="dash-section-title">Jobs</h3></div>
                                <a href="{{ route('mentee.jobs') }}" class="dash-link">All →</a>
                            </div>
                            @forelse(($recentJobs ?? collect())->take(3) as $job)
                            <a href="{{ route('mentee.jobs.show', $job->id) }}" class="dash-row" style="text-decoration:none;color:inherit;">
                                <div style="flex:1;min-width:0;">
                                    <div style="font-weight:700;font-size:.85rem;">{{ $job->title }}</div>
                                    <div style="font-size:11px;color:var(--text-3);">{{ $job->department ?: 'General' }} · {{ $job->job_type_label }}</div>
                                </div>
                                <span style="font-size:11px;font-weight:700;color:var(--ln-dark);">{{ $job->salary_range }}</span>
                            </a>
                            @empty
                            <div class="empty-block" style="padding:1.2rem;">No open roles right now.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transactions --}}
            <div class="learn-panel">
                <div class="panel-pad panel-head" style="margin-bottom:0;border-bottom:1px solid var(--ln-line);">
                    <div>
                        <h3 class="dash-section-title">Transactions</h3>
                        <p class="dash-section-sub">Wallet, plans, and session payments</p>
                    </div>
                    <a href="{{ route('mentee.wallet') }}" class="dash-link">Wallet →</a>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
                        <thead>
                            <tr style="text-align:left;background:var(--bg-2);">
                                <th style="padding:.75rem 1rem;font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Date</th>
                                <th style="padding:.75rem .5rem;font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Details</th>
                                <th style="padding:.75rem .5rem;font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Category</th>
                                <th style="padding:.75rem .5rem;font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Amount</th>
                                <th style="padding:.75rem 1rem;font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                            <tr style="border-top:1px solid var(--ln-line);">
                                <td style="padding:.85rem 1rem;white-space:nowrap;color:var(--text-2);">{{ $txn->at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? '—' }}</td>
                                <td style="padding:.85rem .5rem;">
                                    @if($txn->url)
                                        <a href="{{ $txn->url }}" style="font-weight:700;color:var(--text);text-decoration:none;">{{ $txn->title }}</a>
                                    @else
                                        <div style="font-weight:700;">{{ $txn->title }}</div>
                                    @endif
                                    @if($txn->detail)
                                        <div style="font-size:11px;color:var(--text-3);">{{ $txn->detail }}</div>
                                    @endif
                                </td>
                                <td style="padding:.85rem .5rem;color:var(--text-2);">{{ $txn->category }}</td>
                                <td style="padding:.85rem .5rem;font-weight:800;color:{{ $txn->direction === 'in' ? '#16a34a' : 'var(--text)' }};">
                                    {{ $txn->direction === 'in' ? '+' : '−' }}₹{{ number_format($txn->amount, 2) }}
                                </td>
                                <td style="padding:.85rem 1rem;"><span class="dash-status {{ strtolower($txn->status) === 'completed' ? 'completed' : 'pending' }}">{{ $txn->status }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" style="padding:2rem;text-align:center;color:var(--text-3);">No transactions yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('frontend.partials.pagination', ['paginator' => $transactions])
            </div>

            {{-- Recommended mentors --}}
            <div class="learn-panel panel-pad">
                <div class="panel-head">
                    <div>
                        <h3 class="dash-section-title">Recommended mentors</h3>
                        <p class="dash-section-sub">Top-rated hosts you can book next</p>
                    </div>
                    <a href="{{ route('mentors.search') }}" class="dash-link">Browse all →</a>
                </div>
                <div class="dash-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;">
                    @forelse($recommendedMentors ?? [] as $mentor)
                    <a href="{{ $mentor->profile_url }}" class="qa-link" style="flex-direction:column;align-items:stretch;gap:.65rem;">
                        <div style="display:flex;align-items:center;gap:.65rem;">
                            <span class="dash-avatar">{{ strtoupper(substr($mentor->name, 0, 1)) }}</span>
                            <div style="min-width:0;">
                                <div style="font-weight:700;font-size:.85rem;">{{ $mentor->name }}</div>
                                <div style="font-size:11px;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $mentor->designation }}</div>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;font-weight:700;">
                            <span style="color:var(--ln-dark);">₹{{ $mentor->rate_per_minute }}/min</span>
                            <span>⭐ {{ $mentor->rating }}</span>
                        </div>
                    </a>
                    @empty
                    <div class="empty-block">No mentors to recommend right now.</div>
                    @endforelse
                </div>
            </div>

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
    const muted = getComputedStyle(document.documentElement).getPropertyValue('--text-3').trim() || '#71717a';
    const grid = 'rgba(148,163,184,.18)';

    const trendLabels = @json($sessionChart->pluck('month'));
    const booked = @json($sessionChart->pluck('sessions'));
    const completed = @json($sessionChart->pluck('completed'));
    const dailyLabels = @json($dailySessions->pluck('label'));
    const dailyCounts = @json($dailySessions->pluck('count'));
    const statusData = @json(array_values($statusBreakdown));
    const statusLabels = @json(array_map('ucfirst', array_keys($statusBreakdown)));

    Chart.defaults.font.family = 'DM Sans, ui-sans-serif, system-ui, sans-serif';
    Chart.defaults.color = muted;

    new Chart(document.getElementById('menteeChartTrend'), {
        type: 'bar',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Booked',
                    data: booked,
                    backgroundColor: 'rgba(245,158,11,.35)',
                    borderRadius: 8,
                    borderSkipped: false,
                },
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: 'rgba(245,158,11,.9)',
                    borderRadius: 8,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8 } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
                y: { beginAtZero: true, grid: { color: grid }, ticks: { precision: 0 } }
            }
        }
    });

    new Chart(document.getElementById('menteeChartStatus'), {
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

    new Chart(document.getElementById('menteeChartDaily'), {
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
})();
</script>
@endpush
