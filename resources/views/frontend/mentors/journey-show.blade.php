@extends('frontend.layouts.app')
@section('title', 'Journey — '.$mentee->name)

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">{{ $mentee->name }}’s Journey</div>
                <div class="dash-subtitle">Curriculum enrollment and progress overview.</div>
            </div>
            <a href="{{ route('mentor.mentees.show', $mentee->id) }}" class="btn btn-outline">View mentee</a>
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
            @if($enrollment->mentor_notes)
            <div style="font-size:13px;color:var(--text-2);padding:10px;background:var(--bg);border-radius:var(--radius-sm);">
                {{ $enrollment->mentor_notes }}
            </div>
            @endif
        </div>
        @empty
        <div class="empty-state" style="padding:48px 0;">
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No curriculum enrollment</div>
            <p style="font-size:13px;color:var(--text-2);">This mentee is not enrolled in a stream with you yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
