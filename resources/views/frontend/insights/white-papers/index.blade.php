@extends('frontend.layouts.app')
@section('title', 'White Papers — Insights')
@section('meta_description', 'Vedrix white papers on mentorship, career readiness, and structured guidance for students and early professionals.')

@section('content')
<div class="insights-page">
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>&gt;</span>
                <span>White Papers</span>
            </nav>
            <div class="insights-banner__eyebrow">📄 Research & Reports</div>
            <h1 class="insights-banner__title">Mentorship Insights, Backed by Research</h1>
            <p class="insights-banner__sub">
                In-depth Vedrix white papers on career readiness, structured mentorship, and how guided learning helps students and early professionals grow with purpose.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="insights-toolbar insights-toolbar--search-only">
                @include('frontend.partials.insights-search', [
                    'placeholder' => 'Search white papers…',
                    'search' => $search ?? '',
                    'variant' => 'toolbar',
                ])
            </div>

            @if($whitePapers->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon">📄</div>
                    <h3>No white papers yet</h3>
                    <p>Check back soon — new research reports are on the way.</p>
                </div>
            @else
                <div class="blog-grid white-paper-grid">
                    @foreach($whitePapers as $paper)
                        <article class="blog-card white-paper-card">
                            <div class="blog-card__media">
                                @if($paper->imageUrl())
                                    <img src="{{ $paper->imageUrl() }}" alt="{{ $paper->title }}">
                                @else
                                    <div class="blog-card__placeholder">📄</div>
                                @endif
                            </div>
                            <div class="blog-card__body white-paper-card__body">
                                <h2 class="blog-card__title">{{ $paper->title }}</h2>
                                <p class="blog-card__excerpt">{{ $paper->excerpt(28) }}</p>
                                <a href="{{ route('insights.white-papers.download', $paper->slug) }}"
                                   class="white-paper-download-btn">
                                    Download White Paper
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['paginator' => $whitePapers])
            @endif
        </div>
    </section>
</div>
@endsection
