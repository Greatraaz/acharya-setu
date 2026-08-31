@extends('frontend.layouts.app')
@section('title', 'Podcasts — Insights')
@section('meta_description', 'Listen to Vedrix mentorship podcasts — career guidance, skill-building conversations, and expert insights for students and professionals.')

@section('content')
<div class="insights-page">
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>&gt;</span>
                <span>Podcasts</span>
            </nav>
            <div class="insights-banner__eyebrow">🎙️ Podcast Library</div>
            <h1 class="insights-banner__title">Podcasts That Guide Real Career Decisions</h1>
            <p class="insights-banner__sub">
                Audio episodes and YouTube conversations with mentors and industry voices — practical guidance you can listen to or watch anytime.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="media-library-intro">
                <div>
                    <div class="media-library-intro__eyebrow">Featured Episodes</div>
                    <h2 class="media-library-intro__title">Explore Audio & YouTube Podcasts</h2>
                </div>
                <p class="media-library-intro__hint">Click any episode to play it in a large screen player.</p>
            </div>

            <div class="session-filter-bar media-library-filters">
                <div class="session-filter-tabs">
                    <a href="{{ route('insights.podcasts.index') }}"
                       class="session-filter-tab {{ $type === '' ? 'is-active' : '' }}">📚 All Episodes</a>
                    <a href="{{ route('insights.podcasts.index', ['type' => 'audio']) }}"
                       class="session-filter-tab {{ $type === 'audio' ? 'is-active' : '' }}">🎧 Audio</a>
                    <a href="{{ route('insights.podcasts.index', ['type' => 'youtube_url']) }}"
                       class="session-filter-tab {{ $type === 'youtube_url' ? 'is-active' : '' }}">▶️ YouTube</a>
                </div>
                <div class="session-filter-status">
                    Showing:
                    @if($type === 'audio')
                        Audio Episodes
                    @elseif($type === 'youtube_url')
                        YouTube Episodes
                    @else
                        All Episodes
                    @endif
                </div>
            </div>

            @if($podcasts->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon">🎙️</div>
                    <h3>No podcasts yet</h3>
                    <p>Check back soon — new episodes are on the way.</p>
                </div>
            @else
                <div class="media-library-grid">
                    @foreach($podcasts as $item)
                        @include('frontend.insights.partials.media-library-card', [
                            'mediaType' => $item->isAudio() ? 'audio' : 'youtube',
                            'title' => $item->title,
                            'excerpt' => $item->excerpt(26),
                            'thumbnail' => $item->thumbnailUrl(),
                            'audioSrc' => $item->isAudio() ? $item->audioUrl() : null,
                            'youtubeSrc' => $item->isYoutube() ? $item->youtubeEmbedUrl(true) : null,
                            'youtubeWatch' => $item->isYoutube() ? $item->youtubeWatchUrl() : null,
                        ])
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['paginator' => $podcasts])
            @endif
        </div>
    </section>
</div>

@include('frontend.insights.partials.media-lightbox')
@endsection
