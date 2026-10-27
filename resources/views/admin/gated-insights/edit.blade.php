@extends('admin.layouts.app')
@section('title', 'Edit '.$meta['singular'])
@section('heading', 'Edit '.$meta['singular'])

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route($meta['admin'].'.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to {{ $meta['label'] }}</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit {{ $meta['singular'] }}</h1>
        <p class="text-sm text-gray-500 mt-1">Update {{ strtolower($meta['singular']) }} details.</p>
    </div>
    @include('admin.gated-insights._form', ['meta' => $meta, 'item' => $item])
</div>
@endsection
