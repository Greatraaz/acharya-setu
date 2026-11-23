@extends('admin.layouts.app')
@section('title', 'Edit Video')
@section('heading', 'Edit Video')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.videos.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Videos</a>
            <span class="learn-eyebrow">Utilities · Videos</span>
            <h2>Video</h2>
            <p>Update video details.</p>
        </div>
    </div>
    @include('admin.videos._form', ['video' => $video])
</div>
@endsection
