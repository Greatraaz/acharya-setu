@extends('frontend.layouts.app')
@section('title', 'My Sessions — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="dash-title">My Sessions</div>
                <div class="dash-subtitle">Upcoming and past mentorship sessions.</div>
            </div>
            <a href="{{ route('mentors.search') }}" class="btn btn-primary">🔍 Book a Session</a>
        </div>

        <form method="GET" action="{{ route('mentee.sessions') }}" class="session-toolbar">
            <div class="session-filter-tabs">
                @foreach([
                    'all' => 'All',
                    'upcoming' => 'Upcoming',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ] as $key => $label)
                @php
                    $tabParams = array_filter([
                        'status' => $key === 'all' ? null : $key,
                        'q' => $search ?? request('q') ?: null,
                        'date' => request('date') ?: null,
                    ]);
                @endphp
                <a href="{{ route('mentee.sessions', $tabParams) }}"
                   class="session-filter-tab {{ (request('status', 'all') === $key || (!request('status') && $key === 'all')) ? 'active' : '' }}">
                    {{ $label }}
                </a>
                @endforeach
            </div>

            <div class="session-toolbar-controls">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="q" class="form-input" value="{{ $search ?? request('q') }}"
                           placeholder="Search by name, mentor, or ID…"
                           autocomplete="off">
                </div>
                <input type="date" name="date" class="form-input session-date-input"
                       value="{{ request('date') }}" title="Filter by date">
                <button type="submit" class="btn btn-outline">Search</button>
                @if(request()->filled('q') || request()->filled('date'))
                    <a href="{{ route('mentee.sessions', array_filter(['status' => request('status')])) }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>

        @forelse($sessions as $session)
        @php
            $statusKey = $session->status;
            $statusLabel = $session->status_label ?? ucfirst(str_replace('_', ' ', $statusKey));
            $barColor = match($statusKey) {
                'upcoming' => 'var(--info)',
                'completed' => 'var(--brand)',
                default => 'var(--error)',
            };
            $canCancel = $statusKey === 'upcoming'
                && $session->scheduled_at
                && $session->scheduled_at->gt(now()->addHours(2));
        @endphp
        <div class="card" style="margin-bottom:12px;padding:0;overflow:hidden;">
            <div style="display:flex;align-items:stretch;">
                <div style="width:4px;flex-shrink:0;background:{{ $barColor }};"></div>
                <div style="flex:1;padding:18px;display:flex;gap:16px;align-items:flex-start;">
                    <div class="mentor-avatar-lg" style="width:50px;height:50px;font-size:18px;">
                        @if($session->mentor->avatar_url ?? false)
                            <img src="{{ $session->mentor->avatar_url }}" alt="">
                        @else
                            {{ strtoupper(substr($session->mentor->name ?? '?', 0, 1)) }}
                        @endif
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                            <div>
                                <div style="font-size:15px;font-weight:700;margin-bottom:3px;">{{ $session->title ?: 'Mentoring Session' }}</div>
                                <div style="font-size:13px;color:var(--text-2);">
                                    Mentor: <strong>{{ $session->mentor->name ?? '—' }}</strong>
                                    @if($session->booking_ref)
                                        · <span style="font-size:11px;color:var(--text-3);">{{ $session->booking_ref }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="session-status {{ str_replace('_', '-', $statusKey) }}">{{ $statusLabel }}</span>
                        </div>
                        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:10px;">
                            <span style="font-size:12px;color:var(--text-2);">📅 {{ $session->scheduled_at?->format('D, d M Y') ?? '—' }}</span>
                            <span style="font-size:12px;color:var(--text-2);">🕐 {{ $session->scheduled_at?->format('g:i A') ?? '—' }}</span>
                            <span style="font-size:12px;color:var(--text-2);">⏱ {{ $session->duration_minutes ?? 30 }} min</span>
                            <span style="font-size:12px;color:var(--brand);">💰 ₹{{ number_format((float) ($session->amount ?? 0), 0) }}</span>
                            <span style="font-size:12px;color:var(--text-2);">{{ $session->paymentMethodLabel() }}</span>
                        </div>
                        @if($session->agenda ?? $session->topic_notes ?? false)
                        <div style="margin-top:10px;padding:10px;background:var(--bg);border-radius:var(--radius-sm);font-size:12px;color:var(--text-2);">
                            📝 <em>{{ Str::limit($session->agenda ?? $session->topic_notes, 120) }}</em>
                        </div>
                        @endif
                    </div>

                    <div class="session-card-actions">
                        <a href="{{ route('mentee.sessions.show', $session->id) }}" class="btn btn-outline btn-sm">View</a>
                        @if($session->sessionInvoice)
                            <a href="{{ route('mentee.session-invoices.download', $session->sessionInvoice) }}" class="btn btn-ghost btn-sm">Download invoice</a>
                        @endif
                        @if($session->canJoinCall())
                            <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary btn-sm">🎥 Join</a>
                        @endif
                        @if($canCancel)
                            <button type="button" class="btn btn-ghost btn-sm" style="color:var(--error);" onclick="cancelSession({{ $session->id }})">Cancel</button>
                        @endif
                        @if($statusKey === 'completed' && ($session->can_review ?? true))
                            <a href="{{ route('mentee.sessions.review', $session->id) }}" class="btn btn-ghost btn-sm">⭐ Review</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:64px 0;">
            <div style="font-size:56px;margin-bottom:14px;">📅</div>
            <div style="font-size:18px;font-weight:700;margin-bottom:8px;">
                @if(request()->filled('q') || request()->filled('date'))
                    No matching sessions
                @elseif(request('status'))
                    No {{ request('status') }} sessions
                @else
                    No sessions yet
                @endif
            </div>
            <p style="font-size:14px;color:var(--text-2);max-width:360px;margin:0 auto 20px;">
                @if(request()->filled('q') || request()->filled('date'))
                    Try another name or date — or clear the filters.
                @else
                    Book a mentor to get personalized guidance on career, skills, and college.
                @endif
            </p>
            @if(request()->filled('q') || request()->filled('date'))
                <a href="{{ route('mentee.sessions', array_filter(['status' => request('status')])) }}" class="btn btn-outline">Clear filters</a>
            @else
                <a href="{{ route('mentors.search') }}" class="btn btn-primary">Find Mentors</a>
            @endif
        </div>
        @endforelse

        @if($sessions->hasPages())
        <div style="margin-top:24px;display:flex;justify-content:center;">{{ $sessions->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function cancelSession(id) {
    if (!confirm('Cancel this session? A refund will be credited if within the free cancellation window.')) return;
    AjaxPost(`/mentee/sessions/${id}`, { reason: 'Cancelled by mentee' }, {
        method: 'DELETE',
        loader: true,
        onSuccess: (data) => {
            showToast('success', data.message || 'Session cancelled.');
            setTimeout(() => location.reload(), 800);
        },
        onError: (err) => showToast('error', err.message || 'Could not cancel session.'),
    });
}
</script>
@endpush
