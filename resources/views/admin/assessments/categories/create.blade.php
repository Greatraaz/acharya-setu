@extends('admin.layouts.app')
@section('title', 'Create Category')
@section('heading', 'Create Category')

@section('content')
<div class="space-y-4">
    @include('admin.assessments.partials.nav')
    <div>
        <a href="{{ route('admin.assessment-categories.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Categories</a>
        <h1 class="font-display text-2xl font-bold text-gray-900 mt-2">Create Category</h1>
    </div>
    @include('admin.assessments.categories._form', ['category' => $category, 'assessments' => $assessments])
</div>
@endsection
