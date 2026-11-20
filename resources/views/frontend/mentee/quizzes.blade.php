@extends('frontend.layouts.app')
@section('title', 'Quizzes — Vedrix')

@push('styles')
<style>
.mq-hub{
    --mq: var(--brand, #f59e0b);
    --mq-dark: var(--brand-dark, #d97706);
    --mq-soft: var(--brand-muted, rgba(245,158,11,.12));
    font-family: var(--font-body, "DM Sans", sans-serif);
}
.mq-hub .dash-title{
    font-family: var(--font-head, "Sora", sans-serif);
    letter-spacing: -.02em;
}
.mq-hub__intro{
    position:relative; overflow:hidden;
    margin:4px 0 20px; padding:20px 22px;
    border:1px solid var(--border, rgba(0,0,0,.07));
    border-radius: var(--radius-lg, 16px);
    background:
        radial-gradient(480px 160px at 100% 0%, color-mix(in srgb, var(--mq) 16%, transparent), transparent 70%),
        var(--card-bg, #fff);
    box-shadow: var(--shadow, 0 4px 24px rgba(0,0,0,.06));
}
.mq-hub__intro::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:linear-gradient(180deg, var(--mq), var(--mq-dark));
}
.mq-hub__eyebrow{
    display:inline-flex; align-items:center; gap:8px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;
    color:var(--mq-dark); background:var(--mq-soft); padding:6px 11px; border-radius:8px;
}
.mq-hub__intro h2{
    margin:12px 0 6px;
    font-family: var(--font-head, "Sora", sans-serif);
    font-size:clamp(22px, 3vw, 28px); line-height:1.15; font-weight:800;
    letter-spacing:-.03em; color:var(--text, #09090b);
}
.mq-hub__intro p{ margin:0; max-width:520px; font-size:14px; line-height:1.55; color:var(--text-2, #52525b); }
.mentee-quiz-card{
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.mentee-quiz-card:hover{
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--mq) 40%, transparent);
    box-shadow: 0 14px 28px rgba(245,158,11,.12);
}
</style>
@endpush

@section('content')
<div class="dash-layout mentee-quiz-page mq-hub">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Quizzes</div>
            <div class="dash-subtitle">Practice quizzes to test and strengthen your knowledge.</div>
        </div>

        <div class="mq-hub__intro">
            <span class="mq-hub__eyebrow">Learning</span>
            <h2>Stay sharp between sessions</h2>
            <p>Attempt published quizzes, track your score, and retake anytime to improve.</p>
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
                    {{ $attempt ? 'View / Retake' : 'Start Quiz' }}
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
