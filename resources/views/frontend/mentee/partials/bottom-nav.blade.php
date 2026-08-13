@php
    $isHome = request()->routeIs('mentee.dashboard');
    $isMentors = request()->routeIs('mentors.search', 'mentors.show') || request()->routeIs('mentee.mentor.*');
    $isSessions = request()->routeIs('mentee.sessions*');
    $isTasks = request()->routeIs('mentee.journey.*') || request()->routeIs('mentee.quizzes*') || request()->routeIs('mentee.assessments*');
    $isProfile = request()->routeIs('account*');
@endphp
<nav class="app-bottom-nav" aria-label="Mentee mobile navigation">
    <a href="{{ route('mentee.dashboard') }}" class="app-bottom-nav__item {{ $isHome ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M4 11.2 12 4.5l8 6.7"/>
            <path d="M6.5 10.2V19a1.5 1.5 0 0 0 1.5 1.5h3.2V15h1.6v5.5H16A1.5 1.5 0 0 0 17.5 19v-8.8"/>
        </svg>
        <span>Home</span>
    </a>
    <a href="{{ route('mentors.search') }}" class="app-bottom-nav__item {{ $isMentors ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="8.2" cy="8.2" r="2.6"/>
            <path d="M3.8 18.5c.6-2.7 2.6-4.2 4.4-4.2s3.8 1.5 4.4 4.2"/>
            <circle cx="16.2" cy="8.6" r="2.2"/>
            <path d="M13.3 18.5c.5-2.2 2.1-3.4 3.6-3.4s3.1 1.2 3.6 3.4"/>
        </svg>
        <span>Mentors</span>
    </a>
    <a href="{{ route('mentee.sessions') }}" class="app-bottom-nav__item {{ $isSessions ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3.2" y="6.2" width="12.2" height="11.6" rx="2"/>
            <path d="M15.4 10.2 20.2 7.4v9.2l-4.8-2.8z"/>
        </svg>
        <span>Sessions</span>
    </a>
    <a href="{{ route('mentee.journey.index') }}" class="app-bottom-nav__item {{ $isTasks ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M8 4.5h8.5A2.5 2.5 0 0 1 19 7v12.5a2.5 2.5 0 0 1-2.5 2.5H7.5A2.5 2.5 0 0 1 5 19.5V7A2.5 2.5 0 0 1 7.5 4.5H8"/>
            <path d="M8 4.5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1H8v-1z"/>
            <path d="M8.5 12h7M8.5 16h5"/>
        </svg>
        <span>Tasks</span>
    </a>
    <a href="{{ route('account') }}" class="app-bottom-nav__item {{ $isProfile ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="8" r="3.2"/>
            <path d="M5.5 19.2c.8-3.2 3.4-5 6.5-5s5.7 1.8 6.5 5"/>
        </svg>
        <span>Profile</span>
    </a>
</nav>
