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
                <div class="dash-subtitle">Manage, confirm and track all your mentoring sessions.</div>
            </div>
        </div>

        {{-- Pending requests banner --}}
        @if($pendingCount ?? 0)
        <div class="alert alert-warning" style="margin-bottom:20px;">
            <span class="alert-icon">🔔</span>
            <div>
                <strong>{{ $pendingCount }} pending session request{{ $pendingCount > 1 ? 's' : '' }}</strong>
                <p style="font-size:12px;margin-top:2px;">Please respond within 24 hours to maintain your response rate.</p>
            </div>
        </div>
        @endif

        {{-- Filter tabs --}}
        <div class="session-filter-tabs">
            @foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','missed'=>'Missed','cancelled'=>'Cancelled'] as $key=>$label)
            <a href="{{ route('mentor.sessions', ['filter'=>$key]) }}"
               class="session-filter-tab {{ ($filter ?? request('filter','all')) === $key ? 'active' : '' }}">
               {{ $label }}
               @if($key==='pending' && ($pendingCount??0))
                   <span class="session-filter-count">{{ $pendingCount }}</span>
               @endif
            </a>
            @endforeach
        </div>

        {{-- Sessions --}}
        @forelse($sessions ?? [] as $session)
        @php
            $statusKey = $session->status;
            $statusLabel = $session->status_label;
            $barColor = match($statusKey) {
                'confirmed' => 'var(--success)',
                'pending', 'upcoming' => 'var(--warning)',
                'completed' => 'var(--brand)',
                'no_show' => 'var(--text-3)',
                default => 'var(--error)',
            };
        @endphp
        <div class="card" style="margin-bottom:12px;padding:0;overflow:hidden;">
            <div style="display:flex;align-items:stretch;">
                <div style="width:4px;flex-shrink:0;background:{{ $barColor }};"></div>
                <div style="flex:1;padding:18px;display:flex;gap:16px;align-items:flex-start;">

                    <div class="mentor-avatar-lg" style="width:50px;height:50px;font-size:18px;">
                        @if($session->mentee->avatar_url ?? false)<img src="{{ $session->mentee->avatar_url }}" alt="">@else{{ strtoupper(substr($session->mentee->name ?? '?',0,1)) }}@endif
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                            <div>
                                <div style="font-size:15px;font-weight:700;margin-bottom:3px;">{{ $session->title }}</div>
                                <div style="font-size:13px;color:var(--text-2);">Mentee: <strong>{{ $session->mentee->name ?? '—' }}</strong></div>
                            </div>
                            <span class="session-status {{ str_replace('_', '-', $statusKey) }}">{{ $statusLabel }}</span>
                        </div>
                        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:10px;">
                            <span style="font-size:12px;color:var(--text-2);">📅 {{ $session->scheduled_at->format('D, d M Y') }}</span>
                            <span style="font-size:12px;color:var(--text-2);">🕐 {{ $session->scheduled_at->format('g:i A') }}</span>
                            <span style="font-size:12px;color:var(--text-2);">⏱ {{ $session->duration_minutes }} min</span>
                            <span style="font-size:12px;color:var(--success);">💰 ₹{{ number_format($session->mentor_earning ?? $session->amount ?? 0, 0) }}</span>
                        </div>
                        @if($session->topic_notes ?? false)
                        <div style="margin-top:10px;padding:10px;background:var(--bg);border-radius:var(--radius-sm);font-size:12px;color:var(--text-2);">
                            📝 <em>{{ Str::limit($session->topic_notes, 120) }}</em>
                        </div>
                        @endif
                    </div>

                    <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;align-items:flex-end;">
                        @if($statusKey === 'pending')
                            <button class="btn btn-success btn-sm" onclick="acceptSession({{ $session->id }})">✓ Accept</button>
                            <button class="btn btn-ghost btn-sm" style="color:var(--error);" onclick="declineSession({{ $session->id }})">✗ Decline</button>
                        @elseif(in_array($statusKey, ['confirmed', 'upcoming', 'ongoing'], true))
                            @if($session->canJoinCall())
                                <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary btn-sm">🎥 Start Session</a>
                            @endif
                            <button type="button" class="btn btn-outline btn-sm"
                                    onclick="addMeetingLink({{ $session->id }}, {{ json_encode($session->meeting_link) }})">
                                🔗 {{ $session->meeting_link ? 'Edit Link' : 'Add Link' }}
                            </button>
                        @elseif($statusKey === 'completed')
                            <a href="{{ route('mentor.sessions.show', $session->id) }}" class="btn btn-outline btn-sm">📝 Session Notes</a>
                        @elseif($statusKey === 'no_show')
                            <span style="font-size:11px;color:var(--text-3);">Session time passed</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        @php
            $activeFilter = $filter ?? request('filter', 'all');
            $filterLabels = [
                'all'       => 'sessions',
                'pending'   => 'pending sessions',
                'confirmed' => 'confirmed sessions',
                'completed' => 'completed sessions',
                'missed'    => 'missed sessions',
                'cancelled' => 'cancelled sessions',
                'upcoming'  => 'upcoming sessions',
            ];
            $u = auth()->user();
            $profileComplete = (bool) $u->avatar_url
                && strlen(trim((string) ($u->bio ?? ''))) >= 50
                && ! empty($u->expertise)
                && filled($u->designation)
                && (float) ($u->rate_per_minute ?? 0) > 0;
        @endphp
        <div class="empty-state" style="padding:80px 0;">
            <div style="font-size:64px;margin-bottom:16px;">📅</div>
            @if($activeFilter !== 'all')
                <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No {{ $filterLabels[$activeFilter] ?? 'sessions' }} found</div>
                <p style="font-size:14px;color:var(--text-2);max-width:360px;margin:0 auto 24px;">
                    Nothing matches this filter right now. Try another status or view all sessions.
                </p>
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

        @if(isset($sessions) && $sessions->hasPages())
        <div style="margin-top:24px;display:flex;justify-content:center;">{{ $sessions->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- Add meeting link modal — uses .open class (see app.css), not display toggles --}}
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
function acceptSession(id) {
    AjaxPost(`/mentor/sessions/${id}/confirm`, {}, {
        loader: true,
        onSuccess: () => { showToast('success', '✅ Session confirmed! Mentee has been notified.'); location.reload(); },
        onError: e => showToast('error', e.message || 'Could not confirm session.')
    });
}
function declineSession(id) {
    if (!confirm('Decline this session request?')) return;
    AjaxPost(`/mentor/sessions/${id}/cancel`, {}, {
        loader: true,
        onSuccess: () => { showToast('info', 'Session declined.'); location.reload(); },
        onError: e => showToast('error', e.message || 'Could not decline session.')
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