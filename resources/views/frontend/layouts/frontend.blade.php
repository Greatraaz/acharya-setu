<!DOCTYPE html>
<html lang="en" data-theme="light" data-app-theme="0">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VEDRIX — Mentors Shape Possibilities | Career Development & Mentorship Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Vedrix is a mentorship-first career development platform connecting students and early professionals with experienced mentors, structured journeys, and measurable progress.')">

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('frontend/css/vj.css') }}?v={{ filemtime(public_path('frontend/css/vj.css')) }}">

    @stack('styles')
</head>
<body class="vj-site">@include('frontend.partials.site-header')

    <main>
        @yield('content')
    </main>

    @include('frontend.partials.site-footer')
    @include('frontend.partials.site-chrome-scripts')

    @stack('scripts')
</body>
</html>
