@extends('frontend.layouts.app')
@section('title', 'Mentor Videos — Vedrix')

@section('content')
<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <div class="dash-title">Mentor Videos</div>
                <div class="dash-subtitle">Watch videos shared by mentors — separate from your curriculum.</div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px;">
            <div class="card" style="padding:14px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);font-weight:700;">Videos</div>
                <div style="font-size:22px;font-weight:800;margin-top:4px;">{{ $summary['total_files'] }}</div>
            </div>
            <div class="card" style="padding:14px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);font-weight:700;">Watched</div>
                <div style="font-size:22px;font-weight:800;margin-top:4px;">{{ $summary['watched'] }}</div>
            </div>
            <div class="card" style="padding:14px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);font-weight:700;">Progress</div>
                <div style="font-size:22px;font-weight:800;margin-top:4px;color:var(--brand);">{{ $summary['percent'] }}%</div>
            </div>
        </div>

        <form method="GET" class="session-toolbar assess-toolbar" style="margin-bottom:16px;">
            <div class="session-filter-tabs">
                @foreach(['' => 'All', '0' => 'Unwatched', '1' => 'Watched'] as $key => $label)
                    <a href="{{ route('mentee.mentor-videos.index', array_filter(['watched' => $key !== '' ? $key : null, 'search' => $search ?: null])) }}"
                       class="session-filter-tab {{ (string) $watched === (string) $key ? 'active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="assess-toolbar__grid">
                @if($watched !== '' && $watched !== null)
                    <input type="hidden" name="watched" value="{{ $watched }}">
                @endif
                <div class="assess-toolbar__search session-search-field">
                    <span class="session-search-icon" aria-hidden="true">🔍</span>
                    <input type="search" name="search" class="form-input" value="{{ $search }}" placeholder="Search videos…">
                </div>
                <button type="submit" class="btn btn-outline assess-toolbar__submit">Search</button>
            </div>
        </form>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
            @forelse($collections as $item)
            @php
                $f = $item->formatted;
                $fileCount = count($f['videos']);
                $watchedCount = collect($f['videos'])->where('is_watched', true)->count();
            @endphp
            <a href="{{ route('mentee.mentor-videos.show', $item->id) }}" class="card" style="padding:16px;display:flex;flex-direction:column;gap:10px;text-decoration:none;color:inherit;">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(245,158,11,.12);display:flex;align-items:center;justify-content:center;font-size:20px;">🎬</div>
                <div style="font-size:15px;font-weight:800;">{{ $item->name }}</div>
                <div style="font-size:12px;color:var(--text-2);">{{ $item->mentor?->name ?? 'Mentor' }} · {{ $fileCount }} video{{ $fileCount === 1 ? '' : 's' }}</div>
                @if($item->description)
                <p style="font-size:12px;color:var(--text-3);margin:0;line-height:1.4;">{{ \Illuminate\Support\Str::limit($item->description, 90) }}</p>
                @endif
                <div style="margin-top:auto;font-size:12px;font-weight:700;color:var(--brand);">
                    {{ $watchedCount }}/{{ $fileCount }} watched
                </div>
            </a>
            @empty
            <div class="card" style="padding:36px;text-align:center;grid-column:1/-1;color:var(--text-3);">
                <div style="font-size:40px;margin-bottom:8px;">🎬</div>
                <div style="font-weight:700;color:var(--text-1);">No videos yet</div>
                <p style="margin:6px 0 0;font-size:13px;">When mentors upload videos, they’ll appear here.</p>
            </div>
            @endforelse
        </div>

        @if($collections->hasPages())
        <div style="margin-top:18px;">@include('frontend.partials.pagination', ['paginator' => $collections])</div>
        @endif
    </div>
</div>
@endsection
