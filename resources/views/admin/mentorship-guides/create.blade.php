@extends('admin.layouts.app')
@section('title', 'Create Mentorship Guide')
@section('heading', 'Create Mentorship Guide')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.mentorship-guides.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Mentorship Guides</a>
            <span class="learn-eyebrow">Utilities · Mentorship Guides</span>
            <h2>Mentorship Guide</h2>
            <p>Add a new guide. It will appear as a readable article on the website.</p>
        </div>
    </div>
    @include('admin.mentorship-guides._form', ['guide' => $guide])
</div>
@endsection
