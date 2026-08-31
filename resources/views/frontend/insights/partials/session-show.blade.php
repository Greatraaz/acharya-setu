@php
    $isWebinar = $type === \App\Models\InsightEvent::TYPE_WEBINAR;
    $indexRoute = $isWebinar ? 'insights.webinars.index' : 'insights.events.index';
    $showRoute = $isWebinar ? 'insights.webinars.show' : 'insights.events.show';
    $registerRoute = $isWebinar ? 'insights.webinars.register' : 'insights.events.register';
    $sectionLabel = $isWebinar ? 'Webinars' : 'Events';
    $typeTag = $isWebinar ? '🎥 WEBINAR' : '📅 EVENT';
@endphp

@extends('frontend.layouts.app')
@section('title', $session->title.' — '.$sectionLabel)
@section('meta_description', $session->excerpt(40))

@section('content')
<div class="insights-page">
    <section class="insights-banner insights-banner--detail session-detail-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span>&gt;</span>
                <a href="{{ route($indexRoute) }}">{{ $sectionLabel }}</a>
                <span>&gt;</span>
                <span>{{ \Illuminate\Support\Str::limit($session->title, 48) }}</span>
            </nav>
            <div class="insights-banner__eyebrow">{{ $typeTag }}</div>
            <h1 class="insights-banner__title insights-banner__title--sm">{{ $session->title }}</h1>
            <ul class="session-detail-meta">
                <li><span>👤</span> {{ $session->speaker }}</li>
                <li><span>📅</span> {{ optional($session->start_date)->format('F d, Y') }}</li>
                <li><span>🕐</span> {{ $session->timeRangeLabel() }}</li>
                <li><span>📍</span> {{ $session->location }}</li>
            </ul>
            @if($session->isUpcoming())
                <div class="session-countdown" data-session-countdown data-start="{{ $session->startsAt()->toIso8601String() }}">
                    <div class="session-countdown__label">Live Session Starts In</div>
                    <div class="session-countdown__grid">
                        <div><strong data-countdown-days>0</strong><span>Days</span></div>
                        <div><strong data-countdown-hours>0</strong><span>Hours</span></div>
                        <div><strong data-countdown-mins>0</strong><span>Mins</span></div>
                        <div><strong data-countdown-secs>0</strong><span>Secs</span></div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="section insights-body">
        <div class="container blog-detail-layout">
            <article class="blog-detail-main">
                @if($session->imageUrl())
                    <img src="{{ $session->imageUrl() }}" alt="{{ $session->title }}" class="blog-detail-hero">
                @endif

                <div class="session-content-block">
                    <div class="session-content-block__head">
                        <span class="session-content-block__icon">ℹ️</span>
                        <h2>Session Overview</h2>
                    </div>
                    <div class="prose-blog">{!! $session->description !!}</div>
                </div>

                <div class="session-speaker-card">
                    <div class="session-content-block__head">
                        <span class="session-content-block__icon">🎤</span>
                        <h2>Featured Speaker</h2>
                    </div>
                    <div class="session-speaker-card__body">
                        <div class="session-speaker-card__avatar">{{ mb_substr($session->speaker, 0, 1) }}</div>
                        <div>
                            <h3>{{ $session->speaker }}</h3>
                            <p>Keynote Speaker & Industry Specialist</p>
                            <p class="session-speaker-card__bio">Expert in mentorship, career guidance, and professional development strategy.</p>
                        </div>
                    </div>
                </div>

                @if(trim(strip_tags((string) $session->what_you_will_learn)) !== '')
                    <div class="session-content-block">
                        <div class="session-content-block__head">
                            <span class="session-content-block__icon">💡</span>
                            <h2>Key Takeaways & {{ $isWebinar ? 'Webinar' : 'Event' }} Outcomes</h2>
                        </div>
                        <div class="prose-blog">{!! $session->what_you_will_learn !!}</div>
                    </div>
                @endif

                @if(trim(strip_tags((string) $session->who_should_attend)) !== '')
                    <div class="session-content-block">
                        <div class="session-content-block__head">
                            <span class="session-content-block__icon">👥</span>
                            <h2>Who Should Attend</h2>
                        </div>
                        <div class="prose-blog">{!! $session->who_should_attend !!}</div>
                    </div>
                @endif

                @if(trim(strip_tags((string) $session->event_agenda)) !== '')
                    <div class="session-content-block">
                        <div class="session-content-block__head">
                            <span class="session-content-block__icon">📋</span>
                            <h2>Event Agenda</h2>
                        </div>
                        <div class="prose-blog">{!! $session->event_agenda !!}</div>
                    </div>
                @endif

                @if($session->faqLines())
                    <div class="session-content-block">
                        <div class="session-content-block__head">
                            <span class="session-content-block__icon">❓</span>
                            <h2>FAQ</h2>
                        </div>
                        <ul class="session-faq-list">
                            @foreach($session->faqLines() as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </article>

            <aside class="blog-detail-aside">
                @include('frontend.insights.partials.session-registration', [
                    'session' => $session,
                    'registerRoute' => $registerRoute,
                ])

                @if($recent->isNotEmpty())
                    <div class="blog-aside-card">
                        <h3>More {{ $sectionLabel }}</h3>
                        @foreach($recent as $item)
                            <a href="{{ route($showRoute, $item->slug) }}" class="blog-aside-item">
                                <div class="blog-aside-thumb">
                                    @if($item->imageUrl())
                                        <img src="{{ $item->imageUrl() }}" alt="">
                                    @else
                                        <span>{{ $isWebinar ? '🎥' : '📅' }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="blog-aside-title">{{ $item->title }}</div>
                                    <div class="blog-aside-date">{{ $item->dateLabel() }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var el = document.querySelector('[data-session-countdown]');
    if (!el) return;
    var start = new Date(el.getAttribute('data-start'));
    var daysEl = el.querySelector('[data-countdown-days]');
    var hoursEl = el.querySelector('[data-countdown-hours]');
    var minsEl = el.querySelector('[data-countdown-mins]');
    var secsEl = el.querySelector('[data-countdown-secs]');

    function tick() {
        var diff = start - new Date();
        if (diff <= 0) {
            daysEl.textContent = '0';
            hoursEl.textContent = '0';
            minsEl.textContent = '0';
            secsEl.textContent = '0';
            return;
        }
        var days = Math.floor(diff / 86400000);
        diff -= days * 86400000;
        var hours = Math.floor(diff / 3600000);
        diff -= hours * 3600000;
        var mins = Math.floor(diff / 60000);
        diff -= mins * 60000;
        var secs = Math.floor(diff / 1000);
        daysEl.textContent = String(days);
        hoursEl.textContent = String(hours);
        minsEl.textContent = String(mins);
        secsEl.textContent = String(secs);
    }

    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
