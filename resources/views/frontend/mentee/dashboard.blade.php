@extends('frontend.layouts.app')
@section('title', 'My Dashboard — Vedrix')

@section('content')
<div class="dash-layout">

    @include('frontend.mentee.partials.sidebar')

    {{-- CONTENT --}}
    <div class="dash-content">

        {{-- Header --}}
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0] }}! 👋</div>
                <div class="dash-subtitle">Here's what's happening with your learning journey.</div>
            </div>
            <a href="{{ route('mentors.search') }}" class="btn btn-primary">🔍 Find a Mentor</a>
        </div>

        {{-- Stats --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon">📅</div>
                <div class="stat-card-label">Sessions Completed</div>
                <div class="stat-card-value">{{ $stats['sessions'] ?? 0 }}</div>
                <div class="stat-card-delta">+2 this month</div>
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
                <div class="stat-card-value">₹{{ number_format(auth()->user()->wallet_balance, 0) }}</div>
                <a href="{{ route('mentee.wallet') }}" style="font-size:11px;color:var(--brand);">Add Money →</a>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">📈</div>
                <div class="stat-card-label">Journey Progress</div>
                @if($canViewProgress ?? false)
                <div class="stat-card-value">{{ $stats['progress'] ?? 0 }}%</div>
                <div class="progress-bar" style="margin-top:8px;">
                    <div class="progress-fill" style="width:{{ $stats['progress'] ?? 0 }}%"></div>
                </div>
                @else
                <div class="stat-card-value" style="font-size:16px;">Locked</div>
                <a href="{{ route('mentee.plans') }}" style="font-size:11px;color:var(--brand);">Upgrade plan →</a>
                @endif
            </div>
        </div>

        {{-- My Mentor --}}
        <div class="card" style="margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap;">
                <h3 style="font-size:15px;font-weight:700;margin:0;">My Mentor</h3>
                <a href="{{ route('mentee.mentor.change') }}" class="btn btn-outline btn-sm">
                    {{ ($assignedMentor ?? null) ? 'Change mentor' : 'Choose mentor' }}
                </a>
            </div>

            @if($assignedMentor ?? null)
            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                <a href="{{ $assignedMentor->profile_url }}" style="display:flex;align-items:center;gap:14px;text-decoration:none;color:inherit;flex:1;min-width:220px;">
                    <div class="mentor-avatar-lg" style="width:56px;height:56px;border-radius:16px;overflow:hidden;flex-shrink:0;">
                        @if($assignedMentor->avatar_url)
                            <img src="{{ $assignedMentor->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            {{ strtoupper(substr($assignedMentor->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div style="font-size:16px;font-weight:700;">{{ $assignedMentor->name }}</div>
                        <div style="font-size:13px;color:var(--text-2);margin-top:2px;">
                            {{ $assignedMentor->designation }}{{ $assignedMentor->company ? ' · '.$assignedMentor->company : '' }}
                        </div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:6px;">
                            ⭐ {{ number_format((float) ($assignedMentor->rating ?? 0), 1) }}
                            · ₹{{ $assignedMentor->rate_per_minute }}/min
                        </div>
                    </div>
                </a>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ $assignedMentor->profile_url }}" class="btn btn-primary btn-sm">Book session</a>
                    <a href="{{ route('mentee.mentor.change') }}" class="btn btn-ghost btn-sm">Change</a>
                </div>
            </div>
            @else
            <div class="empty-state" style="padding:28px 0;">
                <div style="font-size:36px;">🎓</div>
                <p style="font-size:13px;color:var(--text-2);margin-top:8px;">You don’t have an assigned mentor yet</p>
                <a href="{{ route('mentee.mentor.change') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">Find & request a mentor</a>
            </div>
            @endif

            @if(($pendingMentorRequests ?? collect())->isNotEmpty())
            <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--border);">
                <div style="font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;">Pending requests</div>
                @foreach($pendingMentorRequests as $req)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;flex-wrap:wrap;">
                    <div style="flex:1;min-width:160px;font-size:13px;">
                        <strong>{{ $req->mentor?->name }}</strong>
                        <span style="color:var(--text-3);"> · {{ $req->created_at?->diffForHumans() }}</span>
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

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

            {{-- Upcoming Sessions --}}
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:700;">Upcoming Sessions</h3>
                    <a href="{{ route('mentee.sessions') }}" style="font-size:12px;color:var(--brand);">View all →</a>
                </div>

                @forelse($upcomingSessions ?? [] as $session)
                <div class="session-card" style="margin-bottom:8px;">
                    <div class="session-card-icon">🎥</div>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;margin-bottom:2px;">{{ $session->title }}</div>
                        <div style="font-size:12px;color:var(--text-2);">with {{ $session->mentor->name }}</div>
                        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">📅 {{ $session->scheduled_at->format('D, d M Y · g:i A') }}</div>
                    </div>
                    <div>
                        <span class="session-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                        @if($session->canJoinCall())
                        <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary btn-sm" style="margin-top:6px;">Join</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:32px 0;">
                    <div style="font-size:36px;">📅</div>
                    <p style="font-size:13px;color:var(--text-2);margin-top:8px;">No upcoming sessions yet</p>
                    <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">Book Now</a>
                </div>
                @endforelse
            </div>

            {{-- My Journey Progress --}}
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:700;">6-Month Journey</h3>
                    <a href="{{ route('mentee.journey.index') }}" style="font-size:12px;color:var(--brand);">Continue →</a>
                </div>

                @if($enrollment ?? false)
                <div style="margin-bottom:16px;">
                    <div style="font-size:13px;font-weight:600;margin-bottom:4px;">{{ $enrollment->stream->name ?? 'Engineering' }}</div>
                    <div style="font-size:12px;color:var(--text-2);margin-bottom:8px;">Month {{ $enrollment->current_month }} · Week {{ $enrollment->current_week }}</div>
                    @if($canViewProgress ?? false)
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:{{ ($enrollment->current_month/6)*100 }}%"></div>
                    </div>
                    @else
                    <div style="font-size:11px;color:var(--text-3);">Task list shown without scores. <a href="{{ route('mentee.plans') }}" style="color:var(--brand);">Upgrade for progress report →</a></div>
                    @endif
                </div>
                @forelse($weekTasks ?? [] as $task)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);">
                    @if($canViewProgress ?? false)
                    <span style="font-size:16px;">{{ $task->is_completed ? '✅' : '⬜' }}</span>
                    <span style="font-size:13px;{{ $task->is_completed ? 'text-decoration:line-through;color:var(--text-3)' : '' }}">{{ $task->title }}</span>
                    @else
                    <span style="font-size:16px;">⬜</span>
                    <span style="font-size:13px;">{{ $task->title }}</span>
                    @endif
                </div>
                @empty
                <div style="font-size:12px;color:var(--text-3);padding:8px 0;">No tasks for this week yet.</div>
                @endforelse
                @else
                <div class="empty-state" style="padding:32px 0;">
                    <div style="font-size:36px;">🗺️</div>
                    <p style="font-size:13px;color:var(--text-2);margin-top:8px;">You haven't enrolled in a journey yet</p>
                    <a href="{{ route('mentee.journey.index') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">Start Journey</a>
                </div>
                @endif
            </div>
        </div>

        {{-- Recommended Mentors --}}
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <h3 style="font-size:15px;font-weight:700;">Recommended Mentors for You</h3>
                <a href="{{ route('mentors.search') }}" style="font-size:12px;color:var(--brand);">Browse all →</a>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                @forelse($recommendedMentors ?? [] as $mentor)
                <div class="mentor-card" style="cursor:pointer;" onclick="window.location='{{ $mentor->profile_url }}'">
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
                <div class="mentor-card">
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
                        <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-sm" style="flex:1;">Book</a>
                    </div>
                </div>
                @endforeach
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection