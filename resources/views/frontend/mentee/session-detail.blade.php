@extends('frontend.layouts.app')
@section('title', 'Session Detail — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="session-detail-breadcrumb">
            <a href="{{ route('mentee.sessions') }}" style="color:var(--brand);">← Sessions</a>
            <span>/</span>
            <span>{{ $session->booking_ref ?? ('Session #'.$session->id) }}</span>
        </div>

        <div class="session-detail-layout">
            <div class="session-detail-main">
                <div class="card session-detail-card">
                    <div class="session-detail-header-top">
                        <div class="session-detail-header-main">
                            <div class="session-detail-title-row">
                                <h2 class="session-detail-title">{{ $session->title ?: 'Mentoring Session' }}</h2>
                                <span class="session-status {{ str_replace('_', '-', $session->status) }}">{{ $session->status_label ?? ucfirst($session->status) }}</span>
                            </div>
                            <div class="session-detail-meta">
                                <span>📅 {{ $session->scheduled_at?->format('D, d M Y · g:i A') ?? '—' }}</span>
                                <span>⏱ {{ $session->duration_minutes ?? 30 }} min</span>
                                <span>💰 ₹{{ number_format((float) ($session->amount ?? 0), 0) }}</span>
                            </div>
                        </div>
                        <div class="session-detail-actions">
                            @if($session->canJoinCall())
                                <a href="{{ route('sessions.call', $session->id) }}" class="btn btn-primary">🎥 Join Session</a>
                            @endif
                            @if($session->status === 'upcoming' && $session->scheduled_at?->gt(now()->addHours(2)))
                                <button type="button" class="btn btn-outline" style="color:var(--error);" onclick="cancelSession({{ $session->id }})">Cancel</button>
                            @endif
                        </div>
                    </div>
                </div>

                @if($session->agenda)
                <div class="card session-detail-card">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">📋 Agenda</h3>
                    <p style="font-size:14px;color:var(--text-2);line-height:1.8;margin:0;">{{ $session->agenda }}</p>
                </div>
                @endif

                <div class="card session-detail-card">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">📝 Shared Notes from Mentor</h3>
                    @php $sharedNotes = ($session->notes ?? collect())->where('is_shared', true); @endphp
                    @forelse($sharedNotes as $note)
                    <div style="padding:12px;border:1px solid var(--border);border-radius:var(--radius);margin-bottom:10px;">
                        <div style="font-size:12px;color:var(--text-3);margin-bottom:6px;">{{ $note->created_at?->format('d M Y') }}</div>
                        <div style="font-size:14px;color:var(--text-2);line-height:1.7;white-space:pre-wrap;word-break:break-word;">{{ $note->content ?? '' }}</div>
                    </div>
                    @empty
                    <p style="font-size:13px;color:var(--text-2);margin:0;">No shared notes yet. Notes from your mentor appear here after the session.</p>
                    @endforelse
                </div>

                @include('frontend.sessions.partials.my-notes', ['session' => $session])
            </div>

            <div class="session-detail-sidebar">
                <div class="card">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">👤 Mentor</h3>
                    <div class="session-detail-person">
                        <div class="session-detail-person__avatar mentor-avatar-lg" style="width:48px;height:48px;border-radius:50%;">
                            @if($session->mentor->avatar_url ?? false)
                                <img src="{{ $session->mentor->avatar_url }}" alt="">
                            @else
                                {{ strtoupper(substr($session->mentor->name ?? '?', 0, 1)) }}
                            @endif
                        </div>
                        <div class="session-detail-person__info">
                            <div class="session-detail-person__name">{{ $session->mentor->name ?? '—' }}</div>
                            <div class="session-detail-person__email">{{ $session->mentor->designation ?? '' }}</div>
                        </div>
                    </div>
                    @if($session->mentor?->slug)
                    <a href="{{ route('mentors.show', $session->mentor->slug) }}" class="btn btn-outline btn-sm" style="width:100%;margin-top:14px;justify-content:center;">View Profile</a>
                    @endif
                </div>

                <div class="card">
                    <h3 style="font-size:13px;font-weight:700;margin-bottom:10px;">🧾 Details</h3>
                    <div class="booking-summary" style="padding:0;border:none;background:transparent;">
                        <div class="booking-summary-row">
                            <span>Status</span>
                            <strong>{{ $session->status_label ?? ucfirst($session->status) }}</strong>
                        </div>
                        <div class="booking-summary-row">
                            <span>Duration</span>
                            <strong>{{ $session->duration_minutes ?? 30 }} min</strong>
                        </div>
                        <div class="booking-summary-row">
                            <span>Amount</span>
                            <strong>₹{{ number_format((float) ($session->amount ?? 0), 0) }}</strong>
                        </div>
                        @if($session->booking_ref)
                        <div class="booking-summary-row">
                            <span>Ref</span>
                            <strong style="font-size:11px;word-break:break-all;">{{ $session->booking_ref }}</strong>
                        </div>
                        @endif
                    </div>
                    @if($session->status === 'completed')
                    <a href="{{ route('mentee.sessions.review', $session->id) }}" class="btn btn-primary btn-sm" style="width:100%;margin-top:14px;justify-content:center;">⭐ Leave a Review</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cancelSession(id) {
    if (!confirm('Cancel this session? A refund will be credited if eligible.')) return;
    AjaxPost(`/mentee/sessions/${id}`, { reason: 'Cancelled by mentee' }, {
        method: 'DELETE',
        loader: true,
        onSuccess: (data) => {
            showToast('success', data.message || 'Session cancelled.');
            setTimeout(() => location.href = '{{ route('mentee.sessions') }}', 800);
        },
        onError: (err) => showToast('error', err.message || 'Could not cancel session.'),
    });
}
</script>
@endpush
