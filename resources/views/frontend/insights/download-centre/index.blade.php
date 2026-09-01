@extends('frontend.layouts.app')
@section('title', 'Download Centre — Insights')
@section('meta_description', 'Free Vedrix downloads — guides, templates, and resources for students and early professionals.')

@section('content')
<div class="insights-page">
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>&gt;</span>
                <span>Download Centre</span>
            </nav>
            <div class="insights-banner__eyebrow">⬇️ Free Resources</div>
            <h1 class="insights-banner__title">Guides & Templates You Can Use Today</h1>
            <p class="insights-banner__sub">
                Practical Vedrix downloads — career worksheets, mentorship guides, and ready-to-use templates to help you plan, learn, and grow with clarity.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="insights-toolbar insights-toolbar--search-only">
                @include('frontend.partials.insights-search', [
                    'placeholder' => 'Search downloads…',
                    'search' => $search ?? '',
                    'variant' => 'toolbar',
                ])
            </div>

            @if($downloads->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon">⬇️</div>
                    <h3>No downloads yet</h3>
                    <p>Check back soon — new resources will appear here.</p>
                </div>
            @else
                <div class="blog-grid white-paper-grid">
                    @foreach($downloads as $item)
                        <article class="blog-card white-paper-card download-centre-card">
                            <div class="blog-card__media">
                                @if($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}">
                                @else
                                    <div class="blog-card__placeholder">⬇️</div>
                                @endif
                                <span class="download-centre-card__badge">{{ strtoupper($item->documentExtension()) }}</span>
                            </div>
                            <div class="blog-card__body white-paper-card__body">
                                <h2 class="blog-card__title">{{ $item->title }}</h2>
                                <p class="blog-card__excerpt">{{ $item->excerpt(28) }}</p>
                                <a href="{{ route('insights.download-centre.download', $item->slug) }}"
                                   class="white-paper-download-btn">
                                    Download Resource
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
