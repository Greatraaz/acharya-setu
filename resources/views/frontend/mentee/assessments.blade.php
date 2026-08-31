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

        <form method="GET" action="{{ route('mentee.assessments.index') }}" class="session-toolbar" style="margin-bottom:16px;">
            <div class="session-filter-tabs">
                @foreach(['all' => 'All', 'pending' => 'To do', 'completed' => 'Completed'] as $key => $label)
                    @php $tabParams = array_filter(['status' => $key === 'all' ? null : $key, 'search' => ($search ?? request('search')) ?: null]); @endphp
                    <a href="{{ route('mentee.assessments.index', $tabParams) }}"
                       class="session-filter-tab {{ ($status ?? request('status', 'all')) === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="session-toolbar-controls">
                @if(($status ?? request('status')) && ($status ?? request('status')) !== 'all')
                    <input type="hidden" name="status" value="{{ $status ?? request('status') }}">
                @endif
                <div class="session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search assessments…" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-outline">Search</button>
            </div>
        </form>

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
            <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No assessments found</div>
            <p style="font-size:13px;color:var(--text-2);max-width:380px;margin:0 auto;">
                Try adjusting your filters, or check back when your mentor publishes new assessments.
            </p>
        </div>
        @endforelse

        @include('frontend.partials.pagination', ['paginator' => $assessments])
    </div>
</div>
@endsection
