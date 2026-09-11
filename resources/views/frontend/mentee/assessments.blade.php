@extends('frontend.layouts.app')
@section('title', 'Assessments — Vedrix')

@section('content')
<div class="dash-layout mentee-assess-page">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Assessments</div>
            <div class="dash-subtitle">Assessments to track how you're doing.</div>
        </div>

        <form method="GET" action="{{ route('mentee.assessments.index') }}" class="mentee-assess-toolbar">
            <div class="session-filter-tabs mentee-assess-toolbar__tabs">
                @foreach(['all' => 'All', 'pending' => 'To do', 'completed' => 'Completed'] as $key => $label)
                    @php $tabParams = array_filter(['status' => $key === 'all' ? null : $key, 'search' => ($search ?? request('search')) ?: null]); @endphp
                    <a href="{{ route('mentee.assessments.index', $tabParams) }}"
                       class="session-filter-tab {{ ($status ?? request('status', 'all')) === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="mentee-assess-toolbar__search">
                @if(($status ?? request('status')) && ($status ?? request('status')) !== 'all')
                    <input type="hidden" name="status" value="{{ $status ?? request('status') }}">
                @endif
                <div class="session-search-field mentee-assess-toolbar__field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search assessments…" autocomplete="off" aria-label="Search assessments">
                </div>
                <button type="submit" class="btn btn-outline mentee-assess-toolbar__btn">Search</button>
            </div>
        </form>

        <div class="mentee-assess-list">
            @forelse($assessments as $assessment)
            @php
                $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($assessment->description ?? ''))));
            @endphp
            <article class="mentee-assess-card">
                <div class="mentee-assess-card__body">
                    <h3 class="mentee-assess-card__title">{{ $assessment->title }}</h3>
                    @if($desc !== '')
                    <p class="mentee-assess-card__desc">{{ $desc }}</p>
                    @endif
                    <div class="mentee-assess-card__meta">
                        <span>{{ $assessment->question_count }} {{ $assessment->question_count === 1 ? 'question' : 'questions' }}</span>
                        @if($assessment->completed)
                            <span class="mentee-assess-card__status is-done">Completed</span>
                            <span class="mentee-assess-card__score">Score {{ number_format((float) $assessment->score, 0) }}</span>
                            @if($assessment->progress?->completed_at)
                            <span>{{ $assessment->progress->completed_at->format('d M Y') }}</span>
                            @endif
                        @else
                            <span class="mentee-assess-card__status is-todo">To do</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('mentee.assessments.show', $assessment->id) }}"
                   class="btn {{ $assessment->completed ? 'btn-outline' : 'btn-primary' }} btn-sm mentee-assess-card__cta">
                    {{ $assessment->completed ? 'View Results' : 'Start' }}
                </a>
            </article>
            @empty
            <div class="empty-state" style="padding:60px 0;">
                <div style="font-size:48px;margin-bottom:12px;">📝</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No assessments found</div>
                <p style="font-size:13px;color:var(--text-2);max-width:380px;margin:0 auto;">
                    Try adjusting your filters, or check back when your mentor publishes new assessments.
                </p>
            </div>
            @endforelse
        </div>

        @include('frontend.partials.pagination', ['paginator' => $assessments])
    </div>
</div>
@endsection
