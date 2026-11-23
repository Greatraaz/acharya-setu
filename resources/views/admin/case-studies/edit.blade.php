@extends('admin.layouts.app')
@section('title', 'Edit Case Study')
@section('heading', 'Edit Case Study')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.case-studies.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Case Studies</a>
            <span class="learn-eyebrow">Utilities · Case Studies</span>
            <h2>Edit Case Study</h2>
            <p>Update case study details.</p>
        </div>
    </div>
    @include('admin.case-studies._form', ['caseStudy' => $caseStudy])
</div>
@endsection
