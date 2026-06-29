{{-- resources/views/frontend/mentor/sessions.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'My Sessions — AcharyaSetu Mentor')

@section('content')
<div class="dash-layout">
    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentor.dashboard') }}" class="sidebar-item"><span class="si-icon">📊</span> Dashboard</a>
        <div class="sidebar-section-label">Sessions</div>
        <a href="{{ route('mentor.sessions') }}" class="sidebar-item active"><span class="si-icon">📅</span> My Sessions @if($pendingCount ?? 0)<span class="si-badge">{{ $pendingCount }}</span>@endif</a>
        <a href="{{ route('mentor.availability') ?? '#' }}" class="sidebar-item"><span class="si-icon">⏰</span> Set Availability</a>
        <div class="sidebar-section-label">Mentees</div>
        <a href="{{ route('mentor.mentees') ?? '#' }}" class="sidebar-item"><span class="si-icon">🎓</span> My Mentees</a>
        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('mentor.wallet') }}" class="sidebar-item"><span class="si-icon">💰</span> Earnings</a>
        <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item"><span class="si-icon">✏️</span> Edit Profile</a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">@csrf<button class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);"><span class="si-icon">🚪</span> Sign Out</button></form>
    </aside>

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
        <div style="display:flex;gap:4px;margin-bottom:20px;background:var(--bg-2);border-radius:var(--radius-sm);padding:4px;width:fit-content;">
            @foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $key=>$label)
            <a href="{{ route('mentor.sessions', ['filter'=>$key]) }}"
               style="padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;transition:all .15s;
               {{ (request('filter','all') === $key) ? 'background:white;color:var(--text);box-shadow:var(--shadow-sm);' : 'color:var(--text-2);' }}">
               {{ $label }}
               @if($key==='pending' && ($pendingCount??0))
                   <span style="background:var(--error);color:white;border-radius:99px;font-size:10px;padding:1px 5px;margin-left:4px;">{{ $pendingCount }}</span>
               @endif
            </a>
            @endforeach
        </div>

        {{-- Sessions --}}
        @forelse($sessions ?? [] as $session)
        <div class="card" style="margin-bottom:12px;padding:0;overflow:hidden;">
            <div style="display:flex;align-items:stretch;">
                <div style="width:4px;flex-shrink:0;background:{{ $session->status==='confirmed'?'var(--success)':($session->status==='pending'?'var(--warning)':($session->status==='completed'?'var(--brand)':'var(--error)')) }};"></div>
                <div style="flex:1;padding:18px;display:flex;gap:16px;align-items:flex-start;">

                    <div class="mentor-avatar-lg" style="width:50px;height:50px;font-size:18px;">
                        @if($session->mentee->avatar_url ?? false)<img src="{{ $session->mentee->avatar_url }}" alt="">@else{{ strtoupper(substr($session->mentee->name,0,1)) }}@endif
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start;">
                            <div>
                                <div style="font-size:15px;font-weight:700;margin-bottom:3px;">{{ $session->title }}</div>
                                <div style="font-size:13px;color:var(--text-2);">Mentee: <strong>{{ $session->mentee->name }}</strong></div>
                            </div>
                            <span class="session-status {{ $session->status }}">{{ ucfirst($session->status) }}</span>
                        </div>
                        <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:10px;">
                            <span style="font-size:12px;color:var(--text-2);">📅 {{ $session->scheduled_at->format('D, d M Y') }}</span>
                            <span style="font-size:12px;color:var(--text-2);">🕐 {{ $session->scheduled_at->format('g:i A') }}</span>
                            <span style="font-size:12px;color:var(--text-2);">⏱ {{ $session->duration_minutes }} min</span>
                            <span style="font-size:12px;color:var(--success);">💰 ₹{{ number_format($session->mentor_earning ?? 0, 0) }}</span>
                        </div>
                        @if($session->topic_notes ?? false)
                        <div style="margin-top:10px;padding:10px;background:var(--bg);border-radius:var(--radius-sm);font-size:12px;color:var(--text-2);">
                            📝 <em>{{ Str::limit($session->topic_notes, 120) }}</em>
                        </div>
                        @endif
                    </div>

                    <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0;align-items:flex-end;">
                        @if($session->status === 'pending')
                            <button class="btn btn-success btn-sm" onclick="acceptSession({{ $session->id }})">✓ Accept</button>
                            <button class="btn btn-ghost btn-sm" style="color:var(--error);" onclick="declineSession({{ $session->id }})">✗ Decline</button>
                        @elseif($session->status === 'confirmed')
                            @if($session->meeting_link)
                                <a href="{{ $session->meeting_link }}" target="_blank" class="btn btn-primary btn-sm">🎥 Start Session</a>
                            @endif
                            <button class="btn btn-outline btn-sm" onclick="addMeetingLink({{ $session->id }})">🔗 Add Link</button>
                        @elseif($session->status === 'completed')
                            <a href="{{ route('mentor.sessions.notes', $session) }}" class="btn btn-outline btn-sm">📝 Session Notes</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:80px 0;">
            <div style="font-size:64px;margin-bottom:16px;">📅</div>
            <div style="font-size:18px;font-weight:700;margin-bottom:8px;">No sessions yet</div>
            <p style="font-size:14px;color:var(--text-2);max-width:360px;margin:0 auto 24px;">
                Complete your profile and go live so mentees can discover and book sessions with you.
            </p>
            <a href="{{ route('mentor.profile.edit') }}" class="btn btn-primary btn-lg">Complete Profile</a>
        </div>
        @endforelse

        @if(isset($sessions) && $sessions->hasPages())
        <div style="margin-top:24px;display:flex;justify-content:center;">{{ $sessions->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- Add meeting link modal --}}
<div id="link-modal" class="modal-overlay" style="display:none;">
    <div class="modal" style="max-width:400px;">
        <div class="modal-title">Add Meeting Link</div>
        <div class="modal-sub">Paste your Google Meet / Zoom / Teams link</div>
        <form id="link-form" method="POST">
            @csrf @method('PATCH')
            <div class="form-group">
                <input type="url" name="meeting_link" id="meeting-link-input" class="form-input" placeholder="https://meet.google.com/..." required>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('link-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">Save Link</button>
            </div>
        </form>
    </div>
</div>

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
    AjaxPost(`/mentor/sessions/${id}/decline`, {}, {
        loader: true,
        onSuccess: () => { showToast('info', 'Session declined.'); location.reload(); },
        onError: e => showToast('error', e.message || 'Could not decline session.')
    });
}
function addMeetingLink(id) {
    document.getElementById('link-modal').style.display = 'flex';
    document.getElementById('link-form').action = `/mentor/sessions/${id}/meeting-link`;
}
document.getElementById('link-modal')?.addEventListener('click', function(e) {
    if(e.target === this) this.style.display = 'none';
});
document.getElementById('link-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const r = await fetch(this.action, { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}, body: fd });
    if (r.ok) { showToast('success', '🔗 Meeting link saved!'); document.getElementById('link-modal').style.display = 'none'; location.reload(); }
    else showToast('error', 'Could not save link.');
});
</script>
@endpush
@endsection