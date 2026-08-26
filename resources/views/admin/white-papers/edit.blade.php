@extends('admin.layouts.app')
@section('title', 'Edit White Paper')
@section('heading', 'Edit White Paper')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.white-papers.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to White Papers</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Edit White Paper</h1>
        <p class="text-sm text-gray-500 mt-1">Update white paper details.</p>
    </div>
    @include('admin.white-papers._form', ['whitePaper' => $whitePaper])
</div>
@endsection
