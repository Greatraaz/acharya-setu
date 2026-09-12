@extends('frontend.layouts.frontend')
@section('title', 'Mentorship Guides — Insights')
@section('meta_description', 'Essential guides on maximizing mentorship, setting milestone goals, and structuring mentor-mentee engagements.')

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
                Practical frameworks, conversation templates, and goal-setting tools to help you get the highest return on investment from every 1-on-1 mentorship session.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            @if(isset($blogs) && $blogs->isNotEmpty())
                <div class="blog-grid">
                    @foreach($blogs as $blog)
                        <a href="{{ route('insights.blogs.show', $blog->slug) }}" class="blog-card">
                            <div class="blog-card__media">
                                @if($blog->imageUrl())
                                    <img src="{{ $blog->imageUrl() }}" alt="{{ $blog->title }}" loading="lazy">
                                @else
                                    <div class="blog-card__placeholder">
                                        <i class="bi bi-lightbulb text-muted"></i>
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
                                    <span><i class="bi bi-person me-1"></i> {{ $blog->author ?: 'Vedrix Mentorship Team' }}</span>
                                    <span><i class="bi bi-calendar3 me-1"></i> {{ optional($blog->blog_date)->format('M j, Y') ?: '—' }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                @include('frontend.partials.pagination', ['paginator' => $blogs])
            @else
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-lightbulb"></i></div>
                    <h3>Mentorship Best Practices</h3>
                    <p>Learn how to formulate strategic questions, track progress, and build lifelong advisory relationships.</p>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
