@extends('frontend.layouts.app')
@section('title', 'Quizzes — Vedrix')

@section('content')
@include('frontend.partials.dash-premium-ui')

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page" style="display:flex;flex-direction:column;gap:1.1rem;">

            <div class="learn-intro fu">
                <div>
                    <span class="learn-eyebrow">Learning · Practice</span>
                    <h2>Quizzes</h2>
                    <p>Stay sharp between sessions — attempt quizzes, track your score, and retake anytime to improve.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('mentee.quizzes.index') }}" class="learn-filter-bar fu">
                <div class="session-filter-tabs">
                    @foreach([
                        'all' => 'All',
                        'not_attempted' => 'Not attempted',
                        'completed' => 'Completed',
                        'passed' => 'Passed',
                        'failed' => 'Failed',
                    ] as $key => $label)
                        @php $tabParams = array_filter(['status' => $key === 'all' ? null : $key, 'search' => ($search ?? request('search')) ?: null]); @endphp
                        <a href="{{ route('mentee.quizzes.index', $tabParams) }}"
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
                               placeholder="Search quizzes…" autocomplete="off" aria-label="Search quizzes">
                    </div>
                    <button type="submit" class="learn-btn-ghost">Search</button>
                </div>
            </form>

            <div class="learn-grid-cards fu">
                @forelse($quizzes as $quiz)
                @php $attempt = $myAttempts[$quiz->id] ?? null; @endphp
                <article class="learn-tile" style="cursor:default;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <span class="learn-tile__icon" aria-hidden="true">🧠</span>
                        <div style="display:flex;flex-wrap:wrap;gap:.35rem;justify-content:flex-end;">
                            @if($attempt)
                                <span class="learn-chip {{ $attempt->passed ? 'learn-chip--done' : 'learn-chip--todo' }}">
                                    {{ $attempt->passed ? 'Passed' : 'Completed' }}
                                </span>
                            @endif
                            @if($quiz->time_limit)
                                <span class="learn-chip learn-chip--wait">⏱ {{ $quiz->time_limit }}m</span>
                            @endif
                        </div>
                    </div>
                    <h3 class="learn-tile__title">{{ $quiz->title }}</h3>
                    <p class="learn-list-card__desc" style="margin:0;">{{ Str::limit($quiz->description ?: 'No description.', 110) }}</p>
                    <div class="learn-list-card__meta" style="margin-top:0;">
                        <span>{{ $quiz->questions_count }} questions</span>
                        <span>Pass {{ $quiz->pass_score }}%</span>
                        @if($attempt)
                            <span>Score {{ (int) $attempt->percentage }}%</span>
                        @endif
                    </div>
                    <a href="{{ route('mentee.quizzes.show', $quiz) }}"
                       class="{{ $attempt ? 'learn-btn-ghost' : 'learn-btn-primary' }}"
                       style="margin-top:auto;width:100%;">
                        {{ $attempt ? 'View / Retake' : 'Start Quiz' }}
                    </a>
                </article>
                @empty
                <div class="learn-panel empty-block" style="grid-column:1/-1;">
                    <div class="icon">🧠</div>
                    <div style="font-weight:750;color:var(--text);margin-bottom:.25rem;">No quizzes found</div>
                    <p>Try adjusting your filters or check back later.</p>
                </div>
                @endforelse
            </div>

            @include('frontend.partials.pagination', ['paginator' => $quizzes])
        </div>
    </div>
</div>
@endsection
