@extends('frontend.layouts.app')
@section('title', 'Result — '.$quiz->title)

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content mq">
        <a href="{{ route('mentee.quizzes.index') }}" class="mq-back">← Quizzes</a>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:14px;">
            <span class="alert-icon">✓</span>
            <div style="font-size:13px;">{{ session('success') }}</div>
        </div>
        @endif

        <div class="mq-result">
            <div class="card mq-score {{ $attempt->passed ? 'is-pass' : 'is-fail' }}">
                <div class="mq-score__emoji" aria-hidden="true">{{ $attempt->passed ? '🎉' : '😔' }}</div>
                <h1 class="mq-score__title">{{ $attempt->passed ? 'You passed' : 'Not passed' }}</h1>
                <p class="mq-score__quiz">{{ $quiz->title }}</p>
                @if($attemptCount > 1)
                <p class="mq-score__attempt">Attempt {{ $attemptCount }}</p>
                @endif

                <div class="mq-stats">
                    <div class="mq-stat">
                        <strong>{{ (int) $attempt->percentage }}%</strong>
                        <span>Score</span>
                    </div>
                    <div class="mq-stat">
                        <strong>{{ $attempt->score }}/{{ $attempt->total_marks }}</strong>
                        <span>Marks</span>
                    </div>
                    <div class="mq-stat">
                        <strong>{{ $quiz->pass_score }}%</strong>
                        <span>Pass mark</span>
                    </div>
                </div>

                <div class="progress-bar mq-progress">
                    <div class="progress-fill" style="width:{{ (int) $attempt->percentage }}%;background:{{ $attempt->passed ? 'var(--success)' : 'var(--error)' }};"></div>
                </div>

                <div class="mq-score__actions">
                    <form method="POST" action="{{ route('mentee.quizzes.attempt', $quiz) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Retake quiz</button>
                    </form>
                    <a href="{{ route('mentee.quizzes.show', $quiz) }}" class="btn btn-outline">Quiz details</a>
                </div>
            </div>

            <div class="card mq-review">
                <h2 class="mq-review__title">{{ $quiz->show_results ? 'Answer review' : 'Your answers' }}</h2>
                @foreach($quiz->questions as $qIndex => $question)
                @php
                    $userAnswer = $attempt->answers->firstWhere('question_id', $question->id);
                    $correct = (bool) ($userAnswer?->is_correct);
                @endphp
                <article class="mq-review__item {{ $quiz->show_results ? ($correct ? 'is-correct' : 'is-wrong') : '' }}">
                    <div class="mq-review__q">
                        @if($quiz->show_results)
                        <span aria-hidden="true">{{ $correct ? '✓' : '✕' }}</span>
                        @endif
                        <strong>{{ $qIndex + 1 }}. {{ $question->question }}</strong>
                    </div>
                    <div class="mq-review__a">
                        @if($question->type === 'short_answer')
                            Your answer: {{ $userAnswer->text_answer ?? '—' }}
                        @else
                            Your answer: {{ $userAnswer?->option?->option_text ?? 'Not answered' }}
                            @if($quiz->show_results && ! $correct)
                            <div>Correct: {{ $question->options->firstWhere('is_correct', true)?->option_text ?? '—' }}</div>
                            @endif
                        @endif
                        @if($quiz->show_results && $question->explanation)
                        <div class="mq-review__explain">{{ $question->explanation }}</div>
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
