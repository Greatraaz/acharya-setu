@extends('frontend.layouts.app')
@section('title', 'Journey — '.$mentee->name)

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header dash-header--actions flex-between">
            <div class="dash-header__main">
                <div class="dash-title">{{ $mentee->name }}’s Journey</div>
                <div class="dash-subtitle">Curriculum enrollment and progress overview.</div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-primary btn-sm">Edit curriculum</a>
                <a href="{{ route('mentor.mentees.show', $mentee->id) }}" class="btn btn-outline btn-sm">View mentee</a>
            </div>
        </div>

        @forelse($enrollments as $enrollment)
        @php $progress = $enrollment->progress_data ?? []; @endphp
        <div class="card" style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                <div>
                    <div style="font-size:15px;font-weight:700;">{{ $enrollment->stream->name ?? 'Stream' }}</div>
                    <div style="font-size:12px;color:var(--text-2);">
                        Status: {{ ucfirst($enrollment->status) }}
                        · Started {{ $enrollment->start_date?->format('d M Y') ?? '—' }}
                    </div>
                </div>
                <span class="session-status {{ $enrollment->status === 'active' ? 'confirmed' : 'pending' }}">{{ ucfirst($enrollment->status) }}</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px;font-size:13px;">
                <div class="card" style="padding:12px;margin:0;"><div style="color:var(--text-3);font-size:11px;">Month</div><div style="font-weight:700;font-size:18px;">{{ $enrollment->current_month }}</div></div>
                <div class="card" style="padding:12px;margin:0;"><div style="color:var(--text-3);font-size:11px;">Week</div><div style="font-weight:700;font-size:18px;">{{ $enrollment->current_week }}</div></div>
                <div class="card" style="padding:12px;margin:0;"><div style="color:var(--text-3);font-size:11px;">Progress</div><div style="font-weight:700;font-size:18px;">{{ (int) ($progress['percent'] ?? $progress['percentage'] ?? 0) }}%</div></div>
            </div>
            <div style="margin-top:4px;">
                <div class="progress-bar">
                    <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? $progress['percentage'] ?? 0) }}%"></div>
                </div>
            </div>
            @if($enrollment->mentor_notes)
            <div style="font-size:13px;color:var(--text-2);padding:10px;background:var(--bg);border-radius:var(--radius-sm);margin-top:12px;">
                {{ $enrollment->mentor_notes }}
            </div>
            @endif
            @if($enrollment->stream_id)
            <div style="margin-top:12px;">
                <a href="{{ route('mentor.curriculum.months', $enrollment->stream_id) }}" class="btn btn-outline btn-sm">Manage months</a>
            </div>
            @endif
        </div>
        @empty
            @if(($tracks ?? collect())->isNotEmpty())
                @foreach($tracks as $track)
                @php $progress = $track->progress_data ?? []; @endphp
                <div class="card" style="margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                        <div>
                            <div style="font-size:15px;font-weight:700;">{{ $track->name }}</div>
                            <div style="font-size:12px;color:var(--text-2);">Curriculum track assigned</div>
                        </div>
                        <span class="session-status {{ $track->is_active ? 'confirmed' : 'pending' }}">{{ $track->is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <div style="margin-bottom:8px;font-size:11px;color:var(--text-2);display:flex;justify-content:space-between;">
                        <span>Overall progress</span>
                        <span>{{ (int) ($progress['percent'] ?? 0) }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:{{ (int) ($progress['percent'] ?? 0) }}%"></div>
                    </div>
                    <div style="margin-top:12px;">
                        <a href="{{ route('mentor.curriculum.months', $track) }}" class="btn btn-outline btn-sm">Manage months</a>
                    </div>
                </div>
                @endforeach
            @else
            <div class="empty-state" style="padding:48px 0;">
                <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No curriculum enrollment</div>
                <p style="font-size:13px;color:var(--text-2);margin-bottom:16px;">Create a curriculum track for this mentee to start their journey.</p>
                <a href="{{ route('mentor.curriculum.tracks', ['mentee_id' => $mentee->id]) }}" class="btn btn-primary">Create curriculum track</a>
            </div>
            @endif
        @endforelse
    </div>
</div>
@endsection
