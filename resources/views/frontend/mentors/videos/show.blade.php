@extends('frontend.layouts.app')
@section('title', $item->name.' — Videos')

@section('content')
<div class="dash-layout assess-page">
    @include('frontend.mentors.partials.sidebar')

    <div class="dash-content">
        <a href="{{ route('mentor.videos.index') }}" class="assess-back">← Back to Videos</a>

        <div class="dash-header dash-header--actions">
            <div class="dash-header__main">
                <div class="dash-title">{{ $item->name }}</div>
                <div class="dash-subtitle">
                    @if($item->is_active)
                        <span class="assess-badge is-active">Active</span>
                    @else
                        <span class="assess-badge is-inactive">Inactive</span>
                    @endif
                    · {{ $item->files->count() }} video{{ $item->files->count() === 1 ? '' : 's' }}
                </div>
            </div>
            <div class="dash-header__actions">
                <a href="{{ route('mentor.videos.edit', $item->id) }}" class="btn btn-outline btn-sm">Edit</a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        @if($item->description)
        <p style="font-size:14px;color:var(--text-2);margin:0 0 18px;line-height:1.5;">{{ $item->description }}</p>
        @endif

        <div style="display:grid;gap:16px;">
            @forelse($item->files as $file)
            <div class="card" style="padding:14px;">
                <div style="font-size:13px;font-weight:700;margin-bottom:10px;">{{ $file->file_name }}</div>
                <video controls preload="metadata" style="width:100%;max-height:420px;border-radius:12px;background:#000;">
                    <source src="{{ $file->video_url }}" type="video/mp4">
                </video>
            </div>
            @empty
            <div class="card" style="padding:28px;text-align:center;color:var(--text-3);">No video files in this collection.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
