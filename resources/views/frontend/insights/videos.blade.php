@extends('frontend.layouts.frontend')
@section('title', 'Videos — Insights')
@section('meta_description', 'Watch Vedrix mentorship videos — career guidance walkthroughs, skill-building demos, and expert conversations for students and professionals.')

@section('content')
<div class="insights-page">
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.index') }}">Insights</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>Videos</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-play-circle-fill me-1"></i> Video Library
            </div>
            <h1 class="insights-banner__title">Mentorship Videos & Career Demos</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Watch practical walkthroughs, mentorship highlights, skill-building sessions, and expert conversations from the Vedrix community.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="media-library-intro">
                <div>
                    <div class="media-library-intro__eyebrow">Featured Videos</div>
                    <h2 class="media-library-intro__title">Explore YouTube Mentorship Content</h2>
                </div>
                <p class="media-library-intro__hint">Click any video to watch the full presentation in a large player.</p>
            </div>

            @if($videos->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-play-circle"></i></div>
                    <h3>No videos yet</h3>
                    <p>Check back soon — new video content is on the way.</p>
                </div>
            @else
                <div class="media-library-grid">
                    @foreach($videos as $item)
                        @include('frontend.insights.mediaLibraryCard', [
                            'mediaType' => 'youtube',
                            'title' => $item->title,
                            'excerpt' => $item->excerpt(26),
                            'thumbnail' => $item->thumbnailUrl(),
                            'youtubeSrc' => $item->youtubeEmbedUrl(true),
                            'youtubeWatch' => $item->youtubeWatchUrl(),
                        ])
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['paginator' => $videos])
            @endif
        </div>
    </section>
</div>

@include('frontend.insights.mediaLightbox')
@endsection
