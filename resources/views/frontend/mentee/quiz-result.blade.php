@extends('frontend.layouts.app')
@section('title', 'Result — '.$quiz->title)

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;margin-bottom:12px;">
            <a href="{{ route('mentee.quizzes.index') }}" style="color:var(--brand);">← Quizzes</a>
        </div>

        <div class="card" style="max-width:640px;margin:0 auto 20px;padding:28px;text-align:center;">
            <div style="font-size:48px;margin-bottom:10px;">{{ $attempt->passed ? '🎉' : '😔' }}</div>
            <div class="dash-title" style="color:{{ $attempt->passed ? 'var(--success)' : 'var(--error)' }};">
                {{ $attempt->passed ? 'You Passed!' : 'Not Quite' }}
            </div>
            <div class="dash-subtitle" style="margin-bottom:18px;">{{ $quiz->title }}</div>

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px;">
                <div class="card" style="padding:12px;">
                    <div style="font-size:24px;font-weight:800;color:{{ $attempt->passed ? 'var(--success)' : 'var(--error)' }};">{{ (int) $attempt->percentage }}%</div>
                    <div style="font-size:11px;color:var(--text-3);">Your Score</div>
                </div>
                <div class="card" style="padding:12px;">
                    <div style="font-size:24px;font-weight:800;">{{ $attempt->score }}/{{ $attempt->total_marks }}</div>
                    <div style="font-size:11px;color:var(--text-3);">Marks</div>
                </div>
                <div class="card" style="padding:12px;">
                    <div style="font-size:24px;font-weight:800;color:var(--brand);">{{ $quiz->pass_score }}%</div>
                    <div style="font-size:11px;color:var(--text-3);">Pass Mark</div>
                </div>
            </div>

            <div class="progress-bar" style="margin-bottom:18px;">
                <div class="progress-fill" style="width:{{ (int) $attempt->percentage }}%;background:{{ $attempt->passed ? 'var(--success)' : 'var(--error)' }};"></div>
            </div>

            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('mentee.quizzes.show', $quiz) }}" class="btn btn-outline">Retake</a>
                <a href="{{ route('mentee.quizzes.index') }}" class="btn btn-primary">All Quizzes</a>
            </div>
        </div>

        <div class="card" style="max-width:640px;margin:0 auto;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:14px;">Answer Review</h3>
            @foreach($quiz->questions as $qIndex => $question)
            @php
                $userAnswer = $attempt->answers->firstWhere('question_id', $question->id);
                $correct = (bool) ($userAnswer?->is_correct);
            @endphp
            <div style="padding:12px 0;border-bottom:1px solid var(--border);">
                <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:6px;">
                    <span>{{ $correct ? '✅' : '❌' }}</span>
                    <div style="font-size:13px;font-weight:600;">{{ $qIndex + 1 }}. {{ $question->question }}</div>
                </div>
                <div style="font-size:12px;color:var(--text-2);padding-left:28px;">
                    @if($question->type === 'short_answer')
                        Your answer: {{ $userAnswer->text_answer ?? '—' }}
                    @else
                        Your answer: {{ $userAnswer?->option?->option_text ?? '—' }}
                        @unless($correct)
                            · Correct: {{ $question->options->firstWhere('is_correct', true)?->option_text ?? '—' }}
                        @endunless
                    @endif
                    @if($question->explanation)
                    <div style="margin-top:4px;color:var(--text-3);">{{ $question->explanation }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
