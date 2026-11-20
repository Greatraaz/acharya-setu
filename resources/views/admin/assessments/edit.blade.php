@extends('admin.layouts.app')
@section('title', 'Edit Assessment')
@section('heading', 'Edit Assessment')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-4">
    @include('admin.assessments.partials.nav')

    <div class="learn-intro">
        <a href="{{ route('admin.assessments.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back to Assessments</a>
        <span class="learn-eyebrow mt-3 inline-flex">Learning · Edit assessment</span>
        <h2>Edit Assessment</h2>
        <p>{{ $assessment->title }}</p>
    </div>

    @include('admin.assessments.partials.form', [
        'assessment' => $assessment,
        'bands' => $bands,
        'assigneeMentees' => $assigneeMentees ?? collect(),
    ])
</div>
@endsection