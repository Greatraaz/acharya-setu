@extends('admin.layouts.app')
@section('title', 'Create '.$meta['singular'])
@section('heading', 'Create '.$meta['singular'])

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route($meta['admin'].'.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to {{ $meta['label'] }}</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">{{ $meta['singular'] }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            Add a new {{ strtolower($meta['singular']) }}.
            @if(!empty($meta['gated']))
                Visitors fill a form before they can download it.
            @else
                It will appear as a readable article on the website.
            @endif
        </p>
    </div>
    @include('admin.gated-insights._form', ['meta' => $meta, 'item' => $item])
</div>
@endsection
