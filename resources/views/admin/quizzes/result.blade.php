@extends('admin.layouts.app')
@section('title', 'Result — ' . $quiz->title)
 
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Quizzes</a>
    </div>
 
    {{-- Result Card --}}
    <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center mb-6">
        <div class="text-5xl mb-4">{{ $attempt->passed ? '🎉' : '😔' }}</div>
        <h1 class="font-display text-3xl font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-500' }} mb-1">
            {{ $attempt->passed ? 'You Passed!' : 'Not Quite' }}
        </h1>
        <p class="text-gray-500 mb-6">{{ $quiz->title }}</p>
 
        <div class="grid grid-cols-3 gap-3 mb-6">
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-3xl font-bold font-display {{ $attempt->passed ? 'text-green-600' : 'text-red-500' }}">
                    {{ $attempt->percentage }}%
                </p>
                <p class="text-xs text-gray-500 mt-1">Your Score</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-3xl font-bold font-display text-gray-700">
                    {{ $attempt->score }}/{{ $attempt->total_marks }}
                </p>
                <p class="text-xs text-gray-500 mt-1">Marks</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-3xl font-bold font-display text-indigo-600">{{ $quiz->pass_score }}%</p>
                <p class="text-xs text-gray-500 mt-1">Pass Mark</p>
            </div>
        </div>
 
        <div class="bg-gray-100 rounded-full h-3 mb-6">
            <div class="h-3 rounded-full {{ $attempt->passed ? 'bg-green-500' : 'bg-red-400' }}"
                 style="width: {{ $attempt->percentage }}%"></div>
        </div>
 
        <div class="flex gap-3">
            <a href="{{ route('admin.quizzes.show', $quiz) }}"
               class="flex-1 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                Retake Quiz
            </a>
            <a href="{{ route('admin.quizzes.index') }}"
               class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition-colors">
                All Quizzes
            </a>
        </div>
    </div>
 
    {{-- Answer Review --}}
    <h2 class="font-semibold text-gray-800 mb-3">Answer Review</h2>
    <div class="space-y-3">
        @foreach($quiz->questions as $qIndex => $question)
        @php
            $userAnswer = $attempt->answers->firstWhere('question_id', $question->id);
            $correct    = $userAnswer?->is_correct;
        @endphp
        <div class="bg-white border rounded-2xl p-4 {{ $correct ? 'border-green-100' : 'border-red-100' }}">
            <div class="flex items-start gap-3">
                <span class="text-lg flex-shrink-0">{{ $correct ? '✅' : '❌' }}</span>
                <div class="flex-1">
                    <p class="font-medium text-gray-800 text-sm mb-2">{{ $qIndex + 1 }}. {{ $question->question }}</p>
 
                    @if($question->type !== 'short_answer')
                    <div class="space-y-1">
                        @foreach($question->options as $opt)
                        <div class="flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg
                            {{ $opt->is_correct ? 'bg-green-50 text-green-800' : ($userAnswer?->option_id === $opt->id && !$opt->is_correct ? 'bg-red-50 text-red-700' : 'text-gray-600') }}">
                            @if($opt->is_correct)
                            <span class="text-green-500 font-bold">✓</span>
                            @elseif($userAnswer?->option_id === $opt->id)
                            <span class="text-red-400 font-bold">✗</span>
                            @else
                            <span class="w-3.5"></span>
                            @endif
                            {{ $opt->option_text }}
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                        Your answer: {{ $userAnswer?->text_answer ?? '—' }}
                    </div>
                    @endif
 
                    @if($question->explanation)
                    <div class="mt-2 text-xs text-indigo-700 bg-indigo-50 rounded-lg px-3 py-1.5">
                        💡 {{ $question->explanation }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection