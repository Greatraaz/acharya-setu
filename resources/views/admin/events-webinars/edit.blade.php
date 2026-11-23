@extends('admin.layouts.app')
@section('title', 'Edit Event / Webinar')
@section('heading', 'Edit Event / Webinar')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.events-webinars.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Events &amp; Webinars</a>
            <span class="learn-eyebrow">Utilities · Events</span>
            <h2>Edit event / webinar</h2>
            <p>Update this webinar or event listing.</p>
        </div>
    </div>
    @include('admin.events-webinars._form')
</div>
@endsection
