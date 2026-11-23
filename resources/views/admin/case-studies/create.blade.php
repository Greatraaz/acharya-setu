@extends('admin.layouts.app')
@section('title', 'Create Case Study')
@section('heading', 'Create Case Study')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.case-studies.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Case Studies</a>
            <span class="learn-eyebrow">Utilities · Case Studies</span>
            <h2>Case Study</h2>
            <p>Add a new mentorship case study.</p>
        </div>
    </div>
    @include('admin.case-studies._form', ['caseStudy' => $caseStudy])
</div>
@endsection
