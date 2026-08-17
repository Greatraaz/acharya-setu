@extends('admin.layouts.app')
@section('title', 'Create Assessment')
@section('heading', 'Create Assessment')

@section('content')
<div class="space-y-4">
    @include('admin.assessments.partials.nav')
    <div>
        <a href="{{ route('admin.assessments.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Assessments</a>
        <h1 class="font-display text-2xl font-bold text-gray-900 mt-2">Create Assessment</h1>
        <p class="text-sm text-gray-500 mt-1">Add name, media, instructions, and four score bands.</p>
    </div>
    @include('admin.assessments.partials.form', ['assessment' => $assessment, 'bands' => $bands])
</div>
@endsection
