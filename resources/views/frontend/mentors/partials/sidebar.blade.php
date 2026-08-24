{{-- Shared mentor dashboard sidebar --}}
@php
    $pendingCount = $pendingCount
        ?? \App\Models\ConsultationSession::where('mentor_id', auth()->id())->where('status', 'pending')->count();
@endphp
<aside class="sidebar" id="dashSidebar">
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('mentor.dashboard') }}" class="sidebar-item @if(request()->routeIs('mentor.dashboard')) active @endif">
        <span class="si-icon">📊</span> Dashboard
    </a>

    <div class="sidebar-section-label">Sessions</div>
    <a href="{{ route('mentor.sessions') }}" class="sidebar-item @if(request()->routeIs('mentor.sessions*')) active @endif">
        <span class="si-icon">📅</span> My Sessions
        @if($pendingCount > 0)<span class="si-badge">{{ $pendingCount }}</span>@endif
    </a>
    <a href="{{ route('mentor.availability') }}" class="sidebar-item @if(request()->routeIs('mentor.availability*')) active @endif">
        <span class="si-icon">⏰</span> Set Availability
    </a>
    <a href="{{ route('mentor.notes') }}" class="sidebar-item @if(request()->routeIs('mentor.notes*')) active @endif">
        <span class="si-icon">📝</span> Session Notes
    </a>

    <div class="sidebar-section-label">Mentees</div>
    <a href="{{ route('mentor.requests') }}" class="sidebar-item @if(request()->routeIs('mentor.requests*')) active @endif">
        <span class="si-icon">📨</span> Requests
        @php
            $requestCount = \App\Models\MentorRequest::where('mentor_id', auth()->id())->where('status', 'pending')->count();
        @endphp
        @if($requestCount > 0)<span class="si-badge">{{ $requestCount }}</span>@endif
    </a>
    <a href="{{ route('mentor.mentees') }}" class="sidebar-item @if(request()->routeIs('mentor.mentees*')) active @endif">
        <span class="si-icon">🎓</span> My Mentees
    </a>
    <a href="{{ route('mentor.curriculum.tracks') }}" class="sidebar-item @if(request()->routeIs('mentor.curriculum*')) active @endif">
        <span class="si-icon">🗺️</span> Curriculum
    </a>
    <a href="{{ route('mentor.journey') }}" class="sidebar-item @if(request()->routeIs('mentor.journey*')) active @endif">
        <span class="si-icon">📈</span> Progress Tracker
    </a>

    <div class="sidebar-section-label">Content</div>
    <a href="{{ route('mentor.community') }}" class="sidebar-item @if(request()->routeIs('mentor.community*')) active @endif">
        <span class="si-icon">💬</span> Community
    </a>

    <div class="sidebar-section-label">Assessments</div>
    <a href="{{ route('mentor.assessments.index') }}" class="sidebar-item @if(request()->routeIs('mentor.assessments*')) active @endif">
        <span class="si-icon">🧠</span> Categories
    </a>
    <a href="{{ route('mentor.assessment-questions.index') }}" class="sidebar-item @if(request()->routeIs('mentor.assessment-questions*')) active @endif">
        <span class="si-icon">❓</span> Questions
    </a>

    <div class="sidebar-section-label">Account</div>
    <a href="{{ route('mentor.wallet') }}" class="sidebar-item @if(request()->routeIs('mentor.wallet*')) active @endif">
        <span class="si-icon">💰</span> Earnings
        <span style="margin-left:auto;font-size:11px;color:var(--success);">₹{{ number_format(auth()->user()->wallet_balance ?? 0, 0) }}</span>
    </a>
    <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item @if(request()->routeIs('mentor.profile.*')) active @endif">
        <span class="si-icon">✏️</span> Edit Profile
    </a>
    <a href="{{ route('account') }}" class="sidebar-item @if(request()->routeIs('account*')) active @endif">
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
