@extends('frontend.layouts.app')
@section('title', 'Edit Assessment — Vedrix Mentor')

@section('content')
<div class="dash-layout">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <div style="font-size:12px;margin-bottom:12px;">
            <a href="{{ route('mentor.assessments.index') }}" style="color:var(--brand);">← Assessments</a>
        </div>
        <div class="dash-header">
            <div class="dash-title">Edit Assessment</div>
            <div class="dash-subtitle">{{ $assessment->title }}</div>
        </div>

        <div class="card" style="padding:20px;">
            @include('admin.assessments.partials.form', [
                'assessment' => $assessment,
                'formAction' => route('mentor.assessments.update', $assessment),
            ])
        </div>
    </div>
</div>
@endsection
