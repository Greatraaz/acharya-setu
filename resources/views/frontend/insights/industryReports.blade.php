@extends('frontend.layouts.frontend')
@section('title', 'Industry Reports — Insights')
@section('meta_description', 'Data-backed market intelligence, hiring trends, salary benchmarks, and tech industry reports by Vedrix.')

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
                <span>Industry Reports</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-bar-chart-line-fill me-1"></i> Market Intelligence
            </div>
            <h1 class="insights-banner__title">Data-Backed Industry Reports & Trends</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                In-depth research on tech hiring demands, salary benchmarks, emerging skill gaps, and market dynamics across global engineering and product ecosystems.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            @if(isset($whitePapers) && $whitePapers->isNotEmpty())
                <div class="blog-grid white-paper-grid">
                    @foreach($whitePapers as $paper)
                        <article class="blog-card white-paper-card">
                            <div class="blog-card__media">
                                @if($paper->imageUrl())
                                    <img src="{{ $paper->imageUrl() }}" alt="{{ $paper->title }}" loading="lazy">
                                @else
                                    <div class="blog-card__placeholder">
                                        <i class="bi bi-bar-chart-line text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="blog-card__body white-paper-card__body">
                                <h2 class="blog-card__title">{{ $paper->title }}</h2>
                                <p class="blog-card__excerpt">{{ $paper->excerpt(28) }}</p>
                                <a href="{{ route('insights.white-papers.download', $paper->slug) }}"
                                   class="white-paper-download-btn">
                                    <i class="bi bi-download me-1"></i> Download Industry Report
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
                @include('frontend.partials.pagination', ['paginator' => $whitePapers])
            @else
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-bar-chart-line"></i></div>
                    <h3>No reports currently published</h3>
                    <p>Check back soon — our upcoming quarterly hiring and salary benchmarks are releasing shortly.</p>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
