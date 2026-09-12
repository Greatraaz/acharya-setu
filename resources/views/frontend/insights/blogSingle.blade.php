@extends('frontend.layouts.frontend')
@section('title', $blog->meta_title ?: $blog->title)
@section('meta_description', $blog->meta_description ?: $blog->excerpt(40))

@section('content')
<div class="insights-page">
    <section class="insights-banner insights-banner--detail">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.index') }}">Insights</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.blogs.index') }}">Blogs</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>{{ \Illuminate\Support\Str::limit($blog->title, 48) }}</span>
            </nav>
            @if($blog->category)
                <div class="insights-banner__eyebrow">
                    <i class="bi bi-tag-fill me-1"></i> {{ $blog->category }}
                </div>
            @endif
            <h1 class="insights-banner__title insights-banner__title--sm">{{ $blog->title }}</h1>
            <div class="lain"></div>
            <div class="blog-detail-meta">
                <span><i class="bi bi-person me-1"></i> By {{ $blog->author ?: 'Vedrix' }}</span>
                <span><i class="bi bi-calendar3 me-1"></i> {{ optional($blog->blog_date)->format('M j, Y') ?: '—' }}</span>
                <span><i class="bi bi-clock-history me-1"></i> {{ $blog->readTimeMinutes() }} Min Read</span>
            </div>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container blog-detail-layout">
            <article class="blog-detail-main">
                @if($blog->imageUrl())
                    <img src="{{ $blog->imageUrl() }}" alt="{{ $blog->title }}" class="blog-detail-hero">
                @endif
                <div class="blog-detail-content prose-blog">
                    {!! $blog->description !!}
                </div>
            </article>

            <aside class="blog-detail-aside">
                <div class="blog-aside-card">
                    <h3>Recent Insights</h3>
                    @forelse($recent as $item)
                        <a href="{{ route('insights.blogs.show', $item->slug) }}" class="blog-aside-item">
                            <div class="blog-aside-thumb">
                                @if($item->imageUrl())
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" loading="lazy">
                                @else
                                    <span><i class="bi bi-journal-text text-muted"></i></span>
                                @endif
                            </div>
                            <div>
                                <div class="blog-aside-title">{{ $item->title }}</div>
                                <div class="blog-aside-date"><i class="bi bi-calendar3 me-1"></i> {{ optional($item->blog_date)->format('M j, Y') }}</div>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-muted">No other posts yet.</p>
                    @endforelse
                </div>

                <div class="blog-aside-cta">
                    <h3>Need career guidance?</h3>
                    <p>Connect with verified mentors and get structured direction for your next step.</p>
                    <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-full">Find a Mentor <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </aside>
        </div>
    </section>
</div>
@endsection
