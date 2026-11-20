@extends('admin.layouts.app')
@section('title', 'Create Assessment')
@section('heading', 'Create Assessment')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="learn-intro">
        <a href="{{ route('admin.assessments.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back to Assessments</a>
        <span class="learn-eyebrow mt-3 inline-flex">Learning · New assessment</span>
        <h2>Create Assessment</h2>
        <p>Add name, media, instructions, and four score bands.</p>
    </div>

    @include('admin.assessments.partials.form', [
        'assessment' => $assessment,
        'bands' => $bands,
        'assigneeMentees' => $assigneeMentees ?? collect(),
    ])
</div>
@endsection