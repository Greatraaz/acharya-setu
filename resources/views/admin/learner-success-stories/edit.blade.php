@extends('admin.layouts.app')
@section('title', 'Edit Learner Success Story')
@section('heading', 'Edit Learner Success Story')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.learner-success-stories.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Learner Success Stories</a>
            <span class="learn-eyebrow">Utilities · Success Stories</span>
            <h2>Learner Success Story</h2>
            <p>Update learner success story details.</p>
        </div>
    </div>
    @include('admin.learner-success-stories._form', ['story' => $story])
</div>
@endsection
