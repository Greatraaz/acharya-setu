@extends('frontend.layouts.app')
@section('title', 'Case Studies — Insights')
@section('meta_description', 'Real mentorship outcomes from Vedrix — how structured guidance helps students and early professionals find clarity and grow.')

@section('content')
<div class="insights-page">
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>&gt;</span>
                <span>Case Studies</span>
            </nav>
            <div class="insights-banner__eyebrow">🏆 Customer Proof</div>
            <h1 class="insights-banner__title">Mentorship Stories That Prove Direction Works</h1>
            <p class="insights-banner__sub">
                Real journeys from students and early professionals who found clarity, built skills, and moved forward with structured Vedrix mentorship.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            @if($caseStudies->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon">📁</div>
                    <h3>No case studies yet</h3>
                    <p>Check back soon — new mentorship stories are on the way.</p>
                </div>
            @else
                <div class="blog-grid case-study-grid">
                    @foreach($caseStudies as $study)
                        <article class="blog-card case-study-card">
                            <div class="blog-card__media">
                                @if($study->imageUrl())
                                    <img src="{{ $study->imageUrl() }}" alt="{{ $study->title }}">
                                @else
                                    <div class="blog-card__placeholder">📁</div>
                                @endif
                                <span class="case-study-card__badge">{{ $study->industry }}</span>
                            </div>
                            <div class="blog-card__body case-study-card__body">
                                <h2 class="blog-card__title">{{ $study->title }}</h2>
                                <p class="blog-card__excerpt">{{ $study->excerpt(28) }}</p>
                                <a href="{{ route('insights.case-studies.show', $study->slug) }}"
                                   class="case-study-view-btn">
                                    View Mentorship Story
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if(method_exists($caseStudies, 'hasPages') && $caseStudies->hasPages())
                    <div class="blog-pagination">{{ $caseStudies->links() }}</div>
                @endif
            @endif
        </div>
    </section>
</div>
@endsection
