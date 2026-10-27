@extends('frontend.layouts.frontend')
@section('title', $meta['page_title'])
@section('meta_description', $meta['meta_description'])

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
                <span>{{ $meta['label'] }}</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi {{ $meta['eyebrow_icon'] }} me-1"></i> {{ $meta['eyebrow'] }}
            </div>
            <h1 class="insights-banner__title">{{ $meta['banner_title'] }}</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">{{ $meta['banner_sub'] }}</p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="insights-section-header text-center mb-4">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-journal-bookmark-fill me-1"></i> {{ $meta['section_eyebrow'] }}
                </span>
                <h2 class="vj-font-heading h3 mb-2">{{ $meta['section_title'] }}</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">{{ $meta['section_sub'] }}</p>
            </div>

            @if($items->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi {{ $meta['empty_icon'] }}"></i></div>
                    <h3>{{ $meta['empty_title'] }}</h3>
                    <p>{{ $meta['empty_body'] }}</p>
                </div>
            @else
                <div class="blog-grid">
                    @foreach($items as $item)
                        <article class="blog-card white-paper-card">
                            <a href="{{ route($meta['public_show'], $item->slug) }}" class="text-decoration-none text-reset">
                                <div class="blog-card__media">
                                    @if($item->imageUrl())
                                        <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}" loading="lazy">
                                    @else
                                        <div class="blog-card__placeholder">
                                            <i class="bi {{ $meta['placeholder_icon'] }} text-muted"></i>
                                        </div>
                                    @endif
                                    <span class="blog-card__cat">{{ $meta['badge'] }}</span>
                                </div>
                            </a>
                            <div class="blog-card__body white-paper-card__body">
                                <h2 class="blog-card__title">
                                    <a href="{{ route($meta['public_show'], $item->slug) }}" class="text-decoration-none">{{ $item->title }}</a>
                                </h2>
                                <p class="blog-card__excerpt">{{ $item->excerpt(26) }}</p>
                                <div class="blog-card__meta">
                                    <span><i class="bi bi-clock-history me-1"></i> {{ $item->readTimeMinutes() }} min read</span>
                                    <span><i class="bi bi-calendar3 me-1"></i> {{ optional($item->created_at)->format('M j, Y') ?: '—' }}</span>
                                </div>
                                @if(!empty($meta['gated']))
                                    <a href="{{ route($meta['public_show'], $item->slug) }}#download-form"
                                       class="white-paper-download-btn">
                                        <i class="bi bi-download me-1"></i> {{ $meta['download_label'] }}
                                    </a>
                                @else
                                    <a href="{{ route($meta['public_show'], $item->slug) }}"
                                       class="white-paper-download-btn">
                                        Read {{ $meta['singular'] }} <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
                @include('frontend.partials.pagination', ['paginator' => $items])
            @endif
        </div>
    </section>
</div>
@endsection
