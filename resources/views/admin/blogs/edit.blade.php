@extends('admin.layouts.app')
@section('title', 'Edit Blog')
@section('heading', 'Edit Blog')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.blogs.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Blogs</a>
            <span class="learn-eyebrow">Utilities · Blogs</span>
            <h2>Edit Blog</h2>
            <p>Update blog post details.</p>
        </div>
    </div>
    @include('admin.blogs._form', ['blog' => $blog])
</div>
@endsection
