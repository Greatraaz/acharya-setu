@extends('frontend.layouts.frontend')
@section('title', $guide->title.' — Mentorship Guides')
@section('meta_description', $guide->excerpt(40))

@section('content')
<div class="insights-page">
    <section class="insights-banner insights-banner--detail">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.index') }}">Insights</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.mentorship-guides') }}">Mentorship Guides</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>{{ \Illuminate\Support\Str::limit($guide->title, 48) }}</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-lightbulb-fill me-1"></i> Mentorship Guide
            </div>
            <h1 class="insights-banner__title insights-banner__title--sm">{{ $guide->title }}</h1>
            <div class="lain"></div>
            <div class="blog-detail-meta">
                <span><i class="bi bi-journal-richtext me-1"></i> Vedrix Mentorship Team</span>
                <span><i class="bi bi-calendar3 me-1"></i> {{ optional($guide->created_at)->format('M j, Y') ?: '—' }}</span>
                <span><i class="bi bi-clock-history me-1"></i> {{ $guide->readTimeMinutes() }} Min Read</span>
            </div>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container blog-detail-layout">
            <article class="blog-detail-main">
                @if($guide->imageUrl())
                    <img src="{{ $guide->imageUrl() }}" alt="{{ $guide->title }}" class="blog-detail-hero">
                @endif
                <div class="blog-detail-content prose-blog">
                    {!! $guide->description !!}
                </div>
            </article>

            <aside class="blog-detail-aside">
                <div class="blog-aside-card">
                    <h3>More Guides</h3>
                    @forelse($recent as $item)
                        <a href="{{ route('insights.mentorship-guides.show', $item->slug) }}" class="blog-aside-item">
                            <div class="blog-aside-thumb">
                                @if($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" loading="lazy">
                                @else
                                    <span><i class="bi bi-lightbulb text-muted"></i></span>
                                @endif
                            </div>
                            <div>
                                <div class="blog-aside-title">{{ $item->title }}</div>
                                <div class="blog-aside-date"><i class="bi bi-calendar3 me-1"></i> {{ optional($item->created_at)->format('M j, Y') }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-muted">No other guides yet.</p>
                    @endforelse
                </div>

                <div class="blog-aside-cta">
                    <h3>Need a mentor?</h3>
                    <p>Put the guide into practice with a verified Vedrix mentor.</p>
                    <a href="{{ route('mentors.search') }}" class="session-register-btn">Find a Mentor <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </aside>
        </div>
    </section>
</div>
@endsection
