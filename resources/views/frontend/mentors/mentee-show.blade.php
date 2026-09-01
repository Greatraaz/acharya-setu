@extends('frontend.layouts.app')
@section('title', $mentee->name.' — Mentee')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header dash-header--actions flex-between">
            <div class="dash-header__main">
                <div class="dash-title">{{ $mentee->name }}</div>
                <div class="dash-subtitle">{{ $mentee->email }}@if($mentee->phone) · {{ $mentee->phone }}@endif</div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-primary btn-sm">Curriculum</a>
                <a href="{{ route('mentor.journey.show', $mentee->id) }}" class="btn btn-outline btn-sm">Progress</a>
            </div>
        </div>

        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Profile</h3>
            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;font-size:13px;">
                <div><span style="color:var(--text-3);">College</span><div style="font-weight:600;">{{ $mentee->college ?: '—' }}</div></div>
                <div><span style="color:var(--text-3);">Field</span><div style="font-weight:600;">{{ $mentee->field ?: '—' }}</div></div>
                <div><span style="color:var(--text-3);">Location</span><div style="font-weight:600;">{{ $mentee->location ?: '—' }}</div></div>
                <div><span style="color:var(--text-3);">Year</span><div style="font-weight:600;">{{ $mentee->year ?: '—' }}</div></div>
            </div>
            @if($mentee->bio)
            <p style="margin-top:14px;font-size:13px;color:var(--text-2);">{{ $mentee->bio }}</p>
            @endif
        </div>

        @if(($tracks ?? collect())->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Curriculum</h3>
            @foreach($tracks as $track)
            @php
                $enrollment = $enrollments->firstWhere('stream_id', $track->id);
            @endphp
            <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;align-items:center;">
                <div>
                    <strong>{{ $track->name }}</strong>
                    · {{ $track->months_count }} month(s)
                    · {{ $track->is_active ? 'Active' : 'Inactive' }}
                    @if($enrollment)
                    · Enrolled · Month {{ $enrollment->current_month }} / Week {{ $enrollment->current_week }}
                    @endif
                </div>
                <a href="{{ route('mentor.curriculum.months', $track) }}" class="btn btn-ghost btn-sm">Manage</a>
            </div>
            @endforeach
        </div>
        @elseif($enrollments->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Enrollments</h3>
            @foreach($enrollments as $enrollment)
            <div style="padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;">
                <strong>{{ $enrollment->stream->name ?? 'Stream' }}</strong>
                · {{ ucfirst($enrollment->status) }}
                · Month {{ $enrollment->current_month }} / Week {{ $enrollment->current_week }}
            </div>
            @endforeach
        </div>
        @endif

        <div class="card">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:12px;">Recent sessions</h3>
            @forelse($sessions as $session)
            <div style="display:flex;justify-content:space-between;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);font-size:13px;">
                <div>
                    <div style="font-weight:600;">{{ $session->title }}</div>
                    <div style="color:var(--text-2);">{{ $session->scheduled_at?->format('d M Y, g:i A') }} · {{ $session->status_label }}</div>
                </div>
                <a href="{{ route('mentor.sessions.show', $session->id) }}" class="btn btn-ghost btn-sm">Open</a>
            </div>
            @empty
            <p style="font-size:13px;color:var(--text-3);">No sessions with this mentee yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
