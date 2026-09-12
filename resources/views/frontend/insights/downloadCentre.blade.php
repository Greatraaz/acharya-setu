@extends('frontend.layouts.frontend')
@section('title', 'Download Centre — Insights')
@section('meta_description', 'Free Vedrix downloads — guides, templates, and resources for students and early professionals.')

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
                <span>Download Centre</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-download me-1"></i> Free Resources
            </div>
            <h1 class="insights-banner__title">Guides & Templates You Can Use Today</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Practical Vedrix downloads — career worksheets, mentorship guides, and ready-to-use templates to help you plan, learn, and grow with clarity.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            {{-- Section Heading --}}
            <div class="insights-section-header text-center mb-5">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-download me-1"></i> FREE RESOURCES & TEMPLATES
                </span>
                <h2 class="vj-font-heading h3 mb-2">Ready-to-Use Frameworks & Toolkits</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    Download curated worksheets, resume checklists, interview preparation cheat-sheets, and career roadmap templates.
                </p>
            </div>

            @if($downloads->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-download"></i></div>
                    <h3>No downloads yet</h3>
                    <p>Check back soon — new resources will appear here.</p>
                </div>
            @else
                <div class="blog-grid white-paper-grid">
                    @foreach($downloads as $item)
                        <article class="blog-card white-paper-card download-centre-card">
                            <div class="blog-card__media">
                                @if($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" loading="lazy">
                                @else
                                    <div class="blog-card__placeholder">
                                        <i class="bi bi-file-earmark-arrow-down text-muted"></i>
                                    </div>
                                @endif
                                <span class="download-centre-card__badge">{{ strtoupper($item->documentExtension()) }}</span>
                            </div>
                            <div class="blog-card__body white-paper-card__body">
                                <h2 class="blog-card__title">{{ $item->title }}</h2>
                                <p class="blog-card__excerpt">{{ $item->excerpt(28) }}</p>
                                <a href="{{ route('insights.download-centre.download', $item->slug) }}"
                                   class="white-paper-download-btn">
                                    <i class="bi bi-download me-1"></i> Download Resource
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['paginator' => $downloads])
            @endif
        </div>
    </section>
</div>
@endsection
