@extends('admin.layouts.app')
@section('title', 'Add Podcast')
@section('heading', 'Add Podcast')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.podcasts.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Podcasts</a>
            <span class="learn-eyebrow">Utilities · Podcasts</span>
            <h2>Podcast</h2>
            <p>Add a new audio or YouTube podcast episode.</p>
        </div>
    </div>
    @include('admin.podcasts._form', ['podcast' => $podcast])
</div>
@endsection
