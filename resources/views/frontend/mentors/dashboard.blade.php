@extends('frontend.layouts.app')
@section('title', 'Mentor Dashboard — AcharyaSetu')

@section('content')
@php
    $user = auth()->user();
    $rating = (float) ($user->rating ?? 0);
    $filledStars = (int) round(max(0, min(5, $rating)));

    $checks = [
        ['Photo uploaded',  (bool) $user->avatar_url],
        ['Bio written',     strlen(trim((string) ($user->bio ?? ''))) >= 50],
        ['Expertise added', ! empty($user->expertise)],
        ['Designation set', filled($user->designation)],
        ['Rate configured', (float) ($user->rate_per_minute ?? 0) > 0],
        ['LinkedIn linked', filled($user->linkedin)],
    ];
    $completedCount = collect($checks)->filter(fn ($c) => (bool) $c[1])->count();
    $totalChecks    = count($checks);
    $pct            = $totalChecks > 0
        ? (int) max(0, min(100, round(($completedCount / $totalChecks) * 100)))
        : 0;
@endphp

<div class="dash-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentor.dashboard') }}" class="sidebar-item @if(request()->routeIs('mentor.dashboard')) active @endif">
            <span class="si-icon">📊</span> Dashboard
        </a>

        <div class="sidebar-section-label">Sessions</div>
        <a href="{{ route('mentor.sessions') }}" class="sidebar-item @if(request()->routeIs('mentor.sessions*')) active @endif">
            <span class="si-icon">📅</span> My Sessions
            @if(($stats['pending_sessions'] ?? 0) > 0)
                <span class="si-badge">{{ $stats['pending_sessions'] }}</span>
            @endif
        </a>
        <a href="{{ route('mentor.availability') }}" class="sidebar-item @if(request()->routeIs('mentor.availability')) active @endif">
            <span class="si-icon">⏰</span> Set Availability
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">📝</span> Session Notes
        </a>

        <div class="sidebar-section-label">Mentees</div>
        <a href="#" class="sidebar-item">
            <span class="si-icon">🎓</span> My Mentees
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">🗺️</span> Journey Tracker
        </a>

        <div class="sidebar-section-label">Content</div>
        <a href="#" class="sidebar-item">
            <span class="si-icon">💬</span> Community
        </a>
        <a href="#" class="sidebar-item">
            <span class="si-icon">🧠</span> Assessments
        </a>

        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('mentor.wallet') }}" class="sidebar-item @if(request()->routeIs('mentor.wallet')) active @endif">
            <span class="si-icon">💰</span> Earnings
            <span style="margin-left:auto;font-size:11px;color:var(--success);">₹{{ number_format($user->wallet_balance, 0) }}</span>
        </a>
        <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item @if(request()->routeIs('mentor.profile.*')) active @endif">
            <span class="si-icon">✏️</span> Edit Profile
        </a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">
            @csrf
            <button type="submit" class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);border:none;text-align:left;">
                <span class="si-icon">🚪</span> Sign Out
            </button>
        </form>
    </aside>

    {{-- CONTENT --}}
    <div class="dash-content">

        {{-- Header --}}
        <div class="dash-header flex-between" style="gap:16px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">{{ $user->name }}'s Dashboard</div>
                <div class="dash-subtitle" style="margin-top:8px;">
                    @if($user->mentor_status === 'approved')
                        <span class="badge badge-success">✓ Active Mentor</span>
                    @elseif($user->mentor_status === 'pending')
                        <span class="badge badge-muted">⏳ Pending Approval</span>
                    @else
                        <span class="badge badge-error">Profile needs attention</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('mentor.profile.edit') }}" class="btn btn-primary">✏️ Edit Profile</a>
        </div>

        @if($user->mentor_status === 'pending')
        <div class="alert alert-info" style="margin-bottom:24px;">
            <span class="alert-icon">⏳</span>
            <div>
                <strong>Profile under review</strong>
                <p>Your mentor profile is being reviewed by our team. We'll notify you within 24–48 hours.</p>
            </div>
        </div>
        @endif

        {{-- Stats --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon">📅</div>
                <div class="stat-card-label">Total Sessions</div>
                <div class="stat-card-value">{{ number_format($stats['total_sessions'] ?? $user->total_sessions ?? 0) }}</div>
                <div class="stat-card-delta">+{{ $stats['this_month_sessions'] ?? 0 }} this month</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">💰</div>
                <div class="stat-card-label">Total Earnings</div>
                <div class="stat-card-value">₹{{ number_format($stats['total_earnings'] ?? 0, 0) }}</div>
                <div class="stat-card-delta">₹{{ number_format($stats['this_month_earnings'] ?? 0, 0) }} this month</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">⭐</div>
                <div class="stat-card-label">Average Rating</div>
                <div class="stat-card-value">{{ number_format($rating, 1) }}</div>
                <div class="stars" style="margin-top:4px;" aria-label="{{ number_format($rating, 1) }} out of 5">
                    {{ str_repeat('★', $filledStars) }}{{ str_repeat('☆', 5 - $filledStars) }}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">🎓</div>
                <div class="stat-card-label">Active Mentees</div>
                <div class="stat-card-value">{{ $stats['active_mentees'] ?? 0 }}</div>
                <div class="stat-card-delta">{{ $stats['pending_sessions'] ?? 0 }} sessions pending</div>
            </div>
        </div>

        <div class="dash-panels" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px;margin-bottom:24px;">

            {{-- Upcoming Sessions --}}
            <div class="card" style="min-width:0;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;">
                    <h3 style="font-size:15px;font-weight:700;margin:0;">Upcoming Sessions</h3>
                    <a href="{{ route('mentor.sessions') }}" style="font-size:12px;color:var(--brand);white-space:nowrap;">View all →</a>
                </div>

                @forelse($upcomingSessions ?? [] as $session)
                <div class="session-card" style="margin-bottom:8px;">
                    <div class="session-card-icon">🎥</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;margin-bottom:2px;">{{ $session->title }}</div>
                        <div style="font-size:12px;color:var(--text-2);">with {{ $session->mentee->name ?? 'Mentee' }}</div>
                        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">📅 {{ $session->scheduled_at->format('D, d M · g:i A') }}</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                        <span class="session-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                        @if($session->status === 'pending')
                        <button type="button" class="btn btn-success btn-sm" onclick="acceptSession({{ $session->id }})">Accept</button>
                        @elseif($session->meeting_link)
                        <a href="{{ $session->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">Join</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:40px 16px;text-align:center;">
                    <div style="font-size:32px;line-height:1;">📅</div>
                    <p style="font-size:14px;font-weight:600;margin:10px 0 4px;">No upcoming sessions</p>
                    <p style="font-size:12px;color:var(--text-2);margin:0;">New bookings will show up here.</p>
                </div>
                @endforelse
            </div>

            {{-- Recent Reviews --}}
            <div class="card" style="min-width:0;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:700;margin:0;">Recent Reviews</h3>
                </div>

                @forelse($recentReviews ?? [] as $review)
                <div class="testimonial-card" style="margin-bottom:12px;padding:14px;">
                    <div class="stars" style="font-size:12px;">{{ str_repeat('★', (int) $review->overall_rating) }}</div>
                    <p class="testimonial-text" style="font-size:12px;margin:6px 0 10px;">{{ Str::limit($review->review_text, 80) }}</p>
                    <div class="testimonial-author">
                        <div class="author-avatar" style="width:30px;height:30px;font-size:12px;">{{ strtoupper(substr($review->reviewer->name,0,1)) }}</div>
                        <div>
                            <div class="author-name" style="font-size:12px;">{{ $review->reviewer->name }}</div>
                            <div class="author-role">{{ $review->submitted_at?->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:40px 16px;text-align:center;">
                    <div style="font-size:32px;line-height:1;">⭐</div>
                    <p style="font-size:14px;font-weight:600;margin:10px 0 4px;">No reviews yet</p>
                    <p style="font-size:12px;color:var(--text-2);margin:0;">Feedback from mentees will appear here.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Profile Completeness --}}
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;gap:12px;flex-wrap:wrap;">
                <div>
                    <h3 style="font-size:15px;font-weight:700;margin:0;">Profile Completeness</h3>
                    <p style="font-size:12px;color:var(--text-2);margin:4px 0 0;">{{ $completedCount }} of {{ $totalChecks }} items complete</p>
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-size:20px;font-weight:800;color:{{ $pct === 100 ? 'var(--success)' : 'var(--brand)' }};">{{ $pct }}%</span>
                    @if($pct < 100)
                    <a href="{{ route('mentor.profile.edit') }}" class="btn btn-primary btn-sm">Complete Profile</a>
                    @endif
                </div>
            </div>
            <div class="progress-bar" style="margin-bottom:16px;">
                <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $pct === 100 ? 'var(--success)' : 'var(--brand)' }};"></div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;">
                @foreach($checks as [$label, $isDone])
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;{{ $isDone ? '' : 'color:var(--text-3)' }}">
                    <span>{{ $isDone ? '✅' : '⬜' }}</span> {{ $label }}
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function acceptSession(id) {
    AjaxPost(`/mentor/sessions/${id}/confirm`, {}, {
        loader: true,
        onSuccess: () => { showToast('success','Session confirmed!'); location.reload(); }
    });
}
</script>
<style>
@media (max-width: 900px) {
    .dash-panels { grid-template-columns: 1fr !important; }
}
</style>
@endpush
