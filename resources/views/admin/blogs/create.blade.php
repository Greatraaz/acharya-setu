@extends('admin.layouts.app')
@section('title', 'Create Blog')
@section('heading', 'Create Blog')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.blogs.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Blogs</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Blog</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new blog post.</p>
    </div>
    @include('admin.blogs._form', ['blog' => $blog])
</div>
@endsection
