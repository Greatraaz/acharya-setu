@extends('frontend.layouts.app')
@section('title', 'Mentor Videos — Vedrix')

@section('content')
@include('frontend.partials.dash-premium-ui')

<div class="dash-layout">
    @include('frontend.mentee.partials.sidebar')

    <div class="dash-content">
        <div class="dash-page" style="display:flex;flex-direction:column;gap:1.1rem;">

            <div class="learn-intro fu">
                <div>
                    <span class="learn-eyebrow">Learning · Video library</span>
                    <h2>Videos</h2>
                    <p>Watch collections shared by your mentor — track progress as you go, separate from curriculum weeks.</p>
                </div>
            </div>

            @if(session('success'))
            <div class="alert-premium fu" style="border-color:rgba(34,197,94,.3);background:rgba(34,197,94,.1);">
                <span>✓</span>
                <div><p>{{ session('success') }}</p></div>
            </div>
            @endif

            <div class="dash-grid dash-grid-4 fu" style="grid-template-columns:repeat(3,minmax(0,1fr));">
                <div class="learn-stat">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ $summary['total_files'] }}</div>
                            <div class="learn-stat__label">Videos</div>
                        </div>
                        <span class="learn-stat__icon">🎬</span>
                    </div>
                </div>
                <div class="learn-stat">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value">{{ $summary['watched'] }}</div>
                            <div class="learn-stat__label">Watched</div>
                        </div>
                        <span class="learn-stat__icon">👀</span>
                    </div>
                </div>
                <div class="learn-stat">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;">
                        <div>
                            <div class="learn-stat__value" style="color:var(--ln-dark);">{{ $summary['percent'] }}%</div>
                            <div class="learn-stat__label">Progress</div>
                        </div>
                        <span class="learn-stat__icon">📈</span>
                    </div>
                </div>
            </div>

            <form method="GET" class="learn-filter-bar fu">
                <div class="session-filter-tabs">
                    @foreach(['' => 'All', '0' => 'Unwatched', '1' => 'Watched'] as $key => $label)
                        <a href="{{ route('mentee.mentor-videos.index', array_filter(['watched' => $key !== '' ? $key : null, 'search' => $search ?: null])) }}"
                           class="session-filter-tab {{ (string) $watched === (string) $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <div class="learn-search">
                    @if($watched !== '' && $watched !== null)
                        <input type="hidden" name="watched" value="{{ $watched }}">
                    @endif
                    <div class="session-search-field">
                        <span class="session-search-icon" aria-hidden="true">🔍</span>
                        <input type="search" name="search" class="form-input" value="{{ $search }}" placeholder="Search videos…">
                    </div>
                    <button type="submit" class="learn-btn-ghost">Search</button>
                </div>
            </form>

            <div class="learn-grid-cards fu">
                @forelse($collections as $item)
                @php
                    $f = $item->formatted;
                    $fileCount = count($f['videos']);
                    $watchedCount = collect($f['videos'])->where('is_watched', true)->count();
                @endphp
                <a href="{{ route('mentee.mentor-videos.show', $item->id) }}" class="learn-tile">
                    <span class="learn-tile__icon">🎬</span>
                    <h3 class="learn-tile__title">{{ $item->name }}</h3>
                    <div class="learn-tile__meta">{{ $item->mentor?->name ?? 'Mentor' }} · {{ $fileCount }} video{{ $fileCount === 1 ? '' : 's' }}</div>
                    @if($item->description)
                        <p class="learn-list-card__desc" style="margin:0;">{{ \Illuminate\Support\Str::limit($item->description, 90) }}</p>
                    @endif
                    <div class="learn-tile__foot">{{ $watchedCount }}/{{ $fileCount }} watched</div>
                </a>
                @empty
                <div class="learn-panel empty-block" style="grid-column:1/-1;">
                    <div class="icon">🎬</div>
                    <div style="font-weight:750;color:var(--text);margin-bottom:.25rem;">No videos yet</div>
                    <p>Videos uploaded by your mentor will appear here.</p>
                </div>
                @endforelse
            </div>

            @if($collections->hasPages())
                @include('frontend.partials.pagination', ['paginator' => $collections])
            @endif
        </div>
    </div>
</div>
@endsection
