@extends('frontend.layouts.frontend')
@section('title', 'Curated Essays & Media — Insights')
@section('meta_description', 'Curated collection of thought leadership essays, webinar recordings, podcasts, and video masterclasses.')

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
                <span>Curated Essays & Media</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-collection-play-fill me-1"></i> Multimedia & Longform
            </div>
            <h1 class="insights-banner__title">Curated Essays, Masterclasses & Media</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                A rich multimedia repository bringing together deep-dive engineering essays, video teardowns, webinar panels, and intimate mentor conversations.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <a href="{{ route('insights.webinars.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--red">
                                <i class="bi bi-broadcast"></i>
                            </div>
                        </div>
                        <h3 class="insights-hub-card__title">Webinars</h3>
                        <p class="insights-hub-card__desc">Live panels & interactive masterclasses</p>
                        <div class="insights-hub-card__cta">
                            <span>Browse Sessions</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('insights.podcasts.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--purple">
                                <i class="bi bi-mic"></i>
                            </div>
                        </div>
                        <h3 class="insights-hub-card__title">Podcasts</h3>
                        <p class="insights-hub-card__desc">Unfiltered dialogues with tech innovators</p>
                        <div class="insights-hub-card__cta">
                            <span>Listen Now</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('insights.videos.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--pink">
                                <i class="bi bi-play-circle"></i>
                            </div>
                        </div>
                        <h3 class="insights-hub-card__title">Videos</h3>
                        <p class="insights-hub-card__desc">Practical walkthroughs and mock interviews</p>
                        <div class="insights-hub-card__cta">
                            <span>Watch Videos</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('insights.blogs.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--blue">
                                <i class="bi bi-journal-richtext"></i>
                            </div>
                        </div>
                        <h3 class="insights-hub-card__title">Blogs & Essays</h3>
                        <p class="insights-hub-card__desc">Deep dive articles and career breakdowns</p>
                        <div class="insights-hub-card__cta">
                            <span>Read Articles</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            @if(isset($blogs) && $blogs->isNotEmpty())
                <div class="insights-section-header mb-4">
                    <h2 class="vj-font-heading h3 mb-2">Latest Curated Articles</h2>
                    <div class="lain"></div>
                </div>
                <div class="blog-grid">
                    @foreach($blogs as $blog)
                        <a href="{{ route('insights.blogs.show', $blog->slug) }}" class="blog-card">
                            <div class="blog-card__media">
                                @if($blog->imageUrl())
                                    <img src="{{ $blog->imageUrl() }}" alt="{{ $blog->title }}" loading="lazy">
                                @else
                                    <div class="blog-card__placeholder">
                                        <i class="bi bi-journal-text text-muted"></i>
                                    </div>
                                @endif
                                @if($blog->category)
                                    <span class="blog-card__cat">{{ $blog->category }}</span>
                                @endif
                            </div>
                            <div class="blog-card__body">
                                <h2 class="blog-card__title">{{ $blog->title }}</h2>
                                <p class="blog-card__excerpt">{{ $blog->excerpt(26) }}</p>
                                <div class="blog-card__meta">
                                    <span><i class="bi bi-person me-1"></i> {{ $blog->author ?: 'Vedrix' }}</span>
                                    <span><i class="bi bi-calendar3 me-1"></i> {{ optional($blog->blog_date)->format('M j, Y') ?: '—' }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
