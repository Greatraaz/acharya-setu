@extends('admin.layouts.app')
@section('title', 'Create Blog')
@section('heading', 'Create Blog')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.blogs.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Blogs</a>
            <span class="learn-eyebrow">Utilities · Blogs</span>
            <h2>Blog</h2>
            <p>Add a new blog post.</p>
        </div>
    </div>
    @include('admin.blogs._form', ['blog' => $blog])
</div>
@endsection
