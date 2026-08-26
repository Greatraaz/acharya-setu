@extends('admin.layouts.app')
@section('title', 'Edit Blog')
@section('heading', 'Edit Blog')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.blogs.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Blogs</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit Blog</h1>
        <p class="text-sm text-gray-500 mt-1">Update blog post details.</p>
    </div>
    @include('admin.blogs._form', ['blog' => $blog])
</div>
@endsection
