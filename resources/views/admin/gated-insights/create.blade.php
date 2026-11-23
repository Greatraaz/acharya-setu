@extends('admin.layouts.app')
@section('title', 'Create '.$meta['singular'])
@section('heading', 'Create '.$meta['singular'])

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route($meta['admin'].'.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to {{ $meta['label'] }}</a>
            <span class="learn-eyebrow">Utilities · {{ $meta['label'] }}</span>
            <h2>{{ $meta['singular'] }}</h2>
            <p>Add a new {{ strtolower($meta['singular']) }}.
            @if(!empty($meta['gated']))
                Visitors fill a form before they can download it.
            @else
                It will appear as a readable article on the website.
            @endif</p>
        </div>
    </div>
    @include('admin.gated-insights._form', ['meta' => $meta, 'item' => $item])
</div>
@endsection
