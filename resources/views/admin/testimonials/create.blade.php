@extends('admin.layouts.app')
@section('title', 'Add Testimonial')
@section('heading', 'Add Testimonial')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.testimonials.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Testimonials</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Testimonial</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new client or mentee testimonial.</p>
    </div>
    @include('admin.testimonials._form', ['testimonial' => $testimonial])
</div>
@endsection
