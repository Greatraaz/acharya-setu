@extends('frontend.layouts.frontend')
@section('title', $caseStudy->title.' — Case Studies')
@section('meta_description', $caseStudy->excerpt(40))

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
                <a href="{{ route('insights.case-studies.index') }}">Case Studies</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>{{ \Illuminate\Support\Str::limit($caseStudy->title, 48) }}</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-trophy-fill me-1"></i> {{ $caseStudy->industry }}
            </div>
            <h1 class="insights-banner__title insights-banner__title--sm">{{ $caseStudy->title }}</h1>
            <div class="lain"></div>
            @if($caseStudy->excerpt(30))
                <p class="insights-banner__sub">{{ $caseStudy->excerpt(30) }}</p>
            @endif
        </div>
    </section>

    <section class="section insights-body">
        <div class="container blog-detail-layout">
            <article class="blog-detail-main">
                @if($caseStudy->imageUrl())
                    <img src="{{ $caseStudy->imageUrl() }}" alt="{{ $caseStudy->title }}" class="blog-detail-hero">
                @endif

                <div class="blog-detail-content prose-blog">
                    {!! $caseStudy->description !!}
                </div>

                @if($caseStudy->resultPlain() !== '')
                    <div class="case-study-outcome">
                        <div class="case-study-outcome__label">Measured Outcome:</div>
                        <div class="case-study-outcome__body">
                            <span class="case-study-outcome__icon" aria-hidden="true">
                                <i class="bi bi-award-fill text-warning"></i>
                            </span>
                            <div class="case-study-outcome__content">
                                {!! $caseStudy->result !!}
                            </div>
                        </div>
                    </div>
                @endif
            </article>

            <aside class="blog-detail-aside">
                @if($recent->isNotEmpty())
                    <div class="blog-aside-card">
                        <h3>More Case Studies</h3>
                        @foreach($recent as $item)
                            <a href="{{ route('insights.case-studies.show', $item->slug) }}" class="blog-aside-item">
                                <div class="blog-aside-thumb">
                                    @if($item->imageUrl())
                                        <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
                                    @else
                                        <span><i class="bi bi-trophy text-muted"></i></span>
                                    @endif
                                </div>
                                <div>
                                    <div class="blog-aside-title">{{ $item->title }}</div>
                                    <div class="blog-aside-date"><i class="bi bi-tag me-1"></i> {{ $item->industry }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="case-study-aside-cta">
                    <h3>Need a similar mentorship path?</h3>
                    <p>Connect with verified Vedrix mentors for structured guidance, clearer goals, and measurable progress.</p>
                    <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-full">Talk to a Mentor <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </aside>
        </div>
    </section>
</div>
@endsection
