@extends('frontend.layouts.frontend')
@section('title', 'Learner Success Stories & Testimonials — Insights')
@section('meta_description', 'Read what students, early professionals, and mentees say about Vedrix mentorship — real feedback on clarity, guidance, and career growth.')

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
                <span>Learner Success Stories</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-star-fill me-1"></i> Client Success & Trust
            </div>
            <h1 class="insights-banner__title">What Mentees & Professionals Say About Vedrix</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Honest feedback from students, early professionals, and career builders who found clarity, confidence, and measurable progress through structured mentorship.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="testimonials-intro">
                <div>
                    <div class="testimonials-intro__eyebrow">Client Feedback</div>
                    <h2 class="testimonials-intro__title">Trusted by Learners & Professionals Across India & Beyond</h2>
                </div>
                <p class="testimonials-intro__text">
                    Years of consistent mentorship delivery, high mentee satisfaction, and long-term guidance relationships built on trust, accountability, and real outcomes.
                </p>
            </div>

            @if($testimonials->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-star"></i></div>
                    <h3>No testimonials yet</h3>
                    <p>Check back soon — new stories from our community are on the way.</p>
                </div>
            @else
                <div class="testimonials-grid">
                    @foreach($testimonials as $item)
                        <article class="testimonial-card">
                            <div class="testimonial-card__quote" aria-hidden="true">“</div>
                            <div class="testimonial-card__stars" aria-label="5 out of 5 stars">
                                @for($i = 0; $i < 5; $i++)
                                    <span><i class="bi bi-star-fill text-warning"></i></span>
                                @endfor
                            </div>
                            <div class="testimonial-card__message prose-blog">
                                {!! $item->message !!}
                            </div>
                            <div class="testimonial-card__footer">
                                <div class="testimonial-card__avatar">
                                    @if($item->imageUrl())
                                        <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" loading="lazy">
                                    @else
                                        <span>{{ mb_substr($item->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="testimonial-card__name">{{ $item->name }}</div>
                                    @if($item->designation)
                                        <div class="testimonial-card__designation">{{ $item->designation }}</div>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['paginator' => $testimonials])
            @endif
        </div>
    </section>
</div>
@endsection
