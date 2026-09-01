@extends('frontend.layouts.app')
@section('title', 'Blogs — Insights')
@section('meta_description', 'Career guidance articles and mentorship insights from Vedrix.')

@section('content')
<div class="insights-page">
    {{-- Banner (static) --}}
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>&gt;</span>
                <a href="{{ route('insights.blogs.index') }}">Insights</a>
                <span>&gt;</span>
                <span>Blogs</span>
            </nav>
            <div class="insights-banner__eyebrow">📄 Articles & Insights</div>
            <h1 class="insights-banner__title">Guidance That Shapes Real Careers</h1>
            <p class="insights-banner__sub">
                Stories, tips, and lessons from Vedrix mentors — helping students and early professionals find clarity, build skills, and move forward with confidence.
            </p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            {{-- Category filters --}}
            <div class="blog-filters blog-filters--inline">
                <a href="{{ route('insights.blogs.index') }}"
                   class="blog-filter-chip {{ $category === '' ? 'is-active' : '' }}">
                    All Articles
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('insights.blogs.index', ['category' => $cat]) }}"
                       class="blog-filter-chip {{ $category === $cat ? 'is-active' : '' }}">
                        {{ $cat }}
                    </a>
                @endforeach
            </div>
            <p class="blog-filters-meta">
                Showing: <strong>{{ $category !== '' ? $category : 'All Articles' }}</strong>
                · {{ $blogs->total() }} {{ \Illuminate\Support\Str::plural('post', $blogs->total()) }}
            </p>

            @if($blogs->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon">📰</div>
                    <h3>No articles yet</h3>
                    <p>Check back soon — new insights are on the way.</p>
                </div>
            @else
                <div class="blog-grid">
                    @foreach($blogs as $blog)
                        <a href="{{ route('insights.blogs.show', $blog->slug) }}" class="blog-card">
                            <div class="blog-card__media">
                                @if($blog->imageUrl())
                                    <img src="{{ $blog->imageUrl() }}" alt="{{ $blog->title }}">
                                @else
                                    <div class="blog-card__placeholder">📰</div>
                                @endif
                                @if($blog->category)
                                    <span class="blog-card__cat">{{ $blog->category }}</span>
                                @endif
                            </div>
                            <div class="blog-card__body">
                                <h2 class="blog-card__title">{{ $blog->title }}</h2>
                                <p class="blog-card__excerpt">{{ $blog->excerpt(26) }}</p>
                                <div class="blog-card__meta">
                                    <span>👤 {{ $blog->author ?: 'Vedrix' }}</span>
                                    <span>📅 {{ optional($blog->blog_date)->format('M j, Y') ?: '—' }}</span>
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
