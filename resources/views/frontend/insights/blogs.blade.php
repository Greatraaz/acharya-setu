@extends('frontend.layouts.frontend')
@section('title', 'Blogs — Insights')
@section('meta_description', 'Career guidance articles and mentorship insights from Vedrix.')

@section('content')
<div class="insights-page">
    {{-- Banner --}}
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.index') }}">Insights</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>Blogs</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-journal-richtext me-1"></i> Articles & Insights
            </div>
            <h1 class="insights-banner__title">Guidance That Shapes Real Careers</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Stories, tips, and lessons from Vedrix mentors — helping students and early professionals find clarity, build skills, and move forward with confidence.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            {{-- Section Heading --}}
            <div class="insights-section-header text-center mb-4">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-journal-bookmark-fill me-1"></i> CURATED ARTICLES & GUIDES
                </span>
                <h2 class="vj-font-heading h3 mb-2">Explore Articles by Topic & Domain</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    Filter by category to discover actionable career roadmaps, interview playbooks, mentorship frameworks, and industry lessons.
                </p>
            </div>

            {{-- Category filters --}}
            <div class="blog-filters blog-filters--inline justify-content-center">
                <a href="{{ route('insights.blogs.index') }}"
                   class="blog-filter-chip {{ $category === '' ? 'is-active' : '' }}">
                    <i class="bi bi-grid me-1"></i> All Articles
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('insights.blogs.index', ['category' => $cat]) }}"
                       class="blog-filter-chip {{ $category === $cat ? 'is-active' : '' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
            <p class="blog-filters-meta text-center">
                Showing: <strong>{{ $category !== '' ? $category : 'All Articles' }}</strong>
                · {{ $blogs->total() }} {{ \Illuminate\Support\Str::plural('post', $blogs->total()) }}
            </p>

            @if($blogs->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi bi-newspaper"></i></div>
                    <h3>No articles yet</h3>
                    <p>Check back soon — new insights are on the way.</p>
                </div>
            @else
                <div class="blog-grid">
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
                                <h2 class="blog-card__title">{{ $blog->title }}</h2>
                                <p class="blog-card__excerpt">{{ $blog->excerpt(26) }}</p>
                                <div class="blog-card__meta">
                                    <span><i class="bi bi-person me-1"></i> {{ $blog->author ?: 'Vedrix' }}</span>
                                    <span><i class="bi bi-calendar3 me-1"></i> {{ optional($blog->blog_date)->format('M j, Y') ?: '—' }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['paginator' => $blogs])
            @endif
        </div>
    </section>
</div>
@endsection
