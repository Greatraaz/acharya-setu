@extends('admin.layouts.app')
@section('title', 'Create Case Study')
@section('heading', 'Create Case Study')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.case-studies.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Case Studies</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Case Study</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new mentorship case study.</p>
    </div>
    @include('admin.case-studies._form', ['caseStudy' => $caseStudy])
</div>
@endsection
