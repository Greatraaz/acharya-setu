@extends('admin.layouts.app')
@section('title', 'Add Download')
@section('heading', 'Add Download')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.download-centres.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Download Centre</a>
            <span class="learn-eyebrow">Utilities · Downloads</span>
            <h2>Add download</h2>
            <p>Upload a new resource for the Vedrix Insights download centre.</p>
        </div>
    </div>
    @include('admin.download-centres._form')
</div>
@endsection
