@extends('admin.layouts.app')
@section('title', 'Results — ' . $wellnessSurvey->title)
 
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.wellness.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back</a>
        @if($wellnessSurvey->created_by === Auth::id())
        <form method="POST" action="{{ route('admin.wellness.destroy', $wellnessSurvey) }}"
              onsubmit="return confirm('Delete this survey?')">
            @csrf @method('DELETE')
            <button class="text-sm text-red-400 hover:text-red-600">Delete Survey</button>
        </form>
        @endif
    </div>
 
    <div class="bg-white border border-gray-100 rounded-2xl p-6 mb-6">
        <h1 class="font-display text-xl font-bold text-gray-900">{{ $wellnessSurvey->title }}</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $wellnessSurvey->responses_count }} total responses</p>
    </div>
 
    <div class="space-y-4">
        @foreach($stats as $i => $stat)
        <div class="bg-white border border-gray-100 rounded-2xl p-5">
            <p class="font-medium text-gray-800 mb-4">{{ $i + 1 }}. {{ $stat['question'] }}</p>
            <p class="text-xs text-gray-400 mb-3">{{ $stat['count'] }} responses</p>
 
            @if($stat['type'] === 'scale')
            <div class="flex items-end gap-3">
                <div class="text-4xl font-bold font-display text-amber-500">{{ $stat['average'] }}</div>
                <div class="text-sm text-gray-400 mb-1">/ 10 average score</div>
            </div>
            <div class="mt-3 bg-gray-100 rounded-full h-2">
                <div class="bg-amber-400 h-2 rounded-full" style="width: {{ ($stat['average'] / 10) * 100 }}%"></div>
            </div>
 
            @else
            <div class="space-y-2">
                @foreach($stat['answers'] as $answer => $count)
                @php $pct = $stat['count'] > 0 ? round(($count / $stat['count']) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-700">{{ $answer }}</span>
                        <span class="text-gray-400 font-medium">{{ $count }} ({{ $pct }}%)</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-1.5">
                        <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection