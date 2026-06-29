@extends('frontend.layouts.app')
@section('title', 'My Dashboard — AcharyaSetu')

@section('content')
<div class="dash-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentee.dashboard') }}" class="sidebar-item @if(request()->routeIs('mentee.dashboard')) active @endif">
            <span class="si-icon">📊</span> Dashboard
        </a>

        <div class="sidebar-section-label">Learning</div>
        <a href="{{ route('mentee.journey.index') }}" class="sidebar-item @if(request()->routeIs('mentee.journey.*')) active @endif">
            <span class="si-icon">🗺️</span> My Journey
        </a>
        <a href="{{ route('mentors.search') }}" class="sidebar-item">
            <span class="si-icon">🔍</span> Find Mentors
        </a>
        <a href="{{ route('mentee.sessions') }}" class="sidebar-item @if(request()->routeIs('mentee.sessions')) active @endif">
            <span class="si-icon">📅</span> My Sessions
            @if($upcomingCount ?? 0) <span class="si-badge">{{ $upcomingCount }}</span> @endif
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">📝</span> Assessments
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">🧠</span> Quizzes
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">🏥</span> Wellness Survey
        </a>

        <div class="sidebar-section-label">Community</div>
        <a href="#" class="sidebar-item">
            <span class="si-icon">💬</span> Channels
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">💼</span> Job Listings
        </a>

        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('mentee.wallet') }}" class="sidebar-item @if(request()->routeIs('mentee.wallet')) active @endif">
            <span class="si-icon">💰</span> Wallet
            <span style="margin-left:auto;font-size:11px;color:var(--brand);">₹{{ number_format(auth()->user()->wallet_balance,0) }}</span>
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">👤</span> Profile Settings
        </a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">
            @csrf
            <button class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);">
                <span class="si-icon">🚪</span> Sign Out
            </button>
        </form>
    </aside>

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
                <div class="stat-card-value">{{ $stats['progress'] ?? 0 }}%</div>
                <div class="progress-bar" style="margin-top:8px;">
                    <div class="progress-fill" style="width:{{ $stats['progress'] ?? 0 }}%"></div>
                </div>
            </div>
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
                        @if($session->meeting_link && $session->status === 'confirmed')
                        <a href="{{ $session->meeting_link }}" target="_blank" class="btn btn-primary btn-sm" style="margin-top:6px;">Join</a>
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
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:{{ ($enrollment->current_month/6)*100 }}%"></div>
                    </div>
                </div>
                @foreach($weekTasks ?? [] as $task)
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);">
                    <span style="font-size:16px;">{{ $task->is_completed ? '✅' : '⬜' }}</span>
                    <span style="font-size:13px;{{ $task->is_completed ? 'text-decoration:line-through;color:var(--text-3)' : '' }}">{{ $task->title }}</span>
                </div>
                @endforeach
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
                <div class="mentor-card" style="cursor:pointer;" onclick="window.location='/mentors/{{ $mentor->id }}'">
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