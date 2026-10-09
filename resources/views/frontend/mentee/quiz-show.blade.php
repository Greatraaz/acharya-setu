@extends('frontend.layouts.app')
@section('title', $quiz->title.' — Quiz')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content mq">
        <a href="{{ route('mentee.quizzes.index') }}" class="mq-back">← Quizzes</a>

        <div class="mq-intro">
            <div class="mq-intro__main">
                <div class="mq-intro__icon" aria-hidden="true">🧠</div>
                <div>
                    <h1 class="mq-intro__title">{{ $quiz->title }}</h1>
                    @if($quiz->description)
                    <p class="mq-intro__desc">{{ $quiz->description }}</p>
                    @endif
                </div>
            </div>

            <div class="mq-stats">
                <div class="mq-stat">
                    <strong>{{ $quiz->questions->count() }}</strong>
                    <span>Questions</span>
                </div>
                <div class="mq-stat">
                    <strong>{{ $quiz->pass_score }}%</strong>
                    <span>To pass</span>
                </div>
                <div class="mq-stat">
                    <strong>{{ $quiz->time_limit ? $quiz->time_limit.'m' : 'None' }}</strong>
                    <span>{{ $quiz->time_limit ? 'Time limit' : 'No time limit' }}</span>
                </div>
            </div>

            @if($attempt && $attempt->completed_at)
            <div class="mq-last {{ $attempt->passed ? 'is-pass' : 'is-fail' }}">
                <div>
                    <div class="mq-last__label">Latest attempt{{ $attemptCount > 1 ? ' · '.$attemptCount.' taken' : '' }}</div>
                    <div class="mq-last__score">{{ $attempt->passed ? 'Passed' : 'Not passed' }} — {{ (int) $attempt->percentage }}%</div>
                    <div class="mq-last__meta">{{ $attempt->score }}/{{ $attempt->total_marks }} marks · {{ $attempt->completed_at->format('d M Y, h:i A') }}</div>
                </div>
                <a href="{{ route('mentee.quizzes.result', [$quiz, $attempt]) }}" class="btn btn-outline btn-sm">View results</a>
            </div>
            @endif

            <form method="POST" action="{{ route('mentee.quizzes.attempt', $quiz) }}" class="mq-intro__actions">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">
                    {{ $attempt ? 'Retake quiz' : 'Start quiz' }}
                </button>
                <p class="mq-intro__note">{{ $attempt ? 'A new attempt starts fresh. Your earlier scores stay in your history.' : 'Answer each question, then submit. You can retake this quiz later.' }}</p>
            </form>
        </div>
    </div>
</div>
@endsection
