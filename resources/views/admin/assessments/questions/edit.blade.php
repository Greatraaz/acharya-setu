@extends('admin.layouts.app')
@section('title', 'Edit Question')
@section('heading', 'Edit Question')

@section('content')
<div class="space-y-4">
    <div>
        <a href="{{ route('admin.assessment-questions.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Questions</a>
        <h1 class="font-display text-2xl font-bold text-gray-900 mt-2">Edit Question</h1>
    </div>
    @include('admin.assessments.questions._form', ['question' => $question, 'assessments' => $assessments])
</div>
@endsection
