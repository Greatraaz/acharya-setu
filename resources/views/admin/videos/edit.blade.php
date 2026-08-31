@extends('admin.layouts.app')
@section('title', 'Edit Video')
@section('heading', 'Edit Video')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.videos.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Videos</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Video</h1>
        <p class="text-sm text-gray-500 mt-1">Update video details.</p>
    </div>
    @include('admin.videos._form', ['video' => $video])
</div>
@endsection
