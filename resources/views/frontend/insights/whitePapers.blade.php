@extends('frontend.layouts.frontend')
@section('title', 'White Papers — Insights')
@section('meta_description', 'Vedrix white papers on mentorship, career readiness, and structured guidance for students and early professionals.')

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
                <span>White Papers</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-file-earmark-text-fill me-1"></i> Research & Reports
            </div>
            <h1 class="insights-banner__title">Mentorship Insights, Backed by Research</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                In-depth Vedrix white papers on career readiness, structured mentorship, and how guided learning helps students and early professionals grow with purpose.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            {{-- Section Heading --}}
            <div class="insights-section-header text-center mb-5">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-file-earmark-text-fill me-1"></i> RESEARCH & IN-DEPTH REPORTS
                </span>
                <h2 class="vj-font-heading h3 mb-2">Evidence-Backed Mentorship White Papers</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    Download and read comprehensive research on talent readiness, career guidance efficacy, and emerging workforce dynamics.
                </p>
            </div>

            @if($whitePapers->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-file-earmark-text"></i></div>
                    <h3>No white papers yet</h3>
                    <p>Check back soon — new research reports are on the way.</p>
                </div>
            @else
                <div class="blog-grid white-paper-grid">
                    @foreach($whitePapers as $paper)
                        <article class="blog-card white-paper-card">
                            <div class="blog-card__media">
                                @if($paper->imageUrl())
                                    <img src="{{ $paper->imageUrl() }}" alt="{{ $paper->title }}" loading="lazy">
                                @else
                                    <div class="blog-card__placeholder">
                                        <i class="bi bi-file-earmark-pdf text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="blog-card__body white-paper-card__body">
                                <h2 class="blog-card__title">{{ $paper->title }}</h2>
                                <p class="blog-card__excerpt">{{ $paper->excerpt(28) }}</p>
                                <a href="{{ route('insights.white-papers.download', $paper->slug) }}"
                                   class="white-paper-download-btn">
                                    <i class="bi bi-download me-1"></i> Download White Paper
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
