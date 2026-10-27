@extends('admin.layouts.app')
@section('title', 'Create Mentorship Guide')
@section('heading', 'Create Mentorship Guide')

@section('content')
<div class="space-y-4 max-w-5xl">
    <div>
        <a href="{{ route('admin.mentorship-guides.index') }}" class="text-sm text-gray-400 hover:text-gray-600">← Back to Mentorship Guides</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Mentorship Guide</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new guide. It will appear as a readable article on the website.</p>
    </div>
    @include('admin.mentorship-guides._form', ['guide' => $guide])
</div>
@endsection
