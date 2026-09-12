@php
    $isWebinar = $type === \App\Models\InsightEvent::TYPE_WEBINAR;
    $indexRoute = $isWebinar ? 'insights.webinars.index' : 'insights.events.index';
    $showRoute = $isWebinar ? 'insights.webinars.show' : 'insights.events.show';
    $pageTitle = $isWebinar ? 'Webinars & Live Masterclasses' : 'Events & Workshops';
    $eyebrowIcon = $isWebinar ? 'bi-broadcast' : 'bi-calendar-event';
    $eyebrowText = $isWebinar ? 'Knowledge Sessions' : 'Live Events';
    $bannerSub = $isWebinar
        ? 'Join mentors and industry pioneers as they share best practices across careers, skills, and professional growth.'
        : 'Discover in-person and virtual events designed to help students and early professionals connect, learn, and grow.';
    $filterLabels = [
        'all' => $isWebinar ? 'All Sessions' : 'All Events',
        'upcoming' => $isWebinar ? 'Upcoming Sessions' : 'Upcoming Events',
        'past' => $isWebinar ? 'Past Sessions & Recordings' : 'Past Events',
    ];
    $showingLabel = match ($filter) {
        'upcoming' => $filterLabels['upcoming'],
        'past' => $filterLabels['past'],
        default => $filterLabels['all'],
    };
@endphp

@extends('frontend.layouts.frontend')
@section('title', $pageTitle.' — Insights')
@section('meta_description', $bannerSub)

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
                <span>{{ $pageTitle }}</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi {{ $eyebrowIcon }} me-1"></i> {{ $eyebrowText }}
            </div>
            <h1 class="insights-banner__title">{{ $pageTitle }}</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">{{ $bannerSub }}</p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            {{-- Section Heading --}}
            <div class="insights-section-header text-center mb-4">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi {{ $isWebinar ? 'bi-broadcast' : 'bi-calendar-event' }} me-1"></i> LIVE & RECORDED SESSIONS
                </span>
                <h2 class="vj-font-heading h3 mb-2">{{ $isWebinar ? 'Masterclasses & Knowledge Sessions' : 'Workshops & Live Events' }}</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    {{ $isWebinar ? 'Participate in live interactive webinars led by senior practitioners or access on-demand recordings.' : 'Join interactive in-person and virtual workshops to build high-demand skills and network with leaders.' }}
                </p>
            </div>

            <div class="session-filter-bar">
                <div class="session-filter-tabs">
                    @foreach(['all', 'upcoming', 'past'] as $tab)
                        <a href="{{ route($indexRoute, ['filter' => $tab]) }}"
                           class="session-filter-tab {{ $filter === $tab ? 'is-active' : '' }}">
                            @if($tab === 'all')
                                <i class="bi bi-collection-play me-1"></i>
                            @elseif($tab === 'upcoming')
                                <i class="bi bi-clock-history me-1"></i>
                            @else
                                <i class="bi bi-archive me-1"></i>
                            @endif
                            {{ $filterLabels[$tab] }}
                        </a>
                    @endforeach
                </div>
                <div class="session-filter-status">Showing: {{ $showingLabel }}</div>
            </div>

            @if($sessions->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon"><i class="bi {{ $isWebinar ? 'bi-broadcast' : 'bi-calendar-event' }}"></i></div>
                    <h3>No {{ strtolower($pageTitle) }} yet</h3>
                    <p>Check back soon — new sessions are on the way.</p>
                </div>
            @else
                <div class="blog-grid session-grid">
                    @foreach($sessions as $item)
                        @include('frontend.insights.sessionCard', [
                            'item' => $item,
                            'showRoute' => $showRoute,
                        ])
                    @endforeach
                </div>

                @include('frontend.partials.pagination', ['paginator' => $sessions])
            @endif
        </div>
    </section>
</div>
@endsection
