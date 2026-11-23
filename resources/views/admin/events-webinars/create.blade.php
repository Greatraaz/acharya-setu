@extends('admin.layouts.app')
@section('title', 'Add Event / Webinar')
@section('heading', 'Add Event / Webinar')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.events-webinars.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Events &amp; Webinars</a>
            <span class="learn-eyebrow">Utilities · Events</span>
            <h2>Add event / webinar</h2>
            <p>Create a new webinar or live event for the Insights calendar.</p>
        </div>
    </div>
    @include('admin.events-webinars._form')
</div>
@endsection
