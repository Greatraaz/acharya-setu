@extends('frontend.layouts.app')
@section('title', 'Assessments — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header" style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;">
            <div>
                <div class="dash-title">Assessments</div>
                <div class="dash-subtitle">Assessments available to your mentees ({{ $menteeCount }} mentee{{ $menteeCount === 1 ? '' : 's' }}).</div>
            </div>
            @unless($tableMissing ?? false)
            <a href="{{ route('mentor.assessments.create') }}" class="btn btn-primary btn-sm">+ Create Assessment</a>
            @endunless
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        @if($tableMissing ?? false)
        <div class="alert alert-error">Assessments are not available yet. Ask admin to run database migrations.</div>
        @else
        @forelse($assessments as $assessment)
        <div class="card" style="margin-bottom:12px;padding:16px 18px;">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <div style="flex:1;min-width:0;">
                    <div style="font-size:15px;font-weight:700;">{{ $assessment->title }}</div>
                    @if($assessment->description)
                    <p style="font-size:13px;color:var(--text-2);margin-top:6px;max-width:560px;">{{ $assessment->description }}</p>
                    @endif
                    <div style="font-size:12px;color:var(--text-3);margin-top:8px;">
                        {{ $assessment->question_count }} questions · {{ $assessment->completion_count }} completions
                    </div>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('mentor.assessments.show', $assessment) }}" class="btn btn-outline btn-sm">View</a>
                    <a href="{{ route('mentor.assessments.edit', $assessment) }}" class="btn btn-outline btn-sm">Edit</a>
                    <form method="POST" action="{{ route('mentor.assessments.destroy', $assessment) }}"
                          onsubmit="return confirm('Delete this assessment and all mentee progress?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm" style="color:var(--error);">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:60px 0;">
            <div style="font-size:48px;margin-bottom:12px;">🧠</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No assessments yet</div>
            <p style="font-size:13px;color:var(--text-2);max-width:360px;margin:0 auto 16px;">Create monthly assessments for mentees to complete as part of their curriculum.</p>
            <a href="{{ route('mentor.assessments.create') }}" class="btn btn-primary">Create Assessment</a>
        </div>
        @endforelse
        @endif
    </div>
</div>
@endsection
