@php
    $isDash = request()->routeIs('mentor.dashboard');
    $isSessions = request()->routeIs('mentor.sessions*');
    $isTasks = request()->routeIs('mentor.curriculum*') || request()->routeIs('mentor.journey*');
    $isCommunity = request()->routeIs('mentor.community*');
    $isProfile = request()->routeIs('mentor.profile.*') || request()->routeIs('account*');
@endphp
<nav class="app-bottom-nav" aria-label="Mentor mobile navigation">
    <a href="{{ route('mentor.dashboard') }}" class="app-bottom-nav__item {{ $isDash ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3.5" y="3.5" width="7" height="7" rx="1.4"/>
            <rect x="13.5" y="3.5" width="7" height="7" rx="1.4"/>
            <rect x="3.5" y="13.5" width="7" height="7" rx="1.4"/>
            <rect x="13.5" y="13.5" width="7" height="7" rx="1.4"/>
        </svg>
        <span>Dashboard</span>
    </a>
    <a href="{{ route('mentor.sessions') }}" class="app-bottom-nav__item {{ $isSessions ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <rect x="3.5" y="5" width="17" height="15.5" rx="2"/>
            <path d="M3.5 10h17M8 3.5v3M16 3.5v3"/>
        </svg>
        <span>Sessions</span>
    </a>
    <a href="{{ route('mentor.curriculum.tracks') }}" class="app-bottom-nav__item {{ $isTasks ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M8 4.5h8.5A2.5 2.5 0 0 1 19 7v12.5a2.5 2.5 0 0 1-2.5 2.5H7.5A2.5 2.5 0 0 1 5 19.5V7A2.5 2.5 0 0 1 7.5 4.5H8"/>
            <path d="M8 4.5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1H8v-1z"/>
            <path d="M8.5 12h7M8.5 16h5"/>
        </svg>
        <span>Tasks</span>
    </a>
    <a href="{{ route('mentor.community') }}" class="app-bottom-nav__item {{ $isCommunity ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path d="M5 15.5 3.8 19.2a.7.7 0 0 0 .9.9L8.4 18.8"/>
            <path d="M8.2 18.2H7.5A4.5 4.5 0 0 1 3 13.7V10A4.5 4.5 0 0 1 7.5 5.5h5A4.5 4.5 0 0 1 17 10v.8"/>
            <path d="M10 9.5h6.5A4.5 4.5 0 0 1 21 14v3.2A4.5 4.5 0 0 1 16.5 21.7H11A4.5 4.5 0 0 1 6.5 17.2v-3.2A4.5 4.5 0 0 1 11 9.5Z"/>
        </svg>
        <span>Community</span>
    </a>
    <a href="{{ route('mentor.profile.edit') }}" class="app-bottom-nav__item {{ $isProfile ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="8" r="3.2"/>
            <path d="M5.5 19.2c.8-3.2 3.4-5 6.5-5s5.7 1.8 6.5 5"/>
        </svg>
        <span>Profile</span>
    </a>
</nav>
