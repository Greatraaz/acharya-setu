{{-- Shared mentee dashboard sidebar — used on every mentee page --}}
@php
    $upcomingCount = $upcomingCount
        ?? \App\Models\ConsultationSession::where('mentee_id', auth()->id())
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('scheduled_at', '>', now())
            ->count();
@endphp
<aside class="sidebar">
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('mentee.dashboard') }}" class="sidebar-item @if(request()->routeIs('mentee.dashboard')) active @endif">
        <span class="si-icon">📊</span> Dashboard
    </a>

    <div class="sidebar-section-label">Learning</div>
    <a href="{{ route('mentee.journey.index') }}" class="sidebar-item @if(request()->routeIs('mentee.journey.*')) active @endif">
        <span class="si-icon">🗺️</span> My Journey
    </a>
    <a href="{{ route('mentors.search') }}" class="sidebar-item @if(request()->routeIs('mentors.search', 'mentors.show')) active @endif">
        <span class="si-icon">🔍</span> Find Mentors
    </a>
    <a href="{{ route('mentee.mentor.change') }}" class="sidebar-item @if(request()->routeIs('mentee.mentor.change')) active @endif">
        <span class="si-icon">🎓</span> My Mentor
    </a>
    <a href="{{ route('mentee.sessions') }}" class="sidebar-item @if(request()->routeIs('mentee.sessions*')) active @endif">
        <span class="si-icon">📅</span> My Sessions
        @if($upcomingCount > 0)<span class="si-badge">{{ $upcomingCount }}</span>@endif
    </a>
    {{-- <a href="{{ route('mentee.assessments.index') }}" class="sidebar-item @if(request()->routeIs('mentee.assessments*')) active @endif">
        <span class="si-icon">📝</span> Assessments
    </a> --}}
    <a href="{{ route('mentee.quizzes.index') }}" class="sidebar-item @if(request()->routeIs('mentee.quizzes*')) active @endif">
        <span class="si-icon">🧠</span> Quizzes
    </a>
    {{-- <a href="{{ route('mentee.wellness.index') }}" class="sidebar-item @if(request()->routeIs('mentee.wellness*')) active @endif">
        <span class="si-icon">🏥</span> Wellness Survey
    </a> --}}

    <div class="sidebar-section-label">Community</div>
    <a href="{{ route('mentee.community.index') }}" class="sidebar-item @if(request()->routeIs('mentee.community*')) active @endif">
        <span class="si-icon">💬</span> Channels
    </a>
    <a href="{{ route('mentee.jobs') }}" class="sidebar-item @if(request()->routeIs('mentee.jobs*')) active @endif">
        <span class="si-icon">💼</span> Job Listings
    </a>

    <div class="sidebar-section-label">Account</div>
    <a href="{{ route('mentee.plans') }}" class="sidebar-item @if(request()->routeIs('mentee.plans*')) active @endif">
        <span class="si-icon">⭐</span> Plans
    </a>
    <a href="{{ route('mentee.wallet') }}" class="sidebar-item @if(request()->routeIs('mentee.wallet*')) active @endif">
        <span class="si-icon">💰</span> Wallet
        <span style="margin-left:auto;font-size:11px;color:var(--brand);">₹{{ number_format(auth()->user()->wallet_balance ?? 0, 0) }}</span>
    </a>
    <a href="{{ route('account') }}" class="sidebar-item @if(request()->routeIs('account*')) active @endif">
        <span class="si-icon">👤</span> Profile Settings
    </a>
    <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">
        @csrf
        <button type="submit" class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);border:none;text-align:left;">
            <span class="si-icon">🚪</span> Sign Out
        </button>
    </form>
</aside>
