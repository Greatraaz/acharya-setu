@extends('frontend.layouts.frontend')
@section('title', 'Learner Success Stories — Insights')
@section('meta_description', 'Discover inspiring career journeys and real success stories from Vedrix mentees.')

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
                <i class="bi bi-stars me-1"></i> Inspiring Journeys
            </div>
            <h1 class="insights-banner__title">Learner Success Stories</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Read how personalized 1-on-1 mentorship transformed the careers of our mentees, landing them dream roles across top global tech companies.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="testimonials-intro">
                <div>
                    <div class="testimonials-intro__eyebrow">Real Transformations</div>
                    <h2 class="testimonials-intro__title">Empowering Learners to Reach New Heights</h2>
                </div>
                <p class="testimonials-intro__text">
                    From breaking into software engineering to pivoting to leadership roles, these stories celebrate the grit of our learners and guidance of our mentors.
                </p>
            </div>

            @if($testimonials->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-stars"></i></div>
                    <h3>No stories yet</h3>
                    <p>Check back soon — new success stories are being published.</p>
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
