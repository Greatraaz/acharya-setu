{{-- resources/views/frontend/mentor/session-show.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Session Detail — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">

        {{-- Breadcrumb --}}
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:var(--text-2);">
            <a href="{{ route('mentor.sessions') }}" style="color:var(--brand);">← Sessions</a>
            <span>/</span>
            <span>Session #{{ $session->id ?? '—' }}</span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 300px;gap:24px;align-items:start;">

            {{-- Left: Session details --}}
            <div>
                {{-- Header card --}}
                <div class="card" style="margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
                        <div style="flex:1;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;flex-wrap:wrap;">
                                <h2 style="font-size:18px;font-weight:800;">{{ $session->title ?? 'Mentoring Session' }}</h2>
                                <span class="session-status {{ $session->status ?? 'pending' }}">{{ ucfirst($session->status ?? 'Pending') }}</span>
                            </div>
                            <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:13px;color:var(--text-2);">
                                <span>📅 {{ $session->scheduled_at?->format('D, d M Y · g:i A') ?? '—' }}</span>
                                <span>⏱ {{ $session->duration_minutes ?? 30 }} min</span>
                                <span>💰 ₹{{ number_format($session->amount_paid ?? 0, 0) }} (your cut: ₹{{ number_format(($session->amount_paid ?? 0) * 0.8, 0) }})</span>
                            </div>
                        </div>
                        <div style="display:flex;gap:10px;flex-shrink:0;">
                            @if($session->status === 'pending')
                                <button class="btn btn-success" onclick="acceptSession({{ $session->id }})">✓ Accept</button>
                                <button class="btn btn-outline" style="color:var(--error);" onclick="declineSession({{ $session->id }})">✗ Decline</button>
                            @elseif($session->status === 'confirmed')
                                @if($session->meeting_link)
                                    <a href="{{ $session->meeting_link }}" target="_blank" class="btn btn-primary">🎥 Join Session</a>
                                @else
                                    <button class="btn btn-primary" onclick="openMeetingLinkModal()">+ Add Meeting Link</button>
                                @endif
                            @elseif($session->status === 'completed')
                                <span style="font-size:13px;color:var(--success);font-weight:600;">✅ Completed</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Mentee topic / intro --}}
                @if($session->topic ?? false)
                <div class="card" style="margin-bottom:20px;">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">📋 What the Mentee Wants to Discuss</h3>
                    <p style="font-size:14px;color:var(--text-2);line-height:1.8;">{{ $session->topic }}</p>
                </div>
                @endif

                {{-- Session Notes (mentor fills) --}}
                <div class="card" style="margin-bottom:20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h3 style="font-size:14px;font-weight:700;">📝 Session Notes</h3>
                        @if($session->status === 'completed' && !($session->mentor_notes ?? false))
                        <span style="font-size:11px;color:var(--brand);">Add notes for your mentee</span>
                        @endif
                    </div>
                    @if($session->status === 'completed' || $session->status === 'confirmed')
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
                                      required>{{ $session->notes->first()->content ?? $session->mentor_notes ?? '' }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:14px;">💾 Save Notes</button>
                    </form>
                    @else
                    <div class="empty-state" style="padding:24px 0;">
                        <p style="font-size:13px;color:var(--text-3);">Notes can be added once the session is confirmed or completed.</p>
                    </div>
                    @endif
                </div>

                {{-- Review received --}}
                @if($session->menteeReview)
                <div class="card" style="margin-bottom:20px;border:1px solid rgba(245,158,11,.3);background:var(--brand-muted);">
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
            <div style="display:flex;flex-direction:column;gap:20px;">

                {{-- Mentee card --}}
                <div class="card">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">👤 Mentee</h3>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                        <div style="width:48px;height:48px;border-radius:50%;background:var(--brand-muted);
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:18px;font-weight:800;color:var(--brand);flex-shrink:0;">
                            @if($session->mentee->avatar_url ?? false)
                                <img src="{{ $session->mentee->avatar_url }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                            @else
                                {{ strtoupper(substr($session->mentee->name ?? 'M', 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div style="font-size:14px;font-weight:700;">{{ $session->mentee->name ?? '—' }}</div>
                            <div style="font-size:12px;color:var(--text-2);">{{ $session->mentee->email ?? '' }}</div>
                        </div>
                    </div>
                    <div style="font-size:12px;color:var(--text-2);display:flex;flex-direction:column;gap:6px;">
                        <div>Stream: <strong>{{ $session->mentee->stream_name ?? 'N/A' }}</strong></div>
                        <div>Total sessions with you: <strong>{{ $session->mentee->sessions_with_mentor ?? 0 }}</strong></div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:14px;">
                        @if($session->mentee_id)
                        <a href="{{ route('mentor.mentees.show', $session->mentee_id) }}" class="btn btn-outline btn-sm" style="flex:1;text-align:center;">View Profile</a>
                        <a href="{{ route('mentor.sessions', ['filter' => 'all', 'mentee' => $session->mentee_id]) }}" class="btn btn-ghost btn-sm" style="flex:1;text-align:center;">All Sessions</a>
                        @endif
                    </div>
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