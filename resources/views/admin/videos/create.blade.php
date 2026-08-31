@extends('admin.layouts.app')
@section('title', 'Add Video')
@section('heading', 'Add Video')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.videos.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Videos</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Video</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new YouTube video to the library.</p>
    </div>
    @include('admin.videos._form', ['video' => $video])
</div>
@endsection
