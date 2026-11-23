@extends('admin.layouts.app')
@section('title', 'Edit Download')
@section('heading', 'Edit Download')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.download-centres.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to Download Centre</a>
            <span class="learn-eyebrow">Utilities · Downloads</span>
            <h2>Edit download</h2>
            <p>Update this downloadable resource.</p>
        </div>
    </div>
    @include('admin.download-centres._form')
</div>
@endsection
