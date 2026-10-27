@extends('admin.layouts.app')
@section('title', 'Edit Learner Success Story')
@section('heading', 'Edit Learner Success Story')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.learner-success-stories.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Learner Success Stories</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Learner Success Story</h1>
        <p class="text-sm text-gray-500 mt-1">Update learner success story details.</p>
    </div>
    @include('admin.learner-success-stories._form', ['story' => $story])
</div>
@endsection
