@extends('admin.layouts.app')
@section('title', 'Edit Podcast')
@section('heading', 'Edit Podcast')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.podcasts.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Podcasts</a>
            <span class="learn-eyebrow">Utilities · Podcasts</span>
            <h2>Podcast</h2>
            <p>Update podcast episode details.</p>
        </div>
    </div>
    @include('admin.podcasts._form', ['podcast' => $podcast])
</div>
@endsection
