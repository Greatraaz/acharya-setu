@extends('frontend.layouts.app')
@section('title', 'Assessments — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Assessments</div>
            <div class="dash-subtitle">Assessments to track how you're doing.</div>
        </div>

        @forelse($assessments as $assessment)
        <div class="card" style="margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:15px;font-weight:700;">{{ $assessment->title }}</div>
                    @if($assessment->description)
                    <p style="font-size:13px;color:var(--text-2);margin-top:6px;max-width:560px;">{{ $assessment->description }}</p>
                    @endif
                    <div style="font-size:12px;color:var(--text-3);margin-top:8px;">
                        {{ $assessment->question_count }} questions
                        @if($assessment->completed)
                            · <span style="color:var(--success);font-weight:600;">✅ Completed</span>
                            · <span style="color:var(--brand);font-weight:600;">Score {{ number_format((float) $assessment->score, 0) }}</span>
                            @if($assessment->progress?->completed_at)
                            · <span style="color:var(--text-3);">{{ $assessment->progress->completed_at->format('d M Y') }}</span>
                            @endif
                        @endif
                    </div>
                </div>
                <a href="{{ route('mentee.assessments.show', $assessment->id) }}" class="btn {{ $assessment->completed ? 'btn-outline' : 'btn-primary' }} btn-sm">
                    {{ $assessment->completed ? 'View Results →' : 'Start →' }}
                </a>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:60px 0;">
            <div style="font-size:48px;margin-bottom:12px;">📝</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No assessments yet</div>
            <p style="font-size:13px;color:var(--text-2);max-width:380px;margin:0 auto;">
                When your mentor or admin publishes assessments, they’ll show up here.
            </p>
        </div>
        @endforelse
    </div>
</div>
@endsection
