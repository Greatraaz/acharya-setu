@extends('frontend.layouts.app')
@section('title', $item->name.' — Mentor Videos')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <a href="{{ route('mentee.mentor-videos.index') }}" class="assess-back" style="display:inline-block;margin-bottom:12px;">← Mentor Videos</a>

        <div class="dash-header">
            <div>
                <div class="dash-title">{{ $item->name }}</div>
                <div class="dash-subtitle">{{ $item->mentor?->name ?? 'Mentor' }}
                    @if($item->description) · {{ $item->description }}@endif
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        <div style="display:grid;gap:18px;">
            @foreach($formatted['videos'] as $file)
            <div class="card" style="padding:16px;" id="file-{{ $file['id'] }}">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
                    <div style="font-size:14px;font-weight:700;">{{ $file['file_name'] }}</div>
                    @if($file['is_watched'])
                    <span class="cs-status cs-status--success">Watched</span>
                    @else
                    <form method="POST" action="{{ route('mentee.mentor-videos.watched', $file['id']) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Mark watched</button>
                    </form>
                    @endif
                </div>
                <video controls preload="metadata" style="width:100%;max-height:480px;border-radius:12px;background:#000;"
                       data-file-id="{{ $file['id'] }}"
                       @if(!$file['is_watched']) onended="this.closest('.card').querySelector('form')?.requestSubmit()" @endif>
                    <source src="{{ $file['video_url'] }}" type="video/mp4">
                </video>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
