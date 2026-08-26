@extends('frontend.layouts.app')
@section('title', 'Mentor Dashboard — Vedrix')

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
    @include('frontend.mentors.partials.sidebar', ['pendingCount' => $stats['pending_sessions'] ?? 0])

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

        @if(($pendingMentorRequests ?? collect())->isNotEmpty())
        <div class="card" style="margin-bottom:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h3 style="font-size:15px;font-weight:700;margin:0;">Mentee requests</h3>
                <a href="{{ route('mentor.requests') }}" style="font-size:12px;color:var(--brand);">View all →</a>
            </div>
            @foreach($pendingMentorRequests as $req)
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);flex-wrap:wrap;">
                <div style="flex:1;min-width:160px;">
                    <div style="font-weight:600;">{{ $req->mentee?->name }}</div>
                    <div style="font-size:12px;color:var(--text-3);">{{ $req->created_at?->diffForHumans() }}</div>
                </div>
                <form method="POST" action="{{ route('mentor.requests.accept', $req->id) }}">@csrf<button class="btn btn-success btn-sm">Accept</button></form>
                <form method="POST" action="{{ route('mentor.requests.reject', $req->id) }}">@csrf<button class="btn btn-ghost btn-sm">Decline</button></form>
            </div>
            @endforeach
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
                <div class="stat-card-delta">{{ $stats['pending_sessions'] ?? 0 }} upcoming</div>
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
                        @if($session->canJoinCall())
                        <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary btn-sm">Join</a>
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
