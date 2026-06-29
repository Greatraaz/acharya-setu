@extends('frontend.layouts.app')
@section('title', 'Mentor Dashboard — AcharyaSetu')

@section('content')
<div class="dash-layout">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentor.dashboard') }}" class="sidebar-item @if(request()->routeIs('mentor.dashboard')) active @endif">
            <span class="si-icon">📊</span> Dashboard
        </a>

        <div class="sidebar-section-label">Sessions</div>
        <a href="{{ route('mentor.sessions') }}" class="sidebar-item @if(request()->routeIs('mentor.sessions')) active @endif">
            <span class="si-icon">📅</span> My Sessions
            @if($pendingCount ?? 0) <span class="si-badge">{{ $pendingCount }}</span> @endif
        </a>
        <a href="#" class="sidebar-item">
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
        <a href="{{ route('mentor.wallet') }}" class="sidebar-item">
            <span class="si-icon">💰</span> Earnings
            <span style="margin-left:auto;font-size:11px;color:var(--success);">₹{{ number_format(auth()->user()->wallet_balance, 0) }}</span>
        </a>
        <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item">
            <span class="si-icon">✏️</span> Edit Profile
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
                <div class="dash-title">{{ auth()->user()->name }}'s Dashboard 🧑‍🏫</div>
                <div class="dash-subtitle">
                    @if(auth()->user()->mentor_status === 'approved')
                        <span class="badge badge-success">✓ Active Mentor</span>
                    @elseif(auth()->user()->mentor_status === 'pending')
                        <span class="badge badge-muted">⏳ Pending Approval</span>
                    @else
                        <span class="badge badge-error">Profile needs attention</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('mentor.profile.edit') }}" class="btn btn-outline">✏️ Edit Profile</a>
        </div>

        {{-- Pending approval notice --}}
        @if(auth()->user()->mentor_status === 'pending')
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
                <div class="stat-card-value">{{ auth()->user()->total_sessions }}</div>
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
                <div class="stat-card-value">{{ number_format(auth()->user()->rating, 1) }}</div>
                <div class="stars" style="margin-top:4px;">★★★★★</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">🎓</div>
                <div class="stat-card-label">Active Mentees</div>
                <div class="stat-card-value">{{ $stats['active_mentees'] ?? 0 }}</div>
                <div class="stat-card-delta">{{ $stats['pending_sessions'] ?? 0 }} sessions pending</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px;">

            {{-- Upcoming Sessions --}}
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:700;">Upcoming Sessions</h3>
                    <a href="{{ route('mentor.sessions') }}" style="font-size:12px;color:var(--brand);">View all →</a>
                </div>

                @forelse($upcomingSessions ?? [] as $session)
                <div class="session-card" style="margin-bottom:8px;">
                    <div class="session-card-icon">🎥</div>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:600;margin-bottom:2px;">{{ $session->title }}</div>
                        <div style="font-size:12px;color:var(--text-2);">with {{ $session->mentee->name }}</div>
                        <div style="font-size:12px;color:var(--text-2);margin-top:4px;">📅 {{ $session->scheduled_at->format('D, d M · g:i A') }}</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
                        <span class="session-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                        @if($session->status === 'pending')
                        <button class="btn btn-success btn-sm" onclick="acceptSession({{ $session->id }})">Accept</button>
                        @elseif($session->meeting_link)
                        <a href="{{ $session->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">Join</a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:24px 0;">
                    <div style="font-size:32px;">📅</div>
                    <p style="font-size:13px;color:var(--text-2);margin-top:8px;">No upcoming sessions</p>
                </div>
                @endforelse
            </div>

            {{-- Recent Reviews --}}
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:700;">Recent Reviews</h3>
                </div>

                @forelse($recentReviews ?? [] as $review)
                <div class="testimonial-card" style="margin-bottom:12px;padding:14px;">
                    <div class="stars" style="font-size:12px;">{{ str_repeat('★', $review->overall_rating) }}</div>
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
                <div class="empty-state" style="padding:24px 0;">
                    <div style="font-size:32px;">⭐</div>
                    <p style="font-size:13px;color:var(--text-2);margin-top:8px;">No reviews yet</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Profile Completeness --}}
        @php
        $user = auth()->user();
        $checks = [
            ['Photo uploaded', (bool)$user->avatar_url],
            ['Bio written', strlen($user->bio ?? '') > 50],
            ['Expertise added', !empty($user->expertise)],
            ['Designation set', (bool)$user->designation],
            ['Rate configured', $user->rate_per_minute > 0],
            ['LinkedIn linked', (bool)$user->linkedin],
        ];
        $done = collect($checks)->where(1, true)->count();
        $pct = round($done / count($checks) * 100);
        @endphp
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 style="font-size:15px;font-weight:700;">Profile Completeness — {{ $pct }}%</h3>
                <a href="{{ route('mentor.profile.edit') }}" class="btn btn-primary btn-sm">Complete Profile</a>
            </div>
            <div class="progress-bar" style="margin-bottom:16px;">
                <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $pct === 100 ? 'var(--success)' : 'var(--brand)' }}"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                @foreach($checks as [$label, $done])
                <div style="display:flex;align-items:center;gap:8px;font-size:13px;{{ !$done ? 'color:var(--text-3)' : '' }}">
                    <span>{{ $done ? '✅' : '⬜' }}</span> {{ $label }}
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
    AjaxPost(`/admin/sessions/${id}/confirm`, {}, {
        loader: true,
        onSuccess: () => { showToast('success','Session confirmed!'); location.reload(); }
    });
}
</script>
@endpush