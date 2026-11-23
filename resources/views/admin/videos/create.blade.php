@extends('admin.layouts.app')
@section('title', 'Add Video')
@section('heading', 'Add Video')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.videos.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Videos</a>
            <span class="learn-eyebrow">Utilities · Videos</span>
            <h2>Video</h2>
            <p>Add a new YouTube video to the library.</p>
        </div>
    </div>
    @include('admin.videos._form', ['video' => $video])
</div>
@endsection
