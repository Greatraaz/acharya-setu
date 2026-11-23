{{-- Shared mentor / expert dashboard sidebar --}}
@php
    $host = auth()->user();
    $isExpert = $host?->isExpert();
    $pendingCount = $pendingCount
        ?? \App\Models\ConsultationSession::where('mentor_id', auth()->id())->upcoming()->count();

    $nav = function (string|array $patterns): bool {
        return request()->routeIs(...(array) $patterns);
    };
@endphp
<aside class="sidebar" id="dashSidebar">
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('mentor.dashboard') }}"
       class="sidebar-item {{ $nav('mentor.dashboard') ? 'active' : '' }}"
       @if($nav('mentor.dashboard')) data-nav-active="1" @endif>
        <span class="si-icon">📊</span> Dashboard
    </a>

    @unless($isExpert)
    <div class="sidebar-section-label">Mentees</div>
    <a href="{{ route('mentor.requests') }}"
       class="sidebar-item {{ $nav('mentor.requests*') ? 'active' : '' }}"
       @if($nav('mentor.requests*')) data-nav-active="1" @endif>
        <span class="si-icon">📨</span> Requests
        @php
            $requestCount = \App\Models\MentorRequest::where('mentor_id', auth()->id())->where('status', 'pending')->count();
        @endphp
        @if($requestCount > 0)<span class="si-badge">{{ $requestCount }}</span>@endif
    </a>
    <a href="{{ route('mentor.mentees') }}"
       class="sidebar-item {{ $nav('mentor.mentees*') ? 'active' : '' }}"
       @if($nav('mentor.mentees*')) data-nav-active="1" @endif>
        <span class="si-icon">🎓</span> My Mentees
    </a>
    @endunless

    <div class="sidebar-section-label">Sessions</div>
    <a href="{{ route('mentor.sessions') }}"
       class="sidebar-item {{ $nav(['mentor.sessions*', 'sessions.call*', 'mock-interviews.call*']) ? 'active' : '' }}"
       @if($nav(['mentor.sessions*', 'sessions.call*', 'mock-interviews.call*'])) data-nav-active="1" @endif>
        <span class="si-icon">📅</span> My Sessions
        @if($pendingCount > 0)<span class="si-badge">{{ $pendingCount }}</span>@endif
    </a>
    <a href="{{ route('mentor.availability') }}"
       class="sidebar-item {{ $nav('mentor.availability*') ? 'active' : '' }}"
       @if($nav('mentor.availability*')) data-nav-active="1" @endif>
        <span class="si-icon">⏰</span> Set Availability
    </a>
    <a href="{{ route('mentor.notes') }}"
       class="sidebar-item {{ $nav('mentor.notes*') ? 'active' : '' }}"
       @if($nav('mentor.notes*')) data-nav-active="1" @endif>
        <span class="si-icon">📝</span> Session Notes
    </a>

    @unless($isExpert)
    <div class="sidebar-section-label">Teaching</div>
    <a href="{{ route('mentor.curriculum.tracks') }}"
       class="sidebar-item {{ $nav('mentor.curriculum*') ? 'active' : '' }}"
       @if($nav('mentor.curriculum*')) data-nav-active="1" @endif>
        <span class="si-icon">🗺️</span> Curriculum
    </a>
    <a href="{{ route('mentor.tasks.index') }}"
       class="sidebar-item {{ $nav('mentor.tasks*') ? 'active' : '' }}"
       @if($nav('mentor.tasks*')) data-nav-active="1" @endif>
        <span class="si-icon">✅</span> Tasks
    </a>
    <a href="{{ route('mentor.journey') }}"
       class="sidebar-item {{ $nav(['mentor.journey*', 'mentor.submissions*']) ? 'active' : '' }}"
       @if($nav(['mentor.journey*', 'mentor.submissions*'])) data-nav-active="1" @endif>
        <span class="si-icon">📈</span> Progress
        @php
            $submissionReviewCount = $submissionReviewCount
                ?? app(\App\Services\CurriculumSubmissionReviewService::class)->pendingCountForMentor(auth()->id());
        @endphp
        @if($submissionReviewCount > 0)<span class="si-badge">{{ $submissionReviewCount }}</span>@endif
    </a>
    <a href="{{ route('mentor.assessments.index') }}"
       class="sidebar-item {{ $nav('mentor.assessments*') ? 'active' : '' }}"
       @if($nav('mentor.assessments*')) data-nav-active="1" @endif>
        <span class="si-icon">🧠</span> Assessment Categories
    </a>
    <a href="{{ route('mentor.assessment-questions.index') }}"
       class="sidebar-item {{ $nav('mentor.assessment-questions*') ? 'active' : '' }}"
       @if($nav('mentor.assessment-questions*')) data-nav-active="1" @endif>
        <span class="si-icon">❓</span> Assessment Questions
    </a>

    <div class="sidebar-section-label">Content</div>
    <a href="{{ route('mentor.videos.index') }}"
       class="sidebar-item {{ $nav('mentor.videos*') ? 'active' : '' }}"
       @if($nav('mentor.videos*')) data-nav-active="1" @endif>
        <span class="si-icon">🎬</span> Videos
    </a>
    <a href="{{ route('mentor.community') }}"
       class="sidebar-item {{ $nav('mentor.community*') ? 'active' : '' }}"
       @if($nav('mentor.community*')) data-nav-active="1" @endif>
        <span class="si-icon">💬</span> Community
    </a>
    @endunless

    <div class="sidebar-section-label">Account</div>
    <a href="{{ route('mentor.wallet') }}"
       class="sidebar-item {{ $nav('mentor.wallet*') ? 'active' : '' }}"
       @if($nav('mentor.wallet*')) data-nav-active="1" @endif>
        <span class="si-icon">💰</span> Earnings
        <span style="margin-left:auto;font-size:11px;color:var(--success);">₹{{ number_format(auth()->user()->wallet_balance ?? 0, 0) }}</span>
    </a>
    <a href="{{ route('mentor.profile.edit') }}"
       class="sidebar-item {{ $nav('mentor.profile.*') ? 'active' : '' }}"
       @if($nav('mentor.profile.*')) data-nav-active="1" @endif>
        <span class="si-icon">✏️</span> Edit Profile
    </a>
    <a href="{{ route('account') }}"
       class="sidebar-item {{ $nav('account*') ? 'active' : '' }}"
       @if($nav('account*')) data-nav-active="1" @endif>
        <span class="si-icon">👤</span> Account Settings
    </a>
    <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">
        @csrf
        <button type="submit" class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);border:none;text-align:left;">
            <span class="si-icon">🚪</span> Sign Out
        </button>
    </form>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
@include('frontend.mentors.partials.bottom-nav')
@include('frontend.partials.sidebar-active-scroll')
