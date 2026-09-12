@extends('frontend.layouts.frontend')
@section('title', 'Explore All Insights — Knowledge Hub | Vedrix')
@section('meta_description', 'Actionable career playbooks, podcasts, research white papers, webinars, case studies, and mentorship guides by Vedrix.')

@section('content')
<div class="insights-page">
    {{-- Hero Banner --}}
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>Insights Hub</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-lightbulb-fill me-1"></i> Vedrix Knowledge Hub
            </div>
            <h1 class="insights-banner__title">Insights That Propel You Ahead</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Actionable playbooks, podcast dialogues, and research to keep you ahead in modern hiring markets and professional growth.
            </p>
        </div>
    </section>

    {{-- Main Body --}}
    <section class="section insights-body">
        <div class="container">
            {{-- Section Heading --}}
            <div class="insights-section-header text-center mb-4">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-grid-fill me-1"></i> BROWSE KNOWLEDGE PILLARS
                </span>
                <h2 class="vj-font-heading h3 mb-2">Explore Insight Categories</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    Select a category to discover curated mentorship frameworks, live events, case studies, and multimedia guides.
                </p>
            </div>

            {{-- Knowledge Categories Grid --}}
            <div class="row g-4 mb-5">
                {{-- 1. Blogs & Articles --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.blogs.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--blue">
                                <i class="bi bi-journal-richtext"></i>
                            </div>
                            @if(isset($stats['blogs']) && $stats['blogs'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['blogs'] }} Articles</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">Blogs & Articles</h3>
                        <p class="insights-hub-card__desc">Strategy, career frameworks, and actionable lessons from industry mentors.</p>
                        <div class="insights-hub-card__cta">
                            <span>Explore Articles</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>

                {{-- 2. White Papers --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.white-papers.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--amber">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            @if(isset($stats['white_papers']) && $stats['white_papers'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['white_papers'] }} Reports</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">White Papers</h3>
                        <p class="insights-hub-card__desc">Deep research, hiring trends, and evidence-backed mentorship data.</p>
                        <div class="insights-hub-card__cta">
                            <span>Read White Papers</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>

                {{-- 3. Case Studies --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.case-studies.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--green">
                                <i class="bi bi-trophy"></i>
                            </div>
                            @if(isset($stats['case_studies']) && $stats['case_studies'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['case_studies'] }} Stories</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">Case Studies</h3>
                        <p class="insights-hub-card__desc">Verified mentee transformations and real career transition journeys.</p>
                        <div class="insights-hub-card__cta">
                            <span>View Case Studies</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>

                {{-- 4. Podcasts --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.podcasts.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--purple">
                                <i class="bi bi-mic"></i>
                            </div>
                            @if(isset($stats['podcasts']) && $stats['podcasts'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['podcasts'] }} Episodes</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">Podcasts & Audio</h3>
                        <p class="insights-hub-card__desc">Unfiltered conversations and actionable leadership dialogues.</p>
                        <div class="insights-hub-card__cta">
                            <span>Listen to Podcasts</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>

                {{-- 5. Webinars --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.webinars.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--red">
                                <i class="bi bi-broadcast"></i>
                            </div>
                            @if(isset($stats['webinars']) && $stats['webinars'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['webinars'] }} Sessions</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">Webinars & Masterclasses</h3>
                        <p class="insights-hub-card__desc">Live interactive masterclasses and skill breakdowns by specialists.</p>
                        <div class="insights-hub-card__cta">
                            <span>Browse Webinars</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>

                {{-- 6. Videos --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.videos.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--pink">
                                <i class="bi bi-play-circle"></i>
                            </div>
                            @if(isset($stats['videos']) && $stats['videos'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['videos'] }} Videos</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">Video Library</h3>
                        <p class="insights-hub-card__desc">Practical walkthroughs, tech demos, and mock interview teardowns.</p>
                        <div class="insights-hub-card__cta">
                            <span>Watch Videos</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>

                {{-- 7. Download Centre --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.download-centre.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--teal">
                                <i class="bi bi-download"></i>
                            </div>
                            @if(isset($stats['downloads']) && $stats['downloads'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['downloads'] }} Files</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">Download Centre</h3>
                        <p class="insights-hub-card__desc">Free worksheets, career templates, roadmaps, and ready frameworks.</p>
                        <div class="insights-hub-card__cta">
                            <span>Get Free Downloads</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>

                {{-- 8. Testimonials --}}
                <div class="col-lg-3 col-md-6">
                    <a href="{{ route('insights.testimonials.index') }}" class="insights-hub-card h-100">
                        <div class="insights-hub-card__top">
                            <div class="insights-hub-card__icon insights-hub-card__icon--gold">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            @if(isset($stats['testimonials']) && $stats['testimonials'] > 0)
                                <span class="insights-hub-card__count">{{ $stats['testimonials'] }} Reviews</span>
                            @endif
                        </div>
                        <h3 class="insights-hub-card__title">Learner Testimonials</h3>
                        <p class="insights-hub-card__desc">Community feedback, real mentorship reviews, and success stories.</p>
                        <div class="insights-hub-card__cta">
                            <span>Read Reviews</span>
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Featured Recent Insights Section --}}
            @if(isset($blogs) && $blogs->isNotEmpty())
                <div class="insights-section-header mt-5 mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                                <i class="bi bi-stars me-1"></i> FRESH RELEASES
                            </span>
                            <h2 class="vj-font-heading h3 mb-2">Recent Featured Insights</h2>
                            <div class="lain"></div>
                        </div>
                        <a href="{{ route('insights.blogs.index') }}" class="vj-btn vj-btn-outline-mustard btn-sm">
                            View All Articles <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                <div class="blog-grid mb-5">
                    @foreach($blogs as $blog)
                        <a href="{{ route('insights.blogs.show', $blog->slug) }}" class="blog-card">
                            <div class="blog-card__media">
                                @if($blog->imageUrl())
                                    <img src="{{ $blog->imageUrl() }}" alt="{{ $blog->title }}" loading="lazy">
                                @else
                                    <div class="blog-card__placeholder">
                                        <i class="bi bi-journal-text text-muted"></i>
                                    </div>
                                @endif
                                @if($blog->category)
                                    <span class="blog-card__cat">{{ $blog->category }}</span>
                                @endif
                            </div>
                            <div class="blog-card__body">
                                <h3 class="blog-card__title">{{ $blog->title }}</h3>
                                <p class="blog-card__excerpt">{{ $blog->excerpt(26) }}</p>
                                <div class="blog-card__meta">
                                    <span><i class="bi bi-person me-1"></i> {{ $blog->author ?: 'Vedrix' }}</span>
                                    <span><i class="bi bi-calendar3 me-1"></i> {{ optional($blog->blog_date)->format('M j, Y') ?: '—' }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Knowledge Hub CTA Banner --}}
            <div class="insights-hub-banner">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-2">
                            <i class="bi bi-person-check-fill me-1"></i> 1-on-1 Guidance
                        </span>
                        <h3 class="text-white h3 fw-bold mb-2">Looking for Personalized Career Guidance?</h3>
                        <p class="text-white-50 mb-0">
                            Connect directly with verified mentors from leading companies. Get customized feedback, interview prep, and career roadmaps tailored to your goals.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-mustard btn-lg">
                            <i class="bi bi-search me-1"></i> Find a Mentor
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
