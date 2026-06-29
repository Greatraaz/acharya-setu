<!DOCTYPE html>
<html lang="en" data-theme="{{ auth()->user()?->dark_mode ?? session('theme', 'dark') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AcharyaSetu') — Mentorship Platform</title>
    <meta name="description" content="@yield('meta_description', 'Connect with world-class mentors. Grow your career with AcharyaSetu.')">
    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}">
    @stack('styles')

    {{-- Flash data for JS toast --}}
    @if(session('success') || session('error') || session('info') || session('warning'))
    <div data-flash='@json([
        "type"    => session("success") ? "success" : (session("error") ? "error" : (session("warning") ? "warning" : "info")),
        "message" => session("success") ?? session("error") ?? session("warning") ?? session("info")
    ])' style="display:none;"></div>
    @endif
</head>
<body>

{{-- ═══════════════════════════════════════════════════════ NAVBAR --}}
<nav class="navbar">
    <div class="navbar-inner">
        {{-- Brand --}}
        <a href="{{ route('home') }}" class="navbar-brand">
            <img src="{{ asset('frontend/images/logo.png') }}" alt="AcharyaSetu">
        </a>

        {{-- Desktop Nav --}}
        <ul class="navbar-nav">
            <li><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>Home</a></li>
            <li><a href="{{ route('mentors.search') }}" @class(['active' => request()->routeIs('mentors.*')])>Find Mentors</a></li>
            <li><a href="{{ route('about') }}" @class(['active' => request()->routeIs('about')])>About</a></li>
            <li><a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact')])>Contact</a></li>
        </ul>

        {{-- Right side --}}
        <div class="navbar-right">
            {{-- Theme toggle --}}
            <button class="theme-btn" onclick="toggleTheme()" title="Toggle theme">☀️</button>

            @auth
                @php $user = auth()->user(); @endphp
                <div class="user-menu">
                    <div class="user-trigger">
                        <div class="user-avatar">
                            @if($user->avatar_url)
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                            @else
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div class="user-name">{{ $user->name }}</div>
                            <div class="user-role">{{ ucfirst($user->role) }}</div>
                        </div>
                        <span style="font-size:10px; color:var(--text-3); margin-left:4px;">▾</span>
                    </div>
                    <div class="user-dropdown">
                        @if($user->role === 'mentor')
                            <a class="dropdown-item" href="{{ route('mentor.dashboard') }}">📊 Dashboard</a>
                            <a class="dropdown-item" href="{{ route('mentor.sessions') }}">📅 My Sessions</a>
                            <a class="dropdown-item" href="{{ route('mentor.profile.edit') }}">✏️ Edit Profile</a>
                            <a class="dropdown-item" href="{{ route('mentor.wallet') }}">💰 Wallet</a>
                        @elseif($user->role === 'mentee')
                            <a class="dropdown-item" href="{{ route('mentee.dashboard') }}">📊 Dashboard</a>
                            <a class="dropdown-item" href="{{ route('mentee.sessions') }}">📅 My Sessions</a>
                            <a class="dropdown-item" href="{{ route('mentee.journey.index') }}">🗺️ My Journey</a>
                            <a class="dropdown-item" href="{{ route('mentee.wallet') }}">💰 Wallet</a>
                        @elseif($user->role === 'admin')
                            <a class="dropdown-item" href="{{ route('admin.dashboard') }}">⚙️ Admin Panel</a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item danger w-full" style="text-align:left;">🚪 Sign Out</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Get Started</a>
            @endauth

            {{-- Hamburger --}}
            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

{{-- Mobile Menu --}}
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-inner">
        <a href="{{ route('home') }}" class="mobile-nav-item @if(request()->routeIs('home')) active @endif">🏠 Home</a>
        <a href="{{ route('mentors.search') }}" class="mobile-nav-item @if(request()->routeIs('mentors.*')) active @endif">🔍 Find Mentors</a>
        <a href="{{ route('about') }}" class="mobile-nav-item">ℹ️ About</a>
        <a href="{{ route('contact') }}" class="mobile-nav-item">✉️ Contact</a>
        <div class="divider"></div>
        @auth
            @if(auth()->user()->role === 'mentor')
                <a href="{{ route('mentor.dashboard') }}" class="mobile-nav-item">📊 Dashboard</a>
            @elseif(auth()->user()->role === 'mentee')
                <a href="{{ route('mentee.dashboard') }}" class="mobile-nav-item">📊 Dashboard</a>
            @endif
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="mobile-nav-item w-full" style="background:none;cursor:pointer;text-align:left;">🚪 Sign Out</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="mobile-nav-item">Sign In</a>
            <a href="{{ route('register') }}" class="mobile-nav-item">Get Started →</a>
        @endauth
    </div>
</div>

{{-- Page Content --}}
@yield('content')

{{-- Confirm Modal (global) --}}
<div id="confirm-modal" class="modal-overlay">
    <div class="modal" style="max-width:380px">
        <div class="modal-header">
            <span class="modal-title confirm-title">Are you sure?</span>
            <button class="modal-close">✕</button>
        </div>
        <div class="modal-body">
            <p class="confirm-msg text-muted" style="font-size:14px; margin-bottom:0;"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost btn-sm" onclick="closeModal('confirm-modal')">Cancel</button>
            <button class="btn btn-primary btn-sm confirm-ok">Confirm</button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════ FOOTER --}}
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <img src="{{ asset('frontend/images/logo.png') }}" alt="AcharyaSetu">
                <p>Learning beyond the classroom. Connect with world-class mentors and accelerate your career — one session at a time.</p>
                <div class="footer-social">
                    <a href="#" class="social-btn">𝕏</a>
                    <a href="#" class="social-btn">in</a>
                    <a href="#" class="social-btn">📷</a>
                    <a href="#" class="social-btn">▶️</a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Platform</h4>
                <ul>
                    <li><a href="{{ route('mentors.search') }}">Find Mentors</a></li>
                    <li><a href="{{ route('register') }}?role=mentor">Become a Mentor</a></li>
                    <li><a href="{{ route('about') }}">How It Works</a></li>
                    <li><a href="#">Pricing</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul>
                    <li><a href="{{ route('about') }}">About Us</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Blog</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Legal</h4>
                <ul>
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="{{ route('terms') }}">Terms & Conditions</a></li>
                    <li><a href="#">Refund Policy</a></li>
                    <li><a href="#">Cookie Policy</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© {{ date('Y') }} AcharyaSetu. All rights reserved. Made in India 🇮🇳</p>
            <p>hello@acharyasetu.com</p>
        </div>
    </div>
</footer>

<script src="{{ asset('frontend/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>