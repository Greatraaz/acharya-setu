@extends('admin.layouts.app')
@section('title', $quiz->title)
 
@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.quizzes.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
    </div>
 
    <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 flex items-center justify-center text-3xl mx-auto mb-4">🎯</div>
        <h1 class="font-display text-2xl font-bold text-gray-900 mb-2">{{ $quiz->title }}</h1>
        @if($quiz->description)
        <p class="text-gray-500 text-sm mb-6">{{ $quiz->description }}</p>
        @endif
 
        <div class="grid grid-cols-3 gap-3 mb-8">
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-2xl font-bold font-display text-indigo-600">{{ $quiz->questions->count() }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Questions</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-2xl font-bold font-display text-amber-500">{{ $quiz->pass_score }}%</p>
                <p class="text-xs text-gray-500 mt-0.5">To Pass</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-3">
                <p class="text-2xl font-bold font-display text-gray-700">{{ $quiz->time_limit ?? '∞' }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $quiz->time_limit ? 'Minutes' : 'No Limit' }}</p>
            </div>
        </div>
 
        @if($attempt && $attempt->completed_at)
        <div class="mb-6 p-4 rounded-xl {{ $attempt->passed ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' }}">
            <p class="font-semibold {{ $attempt->passed ? 'text-green-800' : 'text-red-800' }}">
                {{ $attempt->passed ? '🎉 Passed!' : '❌ Failed' }} — {{ $attempt->percentage }}%
            </p>
            <p class="text-sm {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }} mt-1">
                {{ $attempt->score }}/{{ $attempt->total_marks }} marks
            </p>
            <a href="{{ route('admin.quizzes.result', [$quiz, $attempt]) }}"
               class="inline-block mt-2 text-sm underline {{ $attempt->passed ? 'text-green-700' : 'text-red-700' }}">
                View detailed results →
            </a>
        </div>
        @endif
 
        <form method="POST" action="{{ route('admin.quizzes.attempt', $quiz) }}">
            @csrf
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-xl font-medium hover:bg-indigo-700 transition-colors">
                {{ $attempt ? 'Retake Quiz' : 'Start Quiz' }}
            </button>
        </form>
    </div>
</div>
@endsection