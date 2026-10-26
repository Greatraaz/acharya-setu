@php
    $showAppTheme = auth()->check() && request()->routeIs(
        'mentor.*',
        'mentee.*',
        'account*',
        'notifications.*',
        'sessions.call*',
        'mock-interviews.call*'
    );
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $showAppTheme ? 'dark' : 'light' }}" data-app-theme="{{ $showAppTheme ? '1' : '0' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vedrix') — Mentorship Platform</title>
    <meta name="description" content="@yield('meta_description', 'Connect with world-class mentors. Grow your career with Vedrix.')">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=202608041559">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=202608041559">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=202608041559">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="theme-color" content="#f59e0b">
    <script>
        (function () {
            var root = document.documentElement;
            if (root.getAttribute('data-app-theme') !== '1') {
                root.setAttribute('data-theme', 'light');
                return;
            }
            try {
                var t = localStorage.getItem('as_theme');
                if (t) root.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Vedrix">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/vj.css') }}?v={{ filemtime(public_path('frontend/css/vj.css')) }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/app.css') }}?v={{ filemtime(public_path('frontend/css/app.css')) }}">
    <style>
        html, body.vj-site-app { margin: 0 !important; padding: 0 !important; }
        body.vj-site-app {
            --nav-h: 70px;
            --vj-header-h: 70px;
        }
        body.vj-site-app > .vj-header {
            position: sticky;
            top: 0;
            margin: 0;
            z-index: 1040;
        }
        .vj-site-app .dash-layout { padding-top: 0 !important; margin-top: 0; min-height: calc(100vh - var(--vj-header-h)); }
        .vj-site-app .sidebar {
            top: var(--vj-header-h);
            height: calc(100vh - var(--vj-header-h));
            bottom: auto;
        }
        .vj-site-app .auth-wrap { padding-top: 0 !important; min-height: calc(100vh - var(--vj-header-h)); }
        .vj-site-app .modal-overlay:not(.open) {
            display: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        .vj-site-app:has(.dash-layout) .vj-footer {
            margin-left: 240px;
            width: calc(100% - 240px);
        }
        @media (max-width: 1199px) {
            .vj-site-app:has(.dash-layout) .vj-footer {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
    @stack('styles')

    @if(session('success') || session('error') || session('info') || session('warning'))
    <div data-flash='@json([
        "type"    => session("success") ? "success" : (session("error") ? "error" : (session("warning") ? "warning" : "info")),
        "message" => session("success") ?? session("error") ?? session("warning") ?? session("info")
    ])' style="display:none;"></div>
    @endif
</head>
<body class="vj-site vj-site-app">@include('frontend.partials.site-header', ['showAppTheme' => $showAppTheme])

@yield('content')

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

@include('frontend.partials.site-footer')
@include('frontend.partials.site-chrome-scripts')

<script src="{{ asset('frontend/js/app.js') }}?v={{ filemtime(public_path('frontend/js/app.js')) }}"></script>
@auth
    @if(in_array(auth()->user()->role, ['mentor', 'mentee'], true))
        @include('frontend.partials.push-notifications')
    @endif
@endauth
@stack('scripts')
</body>
</html>
