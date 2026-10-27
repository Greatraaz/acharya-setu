@extends('frontend.layouts.frontend')
@section('title', 'Mentorship Guides — Insights')
@section('meta_description', 'Practical mentorship frameworks, conversation templates, and goal-setting tools from Vedrix.')

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
                <span>Mentorship Guides</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-lightbulb-fill me-1"></i> Mentorship Frameworks
            </div>
            <h1 class="insights-banner__title">Mastering the Art of Mentorship</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Practical frameworks, conversation templates, and goal-setting tools to help you get the highest return from every 1-on-1 mentorship session.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="insights-section-header text-center mb-4">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-journal-bookmark-fill me-1"></i> MENTORSHIP PLAYBOOKS
                </span>
                <h2 class="vj-font-heading h3 mb-2">Guides You Can Use in Your Next Session</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    Open a guide to read frameworks, conversation templates, and goal-setting tools.
                </p>
            </div>

            @if($guides->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-lightbulb"></i></div>
                    <h3>Mentorship Best Practices</h3>
                    <p>Learn how to formulate strategic questions, track progress, and build lifelong advisory relationships. New guides are on the way.</p>
                </div>
            @else
                <div class="blog-grid">
                    @foreach($guides as $guide)
                        <article class="blog-card white-paper-card">
                            <a href="{{ route('insights.mentorship-guides.show', $guide->slug) }}" class="text-decoration-none text-reset">
                                <div class="blog-card__media">
                                    @if($guide->imageUrl())
                                        <img src="{{ $guide->imageUrl() }}" alt="{{ $guide->title }}" loading="lazy">
                                    @else
                                        <div class="blog-card__placeholder">
                                            <i class="bi bi-lightbulb text-muted"></i>
                                        </div>
                                    @endif
                                    <span class="blog-card__cat">Guide</span>
                                </div>
                            </a>
                            <div class="blog-card__body white-paper-card__body">
                                <h2 class="blog-card__title">
                                    <a href="{{ route('insights.mentorship-guides.show', $guide->slug) }}" class="text-decoration-none">{{ $guide->title }}</a>
                                </h2>
                                <p class="blog-card__excerpt">{{ $guide->excerpt(26) }}</p>
                                <div class="blog-card__meta">
                                    <span><i class="bi bi-clock-history me-1"></i> {{ $guide->readTimeMinutes() }} min read</span>
                                    <span><i class="bi bi-calendar3 me-1"></i> {{ optional($guide->created_at)->format('M j, Y') ?: '—' }}</span>
                                </div>
                                <a href="{{ route('insights.mentorship-guides.show', $guide->slug) }}"
                                   class="white-paper-download-btn">
                                    Read Guide <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                @include('frontend.partials.pagination', ['paginator' => $guides])
            @endif
        </div>
    </section>
</div>
@endsection
