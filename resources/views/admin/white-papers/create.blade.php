@extends('admin.layouts.app')
@section('title', 'Create White Paper')
@section('heading', 'Create White Paper')

@section('content')
<div class="admin-page space-y-5 max-w-5xl">
    <div class="learn-intro">
        <div class="flex flex-col gap-3">
            <a href="{{ route('admin.white-papers.index') }}" class="text-sm font-semibold text-amber-700 hover:underline w-fit">← Back to White Papers</a>
            <span class="learn-eyebrow">Utilities · White Papers</span>
            <h2>White Paper</h2>
            <p>Add a new white paper.</p>
        </div>
    </div>
    @include('admin.white-papers._form', ['whitePaper' => $whitePaper])
</div>
@endsection
