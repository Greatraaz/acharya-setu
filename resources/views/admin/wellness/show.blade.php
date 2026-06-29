@extends('admin.layouts.app')
@section('title', $wellnessSurvey->title)
 
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.wellness.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
    </div>
 
    <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-2xl">🧘</div>
            <div>
                <h1 class="font-display text-xl font-bold text-gray-900">{{ $wellnessSurvey->title }}</h1>
                @if($wellnessSurvey->description)
                <p class="text-sm text-gray-500 mt-1">{{ $wellnessSurvey->description }}</p>
                @endif
                <p class="text-xs text-gray-400 mt-2">{{ $wellnessSurvey->questions->count() }} questions · by {{ $wellnessSurvey->creator->name }}</p>
            </div>
        </div>
    </div>
 
    @if($hasResponded)
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 text-center">
        <div class="text-3xl mb-2">✅</div>
        <p class="font-semibold text-green-800">You've already completed this survey</p>
        <a href="{{ route('admin.wellness.results', $wellnessSurvey) }}"
           class="inline-block mt-3 text-sm text-green-700 underline">View results →</a>
    </div>
    @else
    <form method="POST" action="{{ route('admin.wellness.respond', $wellnessSurvey) }}">
        @csrf
        <div class="space-y-4">
            @foreach($wellnessSurvey->questions as $question)
            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <p class="font-medium text-gray-800 mb-3">
                    {{ $loop->iteration }}. {{ $question->question }}
                    @if($question->required)
                    <span class="text-amber-500">*</span>
                    @endif
                </p>
 
                @if($question->type === 'scale')
                <div class="space-y-2">
                    <div class="flex justify-between text-xs text-gray-400 px-1">
                        <span>1 — Poor</span>
                        <span>10 — Excellent</span>
                    </div>
                    <div class="flex gap-2 flex-wrap">
                        @for($v = 1; $v <= 10; $v++)
                        <label class="cursor-pointer">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $v }}"
                                   class="sr-only peer" {{ $question->required ? 'required' : '' }}>
                            <span class="w-9 h-9 rounded-lg border border-gray-200 flex items-center justify-center text-sm font-medium text-gray-600
                                         peer-checked:bg-amber-500 peer-checked:text-white peer-checked:border-amber-500 hover:border-amber-300 transition-colors cursor-pointer">
                                {{ $v }}
                            </span>
                        </label>
                        @endfor
                    </div>
                </div>
 
                @elseif($question->type === 'text')
                <textarea name="answers[{{ $question->id }}]" rows="3"
                          placeholder="Share your thoughts..."
                          {{ $question->required ? 'required' : '' }}
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"></textarea>
 
                @elseif($question->type === 'yes_no')
                <div class="flex gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="answers[{{ $question->id }}]" value="Yes"
                               class="sr-only peer" {{ $question->required ? 'required' : '' }}>
                        <span class="px-6 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600
                                     peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-500 hover:border-green-300 transition-colors block">
                            Yes
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="answers[{{ $question->id }}]" value="No"
                               class="sr-only peer" {{ $question->required ? 'required' : '' }}>
                        <span class="px-6 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600
                                     peer-checked:bg-red-400 peer-checked:text-white peer-checked:border-red-400 hover:border-red-300 transition-colors block">
                            No
                        </span>
                    </label>
                </div>
 
                @elseif($question->type === 'multiple_choice')
                <div class="space-y-2">
                    @foreach($question->options ?? [] as $option)
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option }}"
                               class="accent-amber-500" {{ $question->required ? 'required' : '' }}>
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $option }}</span>
                    </label>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
 
        <button type="submit"
                class="w-full mt-6 bg-amber-500 text-white py-3 rounded-xl font-medium hover:bg-amber-600 transition-colors">
            Submit Response
        </button>
    </form>
    @endif
</div>
@endsection