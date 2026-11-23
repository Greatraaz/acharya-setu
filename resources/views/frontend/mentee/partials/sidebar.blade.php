{{-- Shared mentee dashboard sidebar — used on every mentee page --}}
@php
    $upcomingCount = $upcomingCount
        ?? \App\Models\ConsultationSession::where('mentee_id', auth()->id())
            ->upcoming()
            ->count();

    $nav = function (string|array $patterns): bool {
        return request()->routeIs(...(array) $patterns);
    };
@endphp
<aside class="sidebar" id="dashSidebar">
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('mentee.dashboard') }}"
       class="sidebar-item {{ $nav('mentee.dashboard') ? 'active' : '' }}"
       @if($nav('mentee.dashboard')) data-nav-active="1" @endif>
        <span class="si-icon">📊</span> Dashboard
    </a>

    <div class="sidebar-section-label">Mentorship</div>
    <a href="{{ route('mentors.search') }}"
       class="sidebar-item {{ $nav(['mentors.search', 'mentors.show', 'mentors.availability']) ? 'active' : '' }}"
       @if($nav(['mentors.search', 'mentors.show', 'mentors.availability'])) data-nav-active="1" @endif>
        <span class="si-icon">🔍</span> Find Mentors
    </a>
    <a href="{{ route('mentee.mentor.change') }}"
       class="sidebar-item {{ $nav(['mentee.mentor.*', 'mentee.mentor-requests*']) ? 'active' : '' }}"
       @if($nav(['mentee.mentor.*', 'mentee.mentor-requests*'])) data-nav-active="1" @endif>
        <span class="si-icon">🎓</span> My Mentor
    </a>
    <a href="{{ route('mentee.sessions') }}"
       class="sidebar-item {{ $nav(['mentee.sessions*', 'sessions.call*', 'mock-interviews.call*']) ? 'active' : '' }}"
       @if($nav(['mentee.sessions*', 'sessions.call*', 'mock-interviews.call*'])) data-nav-active="1" @endif>
        <span class="si-icon">📅</span> My Sessions
        @if($upcomingCount > 0)<span class="si-badge">{{ $upcomingCount }}</span>@endif
    </a>

    <div class="sidebar-section-label">Learning</div>
    <a href="{{ route('mentee.journey.index') }}"
       class="sidebar-item {{ $nav('mentee.journey.*') ? 'active' : '' }}"
       @if($nav('mentee.journey.*')) data-nav-active="1" @endif>
        <span class="si-icon">🗺️</span> My Journey
    </a>
    <a href="{{ route('mentee.tasks.index') }}"
       class="sidebar-item {{ $nav('mentee.tasks*') ? 'active' : '' }}"
       @if($nav('mentee.tasks*')) data-nav-active="1" @endif>
        <span class="si-icon">✅</span> My Tasks
    </a>
    <a href="{{ route('mentee.assessments.index') }}"
       class="sidebar-item {{ $nav('mentee.assessments*') ? 'active' : '' }}"
       @if($nav('mentee.assessments*')) data-nav-active="1" @endif>
        <span class="si-icon">📝</span> Assessments
    </a>
    <a href="{{ route('mentee.quizzes.index') }}"
       class="sidebar-item {{ $nav('mentee.quizzes*') ? 'active' : '' }}"
       @if($nav('mentee.quizzes*')) data-nav-active="1" @endif>
        <span class="si-icon">🧠</span> Quizzes
    </a>
    <a href="{{ route('mentee.mentor-videos.index') }}"
       class="sidebar-item {{ $nav('mentee.mentor-videos*') ? 'active' : '' }}"
       @if($nav('mentee.mentor-videos*')) data-nav-active="1" @endif>
        <span class="si-icon">🎬</span> Videos
    </a>

    <div class="sidebar-section-label">Career</div>
    <a href="{{ route('mentee.career-services.index') }}"
       class="sidebar-item {{ $nav('mentee.career-services*') ? 'active' : '' }}"
       @if($nav('mentee.career-services*')) data-nav-active="1" @endif>
        <span class="si-icon">📄</span> Career Services
    </a>
    <a href="{{ route('mentee.mock-interviews.index') }}"
       class="sidebar-item {{ $nav('mentee.mock-interviews*') ? 'active' : '' }}"
       @if($nav('mentee.mock-interviews*')) data-nav-active="1" @endif>
        <span class="si-icon">🎤</span> Mock Interviews
    </a>
    <a href="{{ route('mentee.jobs') }}"
       class="sidebar-item {{ $nav('mentee.jobs*') ? 'active' : '' }}"
       @if($nav('mentee.jobs*')) data-nav-active="1" @endif>
        <span class="si-icon">💼</span> Job Listings
    </a>

    <div class="sidebar-section-label">Community</div>
    <a href="{{ route('mentee.community.index') }}"
       class="sidebar-item {{ $nav('mentee.community*') ? 'active' : '' }}"
       @if($nav('mentee.community*')) data-nav-active="1" @endif>
        <span class="si-icon">💬</span> Channels
    </a>

    <div class="sidebar-section-label">Account</div>
    <a href="{{ route('mentee.plans') }}"
       class="sidebar-item {{ $nav('mentee.plans*') ? 'active' : '' }}"
       @if($nav('mentee.plans*')) data-nav-active="1" @endif>
        <span class="si-icon">⭐</span> Plans
    </a>
    <a href="{{ route('mentee.wallet') }}"
       class="sidebar-item {{ $nav('mentee.wallet*') ? 'active' : '' }}"
       @if($nav('mentee.wallet*')) data-nav-active="1" @endif>
        <span class="si-icon">💰</span> Wallet
        <span style="margin-left:auto;font-size:11px;color:var(--brand);">₹{{ number_format(auth()->user()->wallet_balance ?? 0, 0) }}</span>
    </a>
    <a href="{{ route('account') }}"
       class="sidebar-item {{ $nav('account*') ? 'active' : '' }}"
       @if($nav('account*')) data-nav-active="1" @endif>
        <span class="si-icon">👤</span> Profile Settings
    </a>
    <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">
        @csrf
        <button type="submit" class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);border:none;text-align:left;">
            <span class="si-icon">🚪</span> Sign Out
        </button>
    </form>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
@include('frontend.mentee.partials.bottom-nav')
@include('frontend.partials.sidebar-active-scroll')
