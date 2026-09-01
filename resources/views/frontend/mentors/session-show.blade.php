{{-- resources/views/frontend/mentor/session-show.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Session Detail — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">

        {{-- Breadcrumb --}}
        <div class="session-detail-breadcrumb">
            <a href="{{ route('mentor.sessions') }}" style="color:var(--brand);">← Sessions</a>
            <span>/</span>
            <span>Session #{{ $session->id ?? '—' }}</span>
        </div>

        <div class="session-detail-layout">

            {{-- Left: Session details --}}
            <div class="session-detail-main">
                {{-- Header card --}}
                <div class="card session-detail-card">
                    <div class="session-detail-header-top">
                        <div class="session-detail-header-main">
                            <div class="session-detail-title-row">
                                <h2 class="session-detail-title">{{ $session->title ?? 'Mentoring Session' }}</h2>
                                <span class="session-status {{ $session->status ?? 'pending' }}">{{ ucfirst($session->status ?? 'Pending') }}</span>
                            </div>
                            <div class="session-detail-meta">
                                <span>📅 {{ $session->scheduled_at?->format('D, d M Y · g:i A') ?? '—' }}</span>
                                <span>⏱ {{ $session->duration_minutes ?? 30 }} min</span>
                                <span>💰 ₹{{ number_format($session->amount_paid ?? 0, 0) }} (your cut: ₹{{ number_format(($session->amount_paid ?? 0) * 0.8, 0) }})</span>
                            </div>
                        </div>
                        <div class="session-detail-actions">
                            @if($session->status === 'upcoming' && $session->canJoinCall())
                                <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary">🎥 Join Session</a>
                                <button class="btn btn-outline" onclick="openMeetingLinkModal()">🔗 Meeting link</button>
                                <button type="button" class="btn btn-outline" onclick="completeSession({{ $session->id }})">✓ Mark Complete</button>
                                <button class="btn btn-ghost" style="color:var(--error);" onclick="declineSession({{ $session->id }})">Cancel</button>
                            @elseif($session->status === 'completed')
                                <span style="font-size:13px;color:var(--success);font-weight:600;">✅ Completed</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Mentee topic / intro --}}
                @if($session->topic ?? false)
                <div class="card session-detail-card">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">📋 What the Mentee Wants to Discuss</h3>
                    <p style="font-size:14px;color:var(--text-2);line-height:1.8;margin:0;">{{ $session->topic }}</p>
                </div>
                @endif

                {{-- Session Notes (mentor shares with mentee) --}}
                <div class="card session-detail-card">
                    <div class="session-detail-card-head">
                        <h3>📝 Shared Session Notes</h3>
                        <span class="session-detail-card-hint">Visible to your mentee</span>
                    </div>
                    @if(in_array($session->status, ['upcoming', 'completed'], true))
                    <form action="{{ route('mentor.sessions.notes', $session->id) }}" method="POST"
                          data-ajax-form="{{ route('mentor.sessions.notes', $session->id) }}"
                          data-success="Notes saved!"
                          data-redirect="{{ route('mentor.sessions.show', $session->id) }}">
                        @csrf
                        <input type="hidden" name="type" value="note">
                        <input type="hidden" name="is_shared" value="1">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Key Discussion Points</label>
                            <textarea name="content" class="form-textarea" rows="5"
                                      placeholder="Summarize what was discussed, insights shared, resources mentioned…"
                                      required>{{ $session->notes->where('is_shared', true)->where('author_id', auth()->id())->first()?->content ?? '' }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:14px;">💾 Save Shared Notes</button>
                    </form>
                    @else
                    <div class="empty-state" style="padding:24px 0;">
                        <p style="font-size:13px;color:var(--text-3);">Shared notes can be added for upcoming or completed sessions.</p>
                    </div>
                    @endif
                </div>

                @include('frontend.sessions.partials.my-notes', ['session' => $session])

                {{-- Review received --}}
                @if($session->menteeReview)
                <div class="card session-detail-card" style="border:1px solid rgba(245,158,11,.3);background:var(--brand-muted);">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">⭐ Review from Mentee</h3>
                    <div class="stars" style="margin-bottom:8px;">{{ str_repeat('★', $session->menteeReview->overall_rating ?? 5) }}</div>
                    <p style="font-size:13px;color:var(--text-2);line-height:1.8;margin-bottom:12px;">"{{ $session->menteeReview->review_text ?? '' }}"</p>
                    <div style="display:flex;gap:16px;font-size:12px;">
                        <span>Communication: <strong>{{ $session->menteeReview->communication_rating ?? '—' }}/5</strong></span>
                        <span>Expertise: <strong>{{ $session->menteeReview->expertise_rating ?? '—' }}/5</strong></span>
                    </div>
                </div>
                @endif

                {{-- Cancellation info --}}
                @if($session->status === 'cancelled')
                <div class="alert alert-error">
                    <span class="alert-icon">❌</span>
                    <div>
                        <strong>Session Cancelled</strong>
                        <p style="margin-top:2px;font-size:12px;">{{ $session->cancellation_reason ?? 'No reason provided.' }}</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right: Mentee info + receipt --}}
            <div class="session-detail-sidebar">

                {{-- Mentee card --}}
                <div class="card">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">👤 Mentee</h3>
                    <div class="session-detail-person">
                        <div class="session-detail-person__avatar">
                            @if($session->mentee->avatar_url ?? false)
                                <img src="{{ $session->mentee->avatar_url }}" alt="">
                            @else
                                {{ strtoupper(substr($session->mentee->name ?? 'M', 0, 1)) }}
                            @endif
                        </div>
                        <div class="session-detail-person__info">
                            <div class="session-detail-person__name">{{ $session->mentee->name ?? '—' }}</div>
                            <div class="session-detail-person__email">{{ $session->mentee->email ?? '' }}</div>
                        </div>
                    </div>
                    <div class="session-detail-person__stats">
                        <div>Stream: <strong>{{ $session->mentee->stream_name ?? 'N/A' }}</strong></div>
                        <div>Total sessions with you: <strong>{{ $session->mentee->sessions_with_mentor ?? 0 }}</strong></div>
                    </div>
                    @if($session->mentee_id)
                    <div class="session-detail-person__actions">
                        <a href="{{ route('mentor.mentees.show', $session->mentee_id) }}" class="btn btn-outline btn-sm">View Profile</a>
                        <a href="{{ route('mentor.sessions', ['filter' => 'all', 'mentee' => $session->mentee_id]) }}" class="btn btn-ghost btn-sm">All Sessions</a>
                    </div>
                    @endif
                </div>

                {{-- Receipt --}}
                <div class="card">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">🧾 Receipt</h3>
                    <div class="booking-summary">
                        <div class="booking-summary-row">
                            <span>Session rate</span>
                            <span>₹{{ $session->mentor->rate_per_minute ?? '—' }}/min</span>
                        </div>
                        <div class="booking-summary-row">
                            <span>Duration</span>
                            <span>{{ $session->duration_minutes ?? 30 }} min</span>
                        </div>
                        <div class="booking-summary-row">
                            <span>Gross amount</span>
                            <span>₹{{ number_format($session->amount_paid ?? 0, 0) }}</span>
                        </div>
                        <div class="booking-summary-row" style="color:var(--error);">
                            <span>Platform fee (20%)</span>
                            <span>−₹{{ number_format(($session->amount_paid ?? 0) * 0.2, 0) }}</span>
                        </div>
                        <div class="booking-summary-row" style="padding-top:10px;border-top:1px solid var(--border);">
                            <span style="font-weight:700;">Your earnings</span>
                            <strong style="color:var(--success);">₹{{ number_format(($session->amount_paid ?? 0) * 0.8, 0) }}</strong>
                        </div>
                        <div class="booking-summary-row" style="font-size:11px;color:var(--text-3);">
                            <span>Status</span>
                            <span>{{ $session->status === 'completed' ? '✅ Credited' : '⏳ Pending' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

{{-- Add Meeting Link Modal --}}
<div id="meeting-link-modal" class="modal-overlay">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <span class="modal-title">Add Meeting Link</span>
            <button type="button" class="modal-close" aria-label="Close">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--text-2);margin:0 0 16px;">Paste your Google Meet, Zoom, or any video call link below. It will be shared with the mentee.</p>
            <form id="meeting-link-form"
                  action="{{ route('mentor.sessions.meeting-link', $session->id) }}"
                  method="POST">
                @csrf @method('PATCH')
                <div class="form-group">
                    <label class="form-label">Meeting Link *</label>
                    <input type="url" name="meeting_link" class="form-input" required
                           value="{{ $session->meeting_link ?? '' }}"
                           placeholder="https://meet.google.com/xxx-yyy-zzz">
                </div>
                <div style="display:flex;gap:10px;margin-top:4px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('meeting-link-modal')">Cancel</button>
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
        onSuccess: () => { showToast('success', '✅ Session confirmed!'); location.reload(); },
        onError: () => showToast('error', 'Could not confirm.')
    });
}
function declineSession(id) {
    if (!confirm('Decline this session?')) return;
    AjaxPost(`/mentor/sessions/${id}/cancel`, {}, {
        loader: true,
        onSuccess: () => { showToast('info', 'Session declined.'); location.href = '{{ route("mentor.sessions") }}'; },
        onError: () => showToast('error', 'Could not decline.')
    });
}
function openMeetingLinkModal() {
    openModal('meeting-link-modal');
}
function completeSession(id) {
    if (!confirm('Mark this session as complete? Earnings will be credited if payment is confirmed.')) return;
    AjaxPost(`/mentor/sessions/${id}/complete`, {}, {
        loader: true,
        onSuccess: (data) => {
            showToast('success', data.message || 'Session marked complete.');
            location.reload();
        },
        onError: () => showToast('error', 'Could not complete session.'),
    });
}
document.getElementById('meeting-link-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
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
            closeModal('meeting-link-modal');
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