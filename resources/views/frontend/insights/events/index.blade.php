@php
    $isWebinar = $type === \App\Models\InsightEvent::TYPE_WEBINAR;
    $indexRoute = $isWebinar ? 'insights.webinars.index' : 'insights.events.index';
    $showRoute = $isWebinar ? 'insights.webinars.show' : 'insights.events.show';
    $pageTitle = $isWebinar ? 'Webinars & Live Events' : 'Events';
    $eyebrow = $isWebinar ? '🎥 Knowledge Sessions' : '📅 Live Events';
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

@extends('frontend.layouts.app')
@section('title', $pageTitle.' — Insights')
@section('meta_description', $bannerSub)

@section('content')
<div class="insights-page">
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>&gt;</span>
                <span>{{ $pageTitle }}</span>
            </nav>
            <div class="insights-banner__eyebrow">{{ $eyebrow }}</div>
            <h1 class="insights-banner__title">{{ $pageTitle }}</h1>
            <p class="insights-banner__sub">{{ $bannerSub }}</p>
        </div>
    </section>

    <section class="section insights-body">
        <div class="container">
            <div class="session-filter-bar">
                <div class="session-filter-tabs">
                    @foreach(['all', 'upcoming', 'past'] as $tab)
                        <a href="{{ route($indexRoute, ['filter' => $tab]) }}"
                           class="session-filter-tab {{ $filter === $tab ? 'is-active' : '' }}">
                            @if($tab === 'all') 📚 @elseif($tab === 'upcoming') 🕐 @else ⏪ @endif
                            {{ $filterLabels[$tab] }}
                        </a>
                    @endforeach
                </div>
                <div class="session-filter-status">Showing: {{ $showingLabel }}</div>
            </div>

            @if($sessions->isEmpty())
                <div class="blog-empty">
                    <div class="blog-empty__icon">{{ $isWebinar ? '🎥' : '📅' }}</div>
                    <h3>No {{ strtolower($pageTitle) }} yet</h3>
                    <p>Check back soon — new sessions are on the way.</p>
                </div>
            @else
                <div class="blog-grid session-grid">
                    @foreach($sessions as $item)
                        @include('frontend.insights.partials.session-card', [
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
