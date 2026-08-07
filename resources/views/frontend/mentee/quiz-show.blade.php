@extends('frontend.layouts.app')
@section('title', $quiz->title.' — Quiz')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;margin-bottom:12px;">
            <a href="{{ route('mentee.quizzes.index') }}" style="color:var(--brand);">← Quizzes</a>
        </div>

        <div class="card" style="max-width:560px;margin:0 auto;padding:28px;text-align:center;">
            <div style="font-size:40px;margin-bottom:12px;">🧠</div>
            <div class="dash-title" style="margin-bottom:8px;">{{ $quiz->title }}</div>
            @if($quiz->description)
            <p style="font-size:13px;color:var(--text-2);margin-bottom:20px;">{{ $quiz->description }}</p>
            @endif

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:22px;">
                <div class="card" style="padding:12px;">
                    <div style="font-size:22px;font-weight:800;color:var(--brand);">{{ $quiz->questions->count() }}</div>
                    <div style="font-size:11px;color:var(--text-3);">Questions</div>
                </div>
                <div class="card" style="padding:12px;">
                    <div style="font-size:22px;font-weight:800;">{{ $quiz->pass_score }}%</div>
                    <div style="font-size:11px;color:var(--text-3);">To Pass</div>
                </div>
                <div class="card" style="padding:12px;">
                    <div style="font-size:22px;font-weight:800;">{{ $quiz->time_limit ?? '∞' }}</div>
                    <div style="font-size:11px;color:var(--text-3);">{{ $quiz->time_limit ? 'Minutes' : 'No limit' }}</div>
                </div>
            </div>

            @if($attempt && $attempt->completed_at)
            <div class="alert {{ $attempt->passed ? 'alert-success' : 'alert-error' }}" style="margin-bottom:16px;text-align:left;">
                <span class="alert-icon">{{ $attempt->passed ? '🎉' : '❌' }}</span>
                <div style="font-size:13px;">
                    <strong>{{ $attempt->passed ? 'Passed' : 'Not passed' }} — {{ (int) $attempt->percentage }}%</strong>
                    <div style="margin-top:2px;">{{ $attempt->score }}/{{ $attempt->total_marks }} marks</div>
                    @if($quiz->show_results)
                    <a href="{{ route('mentee.quizzes.result', [$quiz, $attempt]) }}" style="color:inherit;text-decoration:underline;">View results →</a>
                    @endif
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('mentee.quizzes.attempt', $quiz) }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
                    {{ $attempt ? 'Retake Quiz' : 'Start Quiz' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
