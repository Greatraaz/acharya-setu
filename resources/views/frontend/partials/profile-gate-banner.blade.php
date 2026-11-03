@auth
    @php $gateUser = auth()->user(); @endphp
    @if($gateUser && ! $gateUser->isAdmin() && ! $gateUser->canUsePlatform() && ! request()->routeIs('mentor.onboarding*', 'mentee.onboarding*', 'login', 'register'))
        <div class="profile-gate-banner" style="background:rgba(245,158,11,.12);border-bottom:1px solid rgba(245,158,11,.28);padding:10px 16px;text-align:center;font-size:13px;">
            {{ $gateUser->platformGateMessage() }}
            @if($gateUser->isSessionHost() && in_array($gateUser->mentor_status, ['pending', 'rejected'], true) && $gateUser->onboarding_completed)
                <a href="{{ route('mentor.onboarding.pending') }}" style="color:#d97706;font-weight:700;">View status</a>
            @elseif($gateUser->isSessionHost())
                <a href="{{ route('mentor.onboarding', ['step' => 1]) }}" style="color:#d97706;font-weight:700;">Complete profile</a>
            @else
                <a href="{{ route('mentee.onboarding', ['step' => 1]) }}" style="color:#d97706;font-weight:700;">Complete profile</a>
            @endif
        </div>
    @endif
@endauth
