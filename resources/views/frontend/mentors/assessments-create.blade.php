@extends('frontend.layouts.app')
@section('title', 'Create Assessment — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <a href="{{ route('mentor.assessments.index') }}" class="assess-back">← Back to Assessments</a>
        <div class="dash-header">
            <div>
                <div class="dash-title">Create Assessment</div>
                <div class="dash-subtitle">Add name, media, instructions, and four score bands.</div>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            Please fix the highlighted fields and try again.
        </div>
        @endif

        @include('frontend.mentors.partials.assessment-form', [
            'assessment' => $assessment,
            'bands' => $bands ?? [],
            'formAction' => route('mentor.assessments.store'),
        ])
    </div>
</div>
@endsection
