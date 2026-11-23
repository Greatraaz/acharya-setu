@extends('admin.layouts.app')
@section('title', 'Edit '.$meta['singular'])
@section('heading', 'Edit '.$meta['singular'])

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route($meta['admin'].'.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to {{ $meta['label'] }}</a>
            <span class="learn-eyebrow">Utilities · {{ $meta['label'] }}</span>
            <h2>Edit {{ $meta['singular'] }}</h2>
            <p>Update {{ strtolower($meta['singular']) }} details.</p>
        </div>
    </div>
    @include('admin.gated-insights._form', ['meta' => $meta, 'item' => $item])
</div>
@endsection
