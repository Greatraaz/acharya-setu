@extends('frontend.layouts.app')
@section('title', 'Assessments — AcharyaSetu Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Assessments</div>
            <div class="dash-subtitle">Curriculum assessments available to your mentees ({{ $menteeCount }} mentee{{ $menteeCount === 1 ? '' : 's' }}).</div>
        </div>

        @forelse($assessments as $assessment)
        <div class="card" style="margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div>
                    <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Month {{ $assessment->month }}</div>
                    <div style="font-size:15px;font-weight:700;">{{ $assessment->title }}</div>
                    @if($assessment->description)
                    <p style="font-size:13px;color:var(--text-2);margin-top:6px;max-width:560px;">{{ $assessment->description }}</p>
                    @endif
                </div>
                <span style="font-size:12px;color:var(--text-2);">{{ $assessment->question_count }} questions</span>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:60px 0;">
            <div style="font-size:48px;margin-bottom:12px;">🧠</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No assessments published</div>
            <p style="font-size:13px;color:var(--text-2);max-width:360px;margin:0 auto;">When assessments are added to the curriculum, they’ll be listed here for you to track.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
