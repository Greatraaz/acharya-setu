@extends('frontend.layouts.app')
@section('title', 'Add Question — Vedrix Mentor')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <a href="{{ route('mentor.assessment-questions.index') }}" class="assess-back">← Back to Questions</a>
        <div class="dash-header">
            <div>
                <div class="dash-title">Add Question</div>
                <div class="dash-subtitle">Assign this question to an assessment and customise the answer labels.</div>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">Please fix the highlighted fields and try again.</div>
        @endif

        @include('frontend.mentors.assessment-questions._form', [
            'question'    => $question,
            'assessments' => $assessments,
            'formAction'  => route('mentor.assessment-questions.store'),
        ])
    </div>
</div>
@endsection
