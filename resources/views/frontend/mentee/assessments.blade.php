@extends('frontend.layouts.app')
@section('title', 'Assessments — Vedrix')

@section('content')
@include('frontend.partials.dash-premium-ui')

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page" style="display:flex;flex-direction:column;gap:1.1rem;">

            <div class="learn-intro fu">
                <div>
                    <span class="learn-eyebrow">Learning · Skills check</span>
                    <h2>Assessments</h2>
                    <p>Structured assessments to measure readiness and track how you’re growing with your mentor.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('mentee.assessments.index') }}" class="learn-filter-bar fu">
                <div class="session-filter-tabs">
                    @foreach(['all' => 'All', 'pending' => 'To do', 'completed' => 'Completed'] as $key => $label)
                        @php $tabParams = array_filter(['status' => $key === 'all' ? null : $key, 'search' => ($search ?? request('search')) ?: null]); @endphp
                        <a href="{{ route('mentee.assessments.index', $tabParams) }}"
                           class="session-filter-tab {{ ($status ?? request('status', 'all')) === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <div class="learn-search">
                    @if(($status ?? request('status')) && ($status ?? request('status')) !== 'all')
                        <input type="hidden" name="status" value="{{ $status ?? request('status') }}">
                    @endif
                    <div class="session-search-field">
                        <span class="session-search-icon" aria-hidden="true">🔍</span>
                        <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                               placeholder="Search assessments…" autocomplete="off" aria-label="Search assessments">
                    </div>
                    <button type="submit" class="learn-btn-ghost">Search</button>
                </div>
            </form>

            <div class="learn-list fu">
                @forelse($assessments as $assessment)
                @php
                    $desc = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($assessment->description ?? ''))));
                @endphp
                <article class="learn-list-card">
                    <div style="min-width:0;flex:1;">
                        <div style="display:flex;align-items:flex-start;gap:.65rem;">
                            <span class="learn-stat__icon" aria-hidden="true">📝</span>
                            <div style="min-width:0;">
                                <h3 class="learn-list-card__title">{{ $assessment->title }}</h3>
                                @if($desc !== '')
                                    <p class="learn-list-card__desc">{{ \Illuminate\Support\Str::limit($desc, 140) }}</p>
                                @endif
                                <div class="learn-list-card__meta">
                                    <span>{{ $assessment->question_count }} {{ $assessment->question_count === 1 ? 'question' : 'questions' }}</span>
                                    @if($assessment->completed)
                                        <span class="learn-chip learn-chip--done">Completed</span>
                                        <span>Score {{ number_format((float) $assessment->score, 0) }}</span>
                                        @if($assessment->progress?->completed_at)
                                            <span>{{ $assessment->progress->completed_at->format('d M Y') }}</span>
                                        @endif
                                    @else
                                        <span class="learn-chip learn-chip--todo">To do</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('mentee.assessments.show', $assessment->id) }}"
                       class="{{ $assessment->completed ? 'learn-btn-ghost' : 'learn-btn-primary' }}">
                        {{ $assessment->completed ? 'View Results' : 'Start' }}
                    </a>
                </article>
                @empty
                <div class="learn-panel empty-block">
                    <div class="icon">📝</div>
                    <div style="font-weight:750;color:var(--text);margin-bottom:.25rem;">No assessments found</div>
                    <p>Try adjusting your filters, or check back when your mentor publishes new assessments.</p>
                </div>
                @endforelse
            </div>

            @include('frontend.partials.pagination', ['paginator' => $assessments])
        </div>
    </div>
</div>
@endsection
