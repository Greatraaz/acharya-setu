@extends('frontend.layouts.app')
@section('title', 'Quizzes — Vedrix')

@section('content')
<div class="dash-layout mentee-quiz-page">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Quizzes</div>
            <div class="dash-subtitle">Practice quizzes to test and strengthen your knowledge.</div>
        </div>

        <form method="GET" action="{{ route('mentee.quizzes.index') }}" class="mentee-quiz-toolbar">
            <div class="session-filter-tabs mentee-quiz-toolbar__tabs">
                @foreach([
                    'all' => 'All',
                    'not_attempted' => 'Not attempted',
                    'completed' => 'Completed',
                    'passed' => 'Passed',
                    'failed' => 'Failed',
                ] as $key => $label)
                    @php $tabParams = array_filter(['status' => $key === 'all' ? null : $key, 'search' => ($search ?? request('search')) ?: null]); @endphp
                    <a href="{{ route('mentee.quizzes.index', $tabParams) }}"
                       class="session-filter-tab {{ ($status ?? request('status', 'all')) === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="mentee-quiz-toolbar__search">
                @if(($status ?? request('status')) && ($status ?? request('status')) !== 'all')
                    <input type="hidden" name="status" value="{{ $status ?? request('status') }}">
                @endif
                <div class="session-search-field mentee-quiz-toolbar__field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search ?? request('search') }}"
                           placeholder="Search quizzes…" autocomplete="off" aria-label="Search quizzes">
                </div>
                <button type="submit" class="btn btn-outline mentee-quiz-toolbar__btn">Search</button>
            </div>
        </form>

        <div class="mentee-quiz-grid">
            @forelse($quizzes as $quiz)
            @php $attempt = $myAttempts[$quiz->id] ?? null; @endphp
            <article class="mentee-quiz-card">
                <div class="mentee-quiz-card__top">
                    <span class="mentee-quiz-card__icon" aria-hidden="true">🧠</span>
                    <div class="mentee-quiz-card__badges">
                        @if($attempt)
                            <span class="session-status {{ $attempt->passed ? 'completed' : 'pending' }}">
                                {{ $attempt->passed ? 'Passed' : 'Completed' }}
                            </span>
                        @endif
                        @if($quiz->time_limit)
                            <span class="tag">⏱ {{ $quiz->time_limit }}m</span>
                        @endif
                    </div>
                </div>
                <h3 class="mentee-quiz-card__title">{{ $quiz->title }}</h3>
                <p class="mentee-quiz-card__desc">{{ Str::limit($quiz->description ?: 'No description.', 110) }}</p>
                <div class="mentee-quiz-card__meta">
                    <span>{{ $quiz->questions_count }} questions</span>
                    <span>Pass {{ $quiz->pass_score }}%</span>
                    @if($attempt)
                        <span>Score {{ (int) $attempt->percentage }}%</span>
                    @endif
                </div>
                <a href="{{ route('mentee.quizzes.show', $quiz) }}"
                   class="btn {{ $attempt ? 'btn-outline' : 'btn-primary' }} btn-sm mentee-quiz-card__cta">
                    {{ $attempt ? 'Review / Retake' : 'Start Quiz' }}
                </a>
            </article>
            @empty
            <div class="empty-state mentee-quiz-empty">
                <div class="mentee-quiz-empty__icon" aria-hidden="true">🧠</div>
                <div class="mentee-quiz-empty__title">No quizzes found</div>
                <p class="mentee-quiz-empty__text">Try adjusting your filters or check back later.</p>
            </div>
            @endforelse
        </div>

        @include('frontend.partials.pagination', ['paginator' => $quizzes])
    </div>
</div>
@endsection
