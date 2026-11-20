@extends('admin.layouts.app')
@section('title', 'Create Category')
@section('heading', 'Create Category')

@section('content')
@include('admin.partials.learning-ui')

<div class="learn-page space-y-4 max-w-xl">
    @include('admin.assessments.partials.nav')

    <div class="learn-intro">
        <a href="{{ route('admin.assessment-categories.index') }}" class="text-sm text-slate-400 hover:text-slate-700">← Back to Categories</a>
        <span class="learn-eyebrow mt-3 inline-flex">Learning · New category</span>
        <h2>Create Category</h2>
        <p>Link a category to an assessment before adding questions.</p>
    </div>

    @include('admin.assessments.categories._form', ['category' => $category, 'assessments' => $assessments])
</div>
@endsection