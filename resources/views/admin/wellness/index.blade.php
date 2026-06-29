@extends('admin.layouts.app')
@section('title', 'Wellness Surveys')
 
@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="font-display text-2xl font-bold text-gray-900">Wellness Surveys</h1>
        <p class="text-sm text-gray-500 mt-1">Help us understand team wellbeing and mental health</p>
    </div>
    <a href="{{ route('admin.wellness.create') }}"
       class="inline-flex items-center gap-2 bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-600 transition-colors">
        + Create Survey
    </a>
</div>
 
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($surveys as $survey)
    <div class="bg-white border border-gray-100 rounded-2xl p-5 hover:border-amber-200 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-xl">🧘</div>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                {{ $survey->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $survey->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <h3 class="font-semibold text-gray-900 mb-1">{{ $survey->title }}</h3>
        <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $survey->description ?? 'No description.' }}</p>
        <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
            <span>{{ $survey->responses_count }} responses</span>
            @if($survey->expires_at)
            <span>Expires {{ $survey->expires_at->format('M d') }}</span>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.wellness.show', $survey) }}"
               class="flex-1 text-center text-sm font-medium py-2 rounded-xl border border-amber-200 text-amber-700 hover:bg-amber-50 transition-colors">
                {{ $survey->hasResponded(Auth::user()) ? 'View' : 'Take Survey' }}
            </a>
            <a href="{{ route('admin.wellness.results', $survey) }}"
               class="flex-1 text-center text-sm font-medium py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                Results
            </a>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-20 text-gray-400">
        <div class="text-4xl mb-3">🧘</div>
        <p class="font-medium">No surveys yet</p>
        <p class="text-sm mt-1">Create a wellness survey to get started</p>
    </div>
    @endforelse
</div>
@endsection