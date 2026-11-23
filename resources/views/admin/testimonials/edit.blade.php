@extends('admin.layouts.app')
@section('title', 'Edit Testimonial')
@section('heading', 'Edit Testimonial')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.testimonials.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Testimonials</a>
            <span class="learn-eyebrow">Utilities · Testimonials</span>
            <h2>Testimonial</h2>
            <p>Update testimonial details.</p>
        </div>
    </div>
    @include('admin.testimonials._form', ['testimonial' => $testimonial])
</div>
@endsection
