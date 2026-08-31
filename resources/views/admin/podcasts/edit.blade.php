@extends('admin.layouts.app')
@section('title', 'Edit Podcast')
@section('heading', 'Edit Podcast')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.podcasts.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Podcasts</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Podcast</h1>
        <p class="text-sm text-gray-500 mt-1">Update podcast episode details.</p>
    </div>
    @include('admin.podcasts._form', ['podcast' => $podcast])
</div>
@endsection
