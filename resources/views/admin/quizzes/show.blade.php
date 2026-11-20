@extends('admin.layouts.app')
@section('title', $quiz->title)
@section('heading', 'Quiz details')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page max-w-3xl mx-auto space-y-5">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back to Quizzes</a>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('admin.quizzes.toggle-publish', $quiz) }}">
                @csrf
                <button type="submit" class="learn-btn-ghost text-sm">
                    {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm('Delete this quiz?')">
                @csrf @method('DELETE')
                <button type="submit" class="learn-btn-ghost text-sm text-red-500">Delete</button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="learn-intro">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 flex items-center justify-center text-3xl shrink-0">🎯</div>
            <div class="min-w-0">
                <span class="learn-eyebrow">{{ $quiz->is_published ? 'Published' : 'Draft' }}</span>
                <h2 class="!mt-2">{{ $quiz->title }}</h2>
                @if($quiz->description)
                <p>{{ $quiz->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['Questions', $quiz->questions->count()],
            ['Pass score', $quiz->pass_score.'%'],
            ['Time', $quiz->time_limit ? $quiz->time_limit.'m' : '∞'],
            ['Attempts', $attemptStats['total']],
        ] as [$label, $value])
        <div class="learn-stat">
            <div class="learn-stat__value">{{ $value }}</div>
            <div class="learn-stat__label">{{ $label }}</div>
        </div>
        @endforeach
    </div>

    <div class="learn-panel p-5">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h3 class="font-bold text-slate-900">Questions preview</h3>
            <span class="text-xs text-slate-400">{{ $attemptStats['passed'] }} passed · avg {{ $attemptStats['avg'] }}%</span>
        </div>
        <div class="space-y-3">
            @foreach($quiz->questions as $i => $question)
            <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                <div class="text-xs font-semibold text-amber-700 mb-1">Q{{ $i + 1 }} · {{ strtoupper(str_replace('_', ' ', $question->type)) }} · {{ $question->marks }} mark{{ $question->marks === 1 ? '' : 's' }}</div>
                <div class="text-sm font-semibold text-slate-900">{{ $question->question }}</div>
                @if($question->options->isNotEmpty())
                <ul class="mt-2 space-y-1">
                    @foreach($question->options as $opt)
                    <li class="text-xs {{ $opt->is_correct ? 'text-emerald-700 font-semibold' : 'text-slate-500' }}">
                        {{ $opt->is_correct ? '✓' : '•' }} {{ $opt->option_text }}
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="learn-panel p-5">
        <h3 class="font-bold text-slate-900 mb-2">Preview as admin</h3>
        <p class="text-sm text-slate-500 mb-4">Take the quiz yourself to verify scoring. This does not affect mentee results.</p>
        @if($attempt && $attempt->completed_at)
        <div class="mb-4 p-4 rounded-xl {{ $attempt->passed ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' }}">
            <p class="font-semibold {{ $attempt->passed ? 'text-green-800' : 'text-red-800' }}">
                Last attempt: {{ $attempt->passed ? 'Passed' : 'Failed' }} — {{ $attempt->percentage }}%
            </p>
            <a href="{{ route('admin.quizzes.result', [$quiz, $attempt]) }}" class="inline-block mt-1 text-sm underline {{ $attempt->passed ? 'text-green-700' : 'text-red-700' }}">
                View detailed results →
            </a>
        </div>
        @endif
        <form method="POST" action="{{ route('admin.quizzes.attempt', $quiz) }}">
            @csrf
            <button type="submit" class="learn-btn-primary w-full !py-3">
                {{ $attempt && $attempt->completed_at ? 'Retake preview' : 'Start preview' }}
            </button>
        </form>
    </div>
</div>
@endsection
