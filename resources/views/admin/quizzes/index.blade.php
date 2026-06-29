@extends('admin.layouts.app')
@section('title', 'Quizzes & MCQs')
 
@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-900">Quizzes & MCQs</h1>
        <p class="text-sm text-gray-500 mt-1">Test your knowledge, track your progress</p>
    </div>
    <a href="{{ route('admin.quizzes.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition-colors">
        + Create Quiz
    </a>
</div>
 
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($quizzes as $quiz)
    @php $attempted = in_array($quiz->id, $myAttempts); @endphp
    <div class="bg-white border border-gray-100 rounded-2xl p-5 hover:border-indigo-200 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-xl">🎯</div>
            <div class="flex gap-2 items-center">
                @if($attempted)
                <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-medium">Completed</span>
                @endif
                @if($quiz->time_limit)
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">⏱ {{ $quiz->time_limit }}m</span>
                @endif
            </div>
        </div>
        <h3 class="font-semibold text-gray-900 mb-1">{{ $quiz->title }}</h3>
        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $quiz->description ?? 'No description.' }}</p>
        <div class="flex items-center gap-3 text-xs text-gray-400 mb-4">
            <span>{{ $quiz->questions_count }} questions</span>
            <span>·</span>
            <span>Pass: {{ $quiz->pass_score }}%</span>
            <span>·</span>
            <span>by {{ $quiz->creator->name }}</span>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.quizzes.show', $quiz) }}"
               class="flex-1 text-center text-sm font-medium py-2 rounded-xl
                      {{ $attempted ? 'bg-gray-50 text-gray-700 border border-gray-200' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition-colors">
                {{ $attempted ? 'Review' : 'Start Quiz' }}
            </a>
            @if($quiz->created_by === Auth::id())
            <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}"
                  onsubmit="return confirm('Delete this quiz?')">
                @csrf @method('DELETE')
                <button class="px-3 py-2 rounded-xl border border-gray-200 text-gray-400 hover:text-red-500 hover:border-red-200 transition-colors text-sm">
                    🗑
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-20 text-gray-400">
        <div class="text-4xl mb-3">🎯</div>
        <p class="font-medium">No quizzes yet</p>
        <p class="text-sm mt-1">Create the first quiz to get started</p>
    </div>
    @endforelse
</div>
@endsection