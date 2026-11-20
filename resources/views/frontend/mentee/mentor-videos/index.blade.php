@extends('frontend.layouts.app')
@section('title', 'Mentor Videos — Vedrix')

@push('styles')
<style>
.mv-hub{
    --mv: var(--brand, #f59e0b);
    --mv-dark: var(--brand-dark, #d97706);
    --mv-soft: var(--brand-muted, rgba(245,158,11,.12));
    font-family: var(--font-body, "DM Sans", sans-serif);
}
.mv-hub .dash-title{ font-family: var(--font-head, "Sora", sans-serif); letter-spacing:-.02em; }
.mv-hub__intro{
    position:relative; overflow:hidden;
    margin:4px 0 18px; padding:20px 22px;
    border:1px solid var(--border, rgba(0,0,0,.07));
    border-radius: var(--radius-lg, 16px);
    background:
        radial-gradient(480px 160px at 100% 0%, color-mix(in srgb, var(--mv) 16%, transparent), transparent 70%),
        var(--card-bg, #fff);
    box-shadow: var(--shadow, 0 4px 24px rgba(0,0,0,.06));
}
.mv-hub__intro::before{
    content:""; position:absolute; left:0; top:0; bottom:0; width:4px;
    background:linear-gradient(180deg, var(--mv), var(--mv-dark));
}
.mv-hub__eyebrow{
    display:inline-flex; font-family: var(--font-head, "Sora", sans-serif);
    font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase;
    color:var(--mv-dark); background:var(--mv-soft); padding:6px 11px; border-radius:8px;
}
.mv-hub__intro h2{
    margin:12px 0 6px; font-family: var(--font-head, "Sora", sans-serif);
    font-size:clamp(22px, 3vw, 28px); font-weight:800; letter-spacing:-.03em; color:var(--text, #09090b);
}
.mv-hub__intro p{ margin:0; max-width:520px; font-size:14px; line-height:1.55; color:var(--text-2, #52525b); }
.mv-hub__card{
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.mv-hub__card:hover{
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--mv) 40%, transparent);
    box-shadow: 0 14px 28px rgba(245,158,11,.12);
}
</style>
@endpush

@section('content')
<div class="dash-layout mv-hub">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-header">
            <div>
                <div class="dash-title">Mentor Videos</div>
                <div class="dash-subtitle">Watch videos shared by your mentor — separate from your curriculum.</div>
            </div>
        </div>

        <div class="mv-hub__intro">
            <span class="mv-hub__eyebrow">Learning</span>
            <h2>Watch. Learn. Mark complete.</h2>
            <p>Collections from your mentor appear here. Track watched progress as you go.</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif

        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px;">
            <div class="card" style="padding:14px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);font-weight:700;">Videos</div>
                <div style="font-size:22px;font-weight:800;margin-top:4px;font-family:var(--font-head,Sora,sans-serif);">{{ $summary['total_files'] }}</div>
            </div>
            <div class="card" style="padding:14px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);font-weight:700;">Watched</div>
                <div style="font-size:22px;font-weight:800;margin-top:4px;font-family:var(--font-head,Sora,sans-serif);">{{ $summary['watched'] }}</div>
            </div>
            <div class="card" style="padding:14px;">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);font-weight:700;">Progress</div>
                <div style="font-size:22px;font-weight:800;margin-top:4px;color:var(--brand);font-family:var(--font-head,Sora,sans-serif);">{{ $summary['percent'] }}%</div>
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
            <a href="{{ route('mentee.mentor-videos.show', $item->id) }}" class="card mv-hub__card" style="padding:16px;display:flex;flex-direction:column;gap:10px;text-decoration:none;color:inherit;">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(245,158,11,.12);display:flex;align-items:center;justify-content:center;font-size:20px;">🎬</div>
                <div style="font-size:15px;font-weight:800;font-family:var(--font-head,Sora,sans-serif);">{{ $item->name }}</div>
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
                <p style="margin:6px 0 0;font-size:13px;">Videos uploaded by your mentor will appear here.</p>
            </div>
            @endforelse
        </div>

        @if($collections->hasPages())
        <div style="margin-top:18px;">@include('frontend.partials.pagination', ['paginator' => $collections])</div>
        @endif
    </div>
</div>
@endsection
