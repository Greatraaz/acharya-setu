{{-- resources/views/frontend/mentor/sessions.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'My Sessions — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">My Sessions</div>
                <div class="dash-subtitle">Manage and track all your mentoring sessions.</div>
            </div>
        </div>

        <form method="GET" action="{{ route('mentor.sessions') }}" class="session-toolbar">
            <div class="session-filter-tabs">
                @foreach(['all'=>'All','upcoming'=>'Upcoming','completed'=>'Completed','cancelled'=>'Cancelled'] as $key=>$label)
                @php
                    $tabParams = array_filter([
                        'filter' => $key === 'all' ? null : $key,
                        'q' => $search ?? request('q') ?: null,
                        'date' => request('date') ?: null,
                    ]);
                @endphp
                <a href="{{ route('mentor.sessions', $tabParams) }}"
                   class="session-filter-tab {{ ($filter ?? request('filter','all')) === $key ? 'active' : '' }}">
                   {{ $label }}
                </a>
                @endforeach
            </div>

            <div class="session-toolbar-controls">
                @if(($filter ?? request('filter')) && ($filter ?? request('filter')) !== 'all')
                    <input type="hidden" name="filter" value="{{ $filter ?? request('filter') }}">
                @endif
                <div class="session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="q" class="form-input" value="{{ $search ?? request('q') }}"
                           placeholder="Search by name, mentee, or ID…"
                           autocomplete="off">
                </div>
                <input type="date" name="date" class="form-input session-date-input"
                       value="{{ request('date') }}" title="Filter by date">
                <button type="submit" class="btn btn-outline">Search</button>
                @if(request()->filled('q') || request()->filled('date'))
                    <a href="{{ route('mentor.sessions', array_filter(['filter' => (($filter ?? 'all') === 'all' ? null : ($filter ?? null))])) }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>

        @forelse($sessions ?? [] as $session)
        @php
            $statusKey = $session->status;
            $statusLabel = $session->status_label;
            $barColor = match($statusKey) {
                'upcoming' => 'var(--info)',
                'completed' => 'var(--brand)',
                default => 'var(--error)',
            };
        @endphp
        <div class="card session-list-card">
            <div class="session-list-card__accent" style="background:{{ $barColor }};"></div>
            <div class="session-card-inner">
                <div class="mentor-avatar-lg session-list-card__avatar">
                    @if($session->mentee->avatar_url ?? false)<img src="{{ $session->mentee->avatar_url }}" alt="">@else{{ strtoupper(substr($session->mentee->name ?? '?',0,1)) }}@endif
                </div>

                <div class="session-card-body">
                    <div class="session-card-header">
                        <div class="session-card-header__main">
                            <div class="session-card-title">{{ $session->title }}</div>
                            <div class="session-card-subtitle">
                                Mentee: <strong>{{ $session->mentee->name ?? '—' }}</strong>
                                @if($session->booking_ref)
                                    · <span class="session-card-ref">{{ $session->booking_ref }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="session-status {{ str_replace('_', '-', $statusKey) }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="session-card-meta">
                        <span>📅 {{ $session->scheduled_at->format('D, d M Y') }}</span>
                        <span>🕐 {{ $session->scheduled_at->format('g:i A') }}</span>
                        <span>⏱ {{ $session->duration_minutes }} min</span>
                        <span class="session-card-meta__earn">💰 ₹{{ number_format($session->mentor_earning ?? $session->amount ?? 0, 0) }}</span>
                    </div>
                    @if($session->agenda ?? $session->topic_notes ?? false)
                    <div class="session-card-notes">
                        📝 <em>{{ Str::limit($session->agenda ?? $session->topic_notes, 120) }}</em>
                    </div>
                    @endif
                </div>

                <div class="session-card-actions">
                    <a href="{{ route('mentor.sessions.show', $session->id) }}" class="btn btn-outline btn-sm">View</a>
                    @if($statusKey === 'upcoming')
                        @if($session->canJoinCall())
                            <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary btn-sm">🎥 Start Session</a>
                        @endif
                        <button type="button" class="btn btn-outline btn-sm"
                                onclick="addMeetingLink({{ $session->id }}, {{ json_encode($session->meeting_link) }})">
                            🔗 {{ $session->meeting_link ? 'Edit Link' : 'Add Link' }}
                        </button>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="completeSession({{ $session->id }})">Mark complete</button>
                        <button type="button" class="btn btn-ghost btn-sm" style="color:var(--error);" onclick="declineSession({{ $session->id }})">Cancel</button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        @php
            $activeFilter = $filter ?? request('filter', 'all');
            $u = auth()->user();
            $profileComplete = (bool) $u->avatar_url
                && strlen(trim((string) ($u->bio ?? ''))) >= 50
                && ! empty($u->expertise)
                && filled($u->designation)
                && (float) ($u->rate_per_minute ?? 0) > 0;
        @endphp
        <div class="empty-state" style="padding:80px 0;">
            <div style="font-size:64px;margin-bottom:16px;">📅</div>
            @if(request()->filled('q') || request()->filled('date'))
                <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No matching sessions</div>
                <p style="font-size:14px;color:var(--text-2);max-width:360px;margin:0 auto 24px;">Try another name or date — or clear the filters.</p>
                <a href="{{ route('mentor.sessions', array_filter(['filter' => $activeFilter === 'all' ? null : $activeFilter])) }}" class="btn btn-outline">Clear filters</a>
            @elseif($activeFilter !== 'all')
                <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No {{ $activeFilter }} sessions found</div>
                <a href="{{ route('mentor.sessions') }}" class="btn btn-primary btn-lg">View all sessions</a>
            @elseif(! $profileComplete)
                <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No sessions yet</div>
                <p style="font-size:14px;color:var(--text-2);max-width:360px;margin:0 auto 24px;">
                    Complete your profile and go live so mentees can discover and book sessions with you.
                </p>
                <a href="{{ route('mentor.profile.edit') }}" class="btn btn-primary btn-lg">Complete Profile</a>
            @else
                <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No sessions yet</div>
                <p style="font-size:14px;color:var(--text-2);max-width:360px;margin:0 auto 24px;">
                    Your profile looks ready. Keep your availability up to date so mentees can book you.
                </p>
                <a href="{{ route('mentor.availability') }}" class="btn btn-primary btn-lg">Set Availability</a>
            @endif
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $sessions])
    </div>
</div>

<div id="link-modal" class="modal-overlay">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header">
            <span class="modal-title">Add Meeting Link</span>
            <button type="button" class="modal-close" aria-label="Close">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">Paste your Google Meet / Zoom / Teams link</p>
            <form id="link-form" method="POST">
                @csrf @method('PATCH')
                <div class="form-group">
                    <input type="url" name="meeting_link" id="meeting-link-input" class="form-input" placeholder="https://meet.google.com/..." required>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('link-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">Save Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function declineSession(id) {
    if (!confirm('Cancel this session?')) return;
    AjaxPost(`/mentor/sessions/${id}/cancel`, { reason: 'Cancelled by mentor' }, {
        loader: true,
        onSuccess: () => { showToast('info', 'Session cancelled.'); location.reload(); },
        onError: e => showToast('error', e.message || 'Could not cancel session.')
    });
}
function completeSession(id) {
    if (!confirm('Mark this session as completed?')) return;
    AjaxPost(`/mentor/sessions/${id}/complete`, {}, {
        loader: true,
        onSuccess: (d) => { showToast('success', d.message || 'Completed.'); location.reload(); },
        onError: e => showToast('error', e.message || 'Could not complete session.')
    });
}
window.addMeetingLink = function (id, currentLink) {
    const form = document.getElementById('link-form');
    const input = document.getElementById('meeting-link-input');
    if (!form || !input) {
        showToast('error', 'Link form not found.');
        return;
    }
    form.action = `/mentor/sessions/${id}/meeting-link`;
    input.value = currentLink || '';
    openModal('link-modal');
    setTimeout(() => input.focus(), 50);
};
document.getElementById('link-form')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!this.action) {
        showToast('error', 'Session not selected.');
        return;
    }
    const fd = new FormData(this);
    try {
        const r = await fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: fd,
        });
        const data = await r.json().catch(() => ({}));
        if (r.ok) {
            showToast('success', '🔗 Meeting link saved!');
            closeModal('link-modal');
            location.reload();
        } else {
            showToast('error', data.message || Object.values(data.errors || {})[0]?.[0] || 'Could not save link.');
        }
    } catch (err) {
        showToast('error', 'Could not save link.');
    }
});
</script>
@endpush
