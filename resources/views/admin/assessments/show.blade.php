@extends('admin.layouts.app')
@section('title', $assessment->title)
@section('heading', 'Assessment Details')

@section('content')
<div class="space-y-6">
    @include('admin.assessments.partials.nav')

    <div class="flex items-center justify-between flex-wrap gap-3">
        <a href="{{ route('admin.assessments.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Assessments</a>
        <a href="{{ route('admin.assessments.edit', $assessment) }}"
           class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50">Edit</a>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            @if($assessment->iconUrl())
                <img src="{{ $assessment->iconUrl() }}" class="w-14 h-14 rounded-xl object-cover" alt="">
            @endif
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ $assessment->title }}</h1>
                @if($assessment->description)
                <p class="text-sm text-gray-500 mt-2 whitespace-pre-wrap">{{ $assessment->description }}</p>
                @endif
            </div>
            @if($assessment->imageUrl())
                <img src="{{ $assessment->imageUrl() }}" class="w-28 h-20 rounded-xl object-cover" alt="">
            @endif
        </div>
        @if($assessment->instructions)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 mb-1">Instructions</h3>
            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $assessment->instructions }}</p>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @forelse($assessment->scoreBands as $band)
        <div class="bg-white border border-gray-200 rounded-2xl p-5">
            <div class="text-xs font-bold uppercase tracking-wider text-orange-600 mb-1">Score Band {{ $band->band_index }}</div>
            <div class="font-semibold text-gray-900">{{ $band->heading ?: 'Untitled' }}</div>
            <div class="text-sm text-gray-500 mt-1">From {{ $band->range_from }} to {{ $band->range_to }}</div>
            @if($band->description)
            <div class="prose prose-sm mt-3 text-gray-600">{!! $band->description !!}</div>
            @endif
        </div>
        @empty
        <p class="text-sm text-gray-500">No score bands yet.</p>
        @endforelse
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">Categories & Questions</h2>
            <a href="{{ route('admin.assessment-questions.create') }}" class="text-sm text-orange-600 font-medium">+ Add Question</a>
        </div>
        @forelse($assessment->categories as $category)
        <div class="mb-5 last:mb-0">
            <div class="text-sm font-semibold text-gray-800 mb-2">{{ $category->name }}</div>
            @forelse($category->questions as $q)
            <div class="text-sm text-gray-600 py-2 border-b border-gray-50">{{ $q->question }}</div>
            @empty
            <p class="text-xs text-gray-400">No questions in this category.</p>
            @endforelse
        </div>
        @empty
        <p class="text-sm text-gray-500">No categories yet. Create a category, then add questions.</p>
        @endforelse
    </div>
</div>
@endsection
