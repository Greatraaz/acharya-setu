{{-- resources/views/frontend/mentor/session-show.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Session Detail — AcharyaSetu')

@section('content')
<div class="dash-layout">

    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentor.dashboard') }}" class="sidebar-item"><span class="si-icon">📊</span> Dashboard</a>
        <div class="sidebar-section-label">Sessions</div>
        <a href="{{ route('mentor.sessions') }}" class="sidebar-item active"><span class="si-icon">📅</span> My Sessions</a>
        <a href="{{ route('mentor.availability') }}" class="sidebar-item"><span class="si-icon">⏰</span> Set Availability</a>
        <a href="#" class="sidebar-item"><span class="si-icon">📝</span> Session Notes</a>
        <div class="sidebar-section-label">Mentees</div>
        <a href="{{ route('mentor.mentees') }}" class="sidebar-item"><span class="si-icon">🎓</span> My Mentees</a>
        <a href="#" class="sidebar-item"><span class="si-icon">🗺️</span> Journey Tracker</a>
        <div class="sidebar-section-label">Content</div>
        <a href="#" class="sidebar-item"><span class="si-icon">💬</span> Community</a>
        <a href="#" class="sidebar-item"><span class="si-icon">🧠</span> Assessments</a>
        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('mentor.wallet') }}" class="sidebar-item">
            <span class="si-icon">💰</span> Earnings
            <span style="margin-left:auto;font-size:11px;color:var(--success);">₹{{ number_format(auth()->user()->wallet_balance ?? 0, 0) }}</span>
        </a>
        <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item"><span class="si-icon">✏️</span> Edit Profile</a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">
            @csrf<button class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);"><span class="si-icon">🚪</span> Sign Out</button>
        </form>
    </aside>

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
                    <form action="{{ route('mentor.sessions.notes', $session->id ?? 0) }}" method="POST"
                          data-ajax-form="{{ route('mentor.sessions.notes', $session->id ?? 0) }}"
                          data-success="Notes saved!">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Key Discussion Points</label>
                            <textarea name="mentor_notes" class="form-textarea" rows="4"
                                      placeholder="Summarize what was discussed, insights shared, resources mentioned…">{{ $session->mentor_notes ?? '' }}</textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Action Items for Mentee</label>
                            <textarea name="action_items" class="form-textarea" rows="3"
                                      placeholder="List specific tasks or steps for the mentee to work on…">{{ $session->action_items ?? '' }}</textarea>
                            <div class="form-hint">Shared with the mentee after the session.</div>
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
                @if($session->review ?? false)
                <div class="card" style="margin-bottom:20px;border:1px solid rgba(245,158,11,.3);background:var(--brand-muted);">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">⭐ Review from Mentee</h3>
                    <div class="stars" style="margin-bottom:8px;">{{ str_repeat('★', $session->review->overall_rating ?? 5) }}</div>
                    <p style="font-size:13px;color:var(--text-2);line-height:1.8;margin-bottom:12px;">"{{ $session->review->review_text ?? '' }}"</p>
                    <div style="display:flex;gap:16px;font-size:12px;">
                        <span>Communication: <strong>{{ $session->review->communication_rating ?? '—' }}/5</strong></span>
                        <span>Expertise: <strong>{{ $session->review->expertise_rating ?? '—' }}/5</strong></span>
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
                        <a href="{{ route('mentor.mentees.show', $session->mentee->id ?? 0) }}" class="btn btn-outline btn-sm" style="flex:1;text-align:center;">View Profile</a>
                        <a href="{{ route('mentor.sessions', ['mentee' => $session->mentee->id ?? 0]) }}" class="btn btn-ghost btn-sm" style="flex:1;text-align:center;">All Sessions</a>
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
<div id="meeting-link-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:none;align-items:center;justify-content:center;">
    <div class="card" style="max-width:440px;width:90%;padding:28px;">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">Add Meeting Link</h3>
        <p style="font-size:13px;color:var(--text-2);margin-bottom:16px;">Paste your Google Meet, Zoom, or any video call link below. It will be shared with the mentee.</p>
        <form action="{{ route('mentor.sessions.meeting-link', $session->id ?? 0) }}" method="POST"
              data-ajax-form="{{ route('mentor.sessions.meeting-link', $session->id ?? 0) }}"
              data-success="Meeting link added!">
            @csrf @method('PATCH')
            <div class="form-group">
                <label class="form-label">Meeting Link *</label>
                <input type="url" name="meeting_link" class="form-input" required
                       value="{{ $session->meeting_link ?? '' }}"
                       placeholder="https://meet.google.com/xxx-yyy-zzz">
            </div>
            <div style="display:flex;gap:10px;margin-top:4px;">
                <button type="button" class="btn btn-ghost" onclick="closeMeetingLinkModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Save Link</button>
            </div>
        </form>
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
    AjaxPost(`/mentor/sessions/${id}/decline`, {}, {
        loader: true,
        onSuccess: () => { showToast('info', 'Session declined.'); location.href = '{{ route("mentor.sessions") }}'; },
        onError: () => showToast('error', 'Could not decline.')
    });
}
function openMeetingLinkModal() {
    document.getElementById('meeting-link-modal').style.display = 'flex';
}
function closeMeetingLinkModal() {
    document.getElementById('meeting-link-modal').style.display = 'none';
}
document.getElementById('meeting-link-modal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeMeetingLinkModal();
});
</script>
@endpush