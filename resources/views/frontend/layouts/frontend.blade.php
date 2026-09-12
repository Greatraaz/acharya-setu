<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VEDRIX — Mentors Shape Possibilities | Career Development & Mentorship Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Vedrix is a mentorship-first career development platform connecting students and early professionals with experienced mentors, structured journeys, and measurable progress.')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom vj.css -->
    <link rel="stylesheet" href="{{ asset('frontend/css/vj.css') }}?v={{ time() }}">
    
    @stack('styles')
</head>
<body>

    <!-- =========================================================================
         HEADER & MAIN WEBSITE NAVIGATION WITH 6 MEGA MENUS
         ========================================================================= -->
    <header class="vj-header">
        <div class="container-fluid px-lg-5">
            <div class="vj-navbar">
                <!-- Brand Logo -->
                <a href="{{ url('') }}" class="vj-brand">
                    <img src="{{ asset('frontend/images/logo-light.png') }}" alt="Vedrix" class="vj-brand-logo" style="height: 55px;">
                </a>

                <!-- Primary Desktop Navigation -->
                <nav class="d-none d-xl-block">
                    <ul class="vj-nav-menu">
                        <!-- Home -->
                        <li class="vj-nav-item {{ request()->is('/') || request()->is('home') ? 'active' : '' }}">
                            <a href="{{ url('home') }}" class="vj-nav-link">Home</a>
                        </li>

                        <!-- =========================================================================
                             MEGA MENU 1 — DISCOVER VEDRIX (Layout: Elevate One Media Pill Grid)
                             ========================================================================= -->
                        <li class="vj-nav-item">
                            <a href="#" class="vj-nav-link">
                                Discover Vedrix <i class="bi bi-chevron-down vj-nav-caret"></i>
                            </a>
                            <div class="vj-mega-menu">
                                <div class="container-fluid px-0">
                                    <div class="row g-4 align-items-stretch">
                                        <!-- Left Column: Vision Feature Box -->
                                        <div class="col-lg-3">
                                            <div class="vj-elevate-left-card">
                                                <div>
                                                    <span class="vj-mega-eyebrow">WHO WE ARE</span>
                                                    <h5>Mentorship-First Career Development</h5>
                                                    <div class="lain"></div>
                                                    <p class="text-muted small mb-3">Guiding ambitious learners with structured execution and measurable direction.</p>
                                                </div>
                                                <div class="vj-elevate-thumb-box">
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <i class="bi bi-patch-check-fill text-warning fs-5"></i>
                                                        <span class="fw-bold fs-6">Verified Quality</span>
                                                    </div>
                                                    <p class="small text-white mb-0">Every mentor passes our strict 5-stage background & expertise screening.</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Middle Column: 2x3 Grid of Pill Cards -->
                                        <div class="col-lg-6">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">CORE FOUNDATIONS</span>
                                                <h6 class="vj-mega-heading">Explore Platform & Philosophy</h6>
                                            </div>
                                            <div class="vj-elevate-pill-grid">
                                                <a href="{{ url('our-story-and-vision') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-book text-warning fs-5"></i>
                                                    <span>Our Story & Vision</span>
                                                </a>
                                                <a href="{{ url('why-vedrix-exists') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-bullseye text-warning fs-5"></i>
                                                    <span>Why Vedrix Exists</span>
                                                </a>
                                                <a href="{{ url('the-methodology') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-diagram-3 text-warning fs-5"></i>
                                                    <span>The Methodology</span>
                                                </a>
                                                <a href="{{ url('what-makes-us-different') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-stars text-warning fs-5"></i>
                                                    <span>What Makes Us Different</span>
                                                </a>
                                                <a href="{{ url('trust-and-standards') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-shield-check text-warning fs-5"></i>
                                                    <span>Trust & Standards</span>
                                                </a>
                                                <a href="{{ url('how-vedrix-works') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-gear-wide-connected text-warning fs-5"></i>
                                                    <span>How Vedrix Works</span>
                                                </a>
                                                <a href="{{ url('structured-career-journeys') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-signpost-split text-warning fs-5"></i>
                                                    <span>Structured Career Journeys</span>
                                                </a>
                                                <a href="{{ url('goal-based-mentorship') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-crosshair text-warning fs-5"></i>
                                                    <span>Goal-Based Mentorship</span>
                                                </a>
                                                <a href="{{ url('progress-and-outcomes') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-graph-up-arrow text-warning fs-5"></i>
                                                    <span>Progress & Outcomes</span>
                                                </a>
                                            </div>                                            
                                            <div class="vj-mega-section-head m-1 mt-3">
                                                <h6 class="vj-mega-heading">Explore Vedrix</h6>
                                            </div>
                                            <p class="vj-elevate-subtext">
                                                <a href="{{ url('for-students') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-mortarboard text-warning fs-5"></i>
                                                    <span>For Students</span>
                                                </a>
                                                <a href="{{ url('for-graduates') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-award text-warning fs-5"></i>
                                                    <span>For Graduates</span>
                                                </a>
                                                <a href="{{ url('for-mentors') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-person-badge text-warning fs-5"></i>
                                                    <span>For Mentors</span>
                                                </a>
                                                <a href="{{ url('for-employers') }}" class="vj-elevate-pill-item">
                                                    <i class="bi bi-building text-warning fs-5"></i>
                                                    <span>For Employers</span>
                                                </a>
                                            </p>
                                        </div>

                                        <!-- Right Column: Spotlight Call-to-Action -->
                                        <div class="col-lg-3">
                                            <div class="vj-mega-spotlight-card vj-spotlight-bg-discover">
                                                <div>
                                                    <div class="vj-spotlight-badge">
                                                        <i class="bi bi-stars"></i> Philosophy
                                                    </div>
                                                    <h5 class="vj-spotlight-title">Talent is everywhere. Direction isn't.</h5>
                                                    <p class="vj-spotlight-desc">Vedrix transforms career guidance from occasional advice into structured journeys built around real-world milestones.</p>
                                                </div>
                                                <a href="{{ url('about-vedrix') }}" class="vj-spotlight-btn">
                                                    About Vedrix <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- =========================================================================
                             MEGA MENU 2 — YOUR JOURNEY (Layout: Zindagi Circle 4x2 Visual Tiles Grid)
                             ========================================================================= -->
                        <li class="vj-nav-item">
                            <a href="#" class="vj-nav-link">
                                Your Journey <i class="bi bi-chevron-down vj-nav-caret"></i>
                            </a>
                            <div class="vj-mega-menu">
                                <div class="container-fluid px-0">
                                    <div class="row g-4 align-items-stretch">
                                        <!-- Main Left/Center: 4x2 Visual Stage Tiles -->
                                        <div class="col-lg-8">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">ROADMAP STAGES</span>
                                                <h6 class="vj-mega-heading">
                                                    <span>Career Readiness Journey</span>
                                                    <a href="{{ url('view-full-roadmap') }}" class="vj-mega-view-all">View Full Roadmap <i class="bi bi-arrow-right"></i></a>
                                                </h6>
                                            </div>
                                            <div class="vj-tiles-grid-4x2">
                                                <!-- Tile 1 -->
                                                <a href="{{ url('career-discovery') }}" class="vj-journey-tile vj-tile-bg-1">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-blue">
                                                            <i class="bi bi-compass"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 01</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Career Discovery</div>
                                                </a>
                                                <!-- Tile 2 -->
                                                <a href="{{ url('readiness-assessment') }}" class="vj-journey-tile vj-tile-bg-2">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-amber">
                                                            <i class="bi bi-clipboard2-pulse"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 02</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Readiness Assessment</div>
                                                </a>
                                                <!-- Tile 3 -->
                                                <a href="{{ url('goal-planning') }}" class="vj-journey-tile vj-tile-bg-3">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-purple">
                                                            <i class="bi bi-calendar2-range"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 03</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Goal Planning</div>
                                                </a>
                                                <!-- Tile 4 -->
                                                <a href="{{ url('skills-readiness') }}" class="vj-journey-tile vj-tile-bg-4">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-teal">
                                                            <i class="bi bi-lightning-charge"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 04</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Skills Readiness</div>
                                                </a>
                                                <!-- Tile 5 -->
                                                <a href="{{ url('interview-gym') }}" class="vj-journey-tile vj-tile-bg-5">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-rose">
                                                            <i class="bi bi-camera-video"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 05</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Interview Gym</div>
                                                </a>
                                                <!-- Tile 6 -->
                                                <a href="{{ url('portfolio-building') }}" class="vj-journey-tile vj-tile-bg-6">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-green">
                                                            <i class="bi bi-folder-check"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 06</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Portfolio Building</div>
                                                </a>
                                                <!-- Tile 7 -->
                                                <a href="{{ url('weekly-action-plans') }}" class="vj-journey-tile vj-tile-bg-7">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-indigo">
                                                            <i class="bi bi-list-check"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 07</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Weekly Action Plans</div>
                                                </a>
                                                <!-- Tile 8 -->
                                                <a href="{{ url('progress-score') }}" class="vj-journey-tile vj-tile-bg-8">
                                                    <div class="vj-tile-header-area">
                                                        <div class="vj-tile-icon-wrap chip-mustard">
                                                            <i class="bi bi-speedometer2"></i>
                                                        </div>
                                                        <span class="vj-tile-stage-tag">Stage 08</span>
                                                    </div>
                                                    <div class="vj-tile-footer-label">Progress Score</div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Right Column: Diagnostic Action Card -->
                                        <div class="col-lg-4">
                                            <div class="vj-mega-spotlight-card vj-spotlight-bg-journey">
                                                <div>
                                                    <div class="vj-spotlight-badge">
                                                        <i class="bi bi-bullseye"></i> Readiness Diagnostic
                                                    </div>
                                                    <h5 class="vj-spotlight-title">Not sure where you stand?</h5>
                                                    <p class="vj-spotlight-desc">Take the 5-minute Vedrix Career Readiness Diagnostic. Benchmark your interview, resume, and skills readiness.</p>
                                                </div>
                                                <a href="{{ url('start-assessment') }}" class="vj-spotlight-btn">
                                                    Start Assessment <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- =========================================================================
                             MEGA MENU 3 — MENTORS (Layout: Meet Richa 3-Column Asymmetric)
                             ========================================================================= -->
                        <li class="vj-nav-item">
                            <a href="#" class="vj-nav-link">
                                Mentors <i class="bi bi-chevron-down vj-nav-caret"></i>
                            </a>
                            <div class="vj-mega-menu">
                                <div class="container-fluid px-0">
                                    <div class="row g-4 align-items-stretch">
                                        <!-- Column 1: Stacked Engagement Formats -->
                                        <div class="col-lg-4">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">ENGAGEMENT FORMATS</span>
                                                <h6 class="vj-mega-heading">Mentorship Models</h6>
                                            </div>
                                            <a href="{{ url('1-on-1-video-sessions') }}" class="vj-stacked-card">
                                                <div class="vj-mega-icon-chip chip-teal">
                                                    <i class="bi bi-person-video3"></i>
                                                </div>
                                                <div>
                                                    <div class="vj-card-head">1-on-1 Video Sessions</div>
                                                    <div class="vj-card-desc">Deep 45-min personalized strategy and mock reviews</div>
                                                </div>
                                            </a>
                                            <a href="{{ url('goal-based-sprints') }}" class="vj-stacked-card">
                                                <div class="vj-mega-icon-chip chip-rose">
                                                    <i class="bi bi-rocket-takeoff"></i>
                                                </div>
                                                <div>
                                                    <div class="vj-card-head">Goal-Based Sprints</div>
                                                    <div class="vj-card-desc">Multi-week structured guidance for career transitions</div>
                                                </div>
                                            </a>
                                            <a href="{{ url('group-mentoring') }}" class="vj-stacked-card">
                                                <div class="vj-mega-icon-chip chip-indigo">
                                                    <i class="bi bi-people-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="vj-card-head">Group Mentoring</div>
                                                    <div class="vj-card-desc">Quick 15-min drop-in Q&A and priority query solving</div>
                                                </div>
                                            </a>
                                        </div>

                                        <!-- Column 2: 2x2 Grid of Expertise Areas -->
                                        <div class="col-lg-4">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">TOP EXPERTISE</span>
                                                <h6 class="vj-mega-heading">
                                                    <span>Browse by Domain</span>
                                                    <a href="{{ url('all-mentors') }}" class="vj-mega-view-all">All Mentors <i class="bi bi-arrow-right"></i></a>
                                                </h6>
                                            </div>
                                            <div class="vj-grid-2x2">
                                                <a href="{{ url('tech-and-ai') }}" class="vj-grid-box-card">
                                                    <div class="vj-mega-icon-chip chip-blue">
                                                        <i class="bi bi-code-slash"></i>
                                                    </div>
                                                    <span class="vj-grid-title">Tech & AI</span>
                                                    <span class="vj-grid-sub">Engineering & Data Science</span>
                                                </a>
                                                <a href="{{ url('product-and-design') }}" class="vj-grid-box-card">
                                                    <div class="vj-mega-icon-chip chip-purple">
                                                        <i class="bi bi-kanban"></i>
                                                    </div>
                                                    <span class="vj-grid-title">Product & Design</span>
                                                    <span class="vj-grid-sub">UI/UX & Product Management</span>
                                                </a>
                                                <a href="{{ url('leadership-and-cxo') }}" class="vj-grid-box-card">
                                                    <div class="vj-mega-icon-chip chip-amber">
                                                        <i class="bi bi-briefcase-fill"></i>
                                                    </div>
                                                    <span class="vj-grid-title">Leadership & CXO</span>
                                                    <span class="vj-grid-sub">Executive Mentorship</span>
                                                </a>
                                                <a href="{{ url('finance-and-strategy') }}" class="vj-grid-box-card">
                                                    <div class="vj-mega-icon-chip chip-green">
                                                        <i class="bi bi-pie-chart-fill"></i>
                                                    </div>
                                                    <span class="vj-grid-title">Finance & Strategy</span>
                                                    <span class="vj-grid-sub">Fintech & Consulting</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Column 3: Mentor Spotlight -->
                                        <div class="col-lg-4">
                                            <div class="vj-mega-spotlight-card vj-spotlight-bg-mentors">
                                                <div>
                                                    <div class="vj-spotlight-badge">
                                                        <i class="bi bi-award"></i> Give Back
                                                    </div>
                                                    <h5 class="vj-spotlight-title">Share your journey. Shape future leaders.</h5>
                                                    <p class="vj-spotlight-desc">Experience becomes powerful when it becomes someone else's direction. Join our verified mentor community.</p>
                                                </div>
                                                <a href="{{ url('become-a-mentor') }}" class="vj-spotlight-btn">
                                                    Become a Mentor <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- =========================================================================
                             MEGA MENU 4 — FOR ORGANISATIONS (Layout: Who We Serve Domain Cards Grid)
                             ========================================================================= -->
                        <li class="vj-nav-item">
                            <a href="#" class="vj-nav-link">
                                For Organisations <i class="bi bi-chevron-down vj-nav-caret"></i>
                            </a>
                            <div class="vj-mega-menu">
                                <div class="container-fluid px-0">
                                    <div class="row g-4 align-items-stretch">
                                        <!-- Main Left/Center: 2x3 Domain Cards Grid -->
                                        <div class="col-lg-8">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">ENTERPRISE & ACADEMIA SOLUTIONS</span>
                                                <h6 class="vj-mega-heading">Who We Partner With</h6>
                                            </div>
                                            <div class="vj-domain-grid">
                                                <a href="{{ url('colleges-and-universities') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-blue">
                                                        <i class="bi bi-buildings"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Colleges & Universities</span>
                                                        <span class="vj-domain-desc">Embed verified mentorship into academic curricula</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('training-and-placement-cells') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-amber">
                                                        <i class="bi bi-mortarboard-fill"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Training & Placement Cells</span>
                                                        <span class="vj-domain-desc">Intensive mock interview clinics & placement cohorts</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('campus-alumni-networks') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-green">
                                                        <i class="bi bi-people"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Campus Alumni Networks</span>
                                                        <span class="vj-domain-desc">Connect current batches with established alumni</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('employability-diagnostics') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-teal">
                                                        <i class="bi bi-clipboard2-data"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Employability Diagnostics</span>
                                                        <span class="vj-domain-desc">Benchmark batch skills with predictive scorecards</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('corporate-graduate-hiring') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-purple">
                                                        <i class="bi bi-briefcase"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Corporate Graduate Hiring</span>
                                                        <span class="vj-domain-desc">Accelerate early-career hire onboarding & impact</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('csr-and-youth-foundations') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-rose">
                                                        <i class="bi bi-heart"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">CSR & Youth Foundations</span>
                                                        <span class="vj-domain-desc">Empower underprivileged youth via guided roadmaps</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('interview-preparation-clinics') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-blue">
                                                        <i class="bi bi-person-workspace"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Interview Preparation Clinics</span>
                                                        <span class="vj-domain-desc">Embed verified mentorship into academic curricula</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('industry-mentor-access') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-green">
                                                        <i class="bi bi-person-check"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Industry Mentor Access</span>
                                                        <span class="vj-domain-desc">Connect current batches with established alumni</span>
                                                    </div>
                                                </a>
                                                <a href="{{ url('graduate-development-programs') }}" class="vj-domain-card">
                                                    <div class="vj-domain-icon-sq chip-purple">
                                                        <i class="bi bi-trophy"></i>
                                                    </div>
                                                    <div>
                                                        <span class="vj-domain-title">Graduate Development Programs</span>
                                                        <span class="vj-domain-desc">Accelerate early-career hire onboarding & impact</span>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Right Column: Institutional Scale Spotlight -->
                                        <div class="col-lg-4">
                                            <div class="vj-mega-spotlight-card vj-spotlight-bg-organisations">
                                                <div>
                                                    <div class="vj-spotlight-badge">
                                                        <i class="bi bi-buildings"></i> Institutional Scale
                                                    </div>
                                                    <h5 class="vj-spotlight-title">Build career readiness at scale.</h5>
                                                    <p class="vj-spotlight-desc">Give every learner access to structured guidance while giving your institution complete visibility into progress.</p>
                                                </div>
                                                <a href="{{ url('partner-with-vedrix') }}" class="vj-spotlight-btn">
                                                    Partner with Vedrix <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- =========================================================================
                             MEGA MENU 5 — INSIGHTS (Layout: Richa Writes Magazine Layout)
                             ========================================================================= -->
                        <li class="vj-nav-item">
                            <a href="#" class="vj-nav-link">
                                Insights <i class="bi bi-chevron-down vj-nav-caret"></i>
                            </a>
                            <div class="vj-mega-menu">
                                <div class="container-fluid px-0">
                                    <div class="row g-4 align-items-stretch">
                                        <!-- Column 1: Vertical Topic List -->
                                        <div class="col-lg-2">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">EXPLORE</span>
                                                <h6 class="vj-mega-heading">Themes & Topics</h6>
                                            </div>
                                            <a href="{{ url('career-guides') }}" class="vj-mag-list-item">
                                                <i class="bi bi-compass text-warning"></i>
                                                <span>Career Guides</span>
                                            </a>
                                            <a href="{{ url('mentorship-guides') }}" class="vj-mag-list-item">
                                                <i class="bi bi-lightbulb text-danger"></i>
                                                <span>Mentorship Guides</span>
                                            </a>
                                            <a href="{{ url('industry-reports') }}" class="vj-mag-list-item">
                                                <i class="bi bi-file-earmark-bar-graph text-primary"></i>
                                                <span>Industry Reports</span>
                                            </a>
                                            <a href="{{ url('learner-success-stories') }}" class="vj-mag-list-item">
                                                <i class="bi bi-stars text-success"></i>
                                                <span>Learner Success Stories</span>
                                            </a>
                                            <a href="{{ url('download-centre') }}" class="vj-mag-list-item">
                                                <i class="bi bi-download text-info"></i>
                                                <span>Download Centre</span>
                                            </a>
                                        </div>

                                        <!-- Column 2: Horizontal Article Cards -->
                                        <div class="col-lg-6">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">WATCH & LISTEN</span>
                                                <h6 class="vj-mega-heading">
                                                    <span>Curated Essays & Media</span>
                                                    <a href="{{ url('curated-essays-and-media') }}" class="vj-mega-view-all">View All <i class="bi bi-arrow-right"></i></a>
                                                </h6>
                                            </div>
                                            <div class="vj-grid-2x2">
                                                <a href="{{ url('webinars') }}" class="vj-mag-article-card">
                                                    <div class="vj-mag-thumb chip-amber">
                                                        <i class="bi bi-easel"></i>
                                                    </div>
                                                    <div>
                                                        <div class="vj-mag-title">Webinars</div>
                                                        <div class="vj-mag-desc">Step-by-step strategy for systemic problem-solving</div>
                                                    </div>
                                                </a>
                                                <a href="{{ url('podcasts') }}" class="vj-mag-article-card">
                                                    <div class="vj-mag-thumb chip-rose">
                                                        <i class="bi bi-headphones"></i>
                                                    </div>
                                                    <div>
                                                        <div class="vj-mag-title">Podcasts</div>
                                                        <div class="vj-mag-desc">Unfiltered career lessons with seasoned tech leaders</div>
                                                    </div>
                                                </a>
                                                <a href="{{ url('case-studies') }}" class="vj-mag-article-card">
                                                    <div class="vj-mag-thumb chip-indigo">
                                                        <i class="bi bi-journal-check"></i>
                                                    </div>
                                                    <div>
                                                        <div class="vj-mag-title">Case Studies</div>
                                                        <div class="vj-mag-desc">Proven templates used by successful job seekers</div>
                                                    </div>
                                                </a>
                                                <a href="{{ url('blogs') }}" class="vj-mag-article-card">
                                                    <div class="vj-mag-thumb chip-amber">
                                                        <i class="bi bi-newspaper"></i>
                                                    </div>
                                                    <div>
                                                        <div class="vj-mag-title">Blogs</div>
                                                        <div class="vj-mag-desc">Step-by-step strategy for systemic problem-solving</div>
                                                    </div>
                                                </a>
                                                <a href="{{ url('white-papers') }}" class="vj-mag-article-card">
                                                    <div class="vj-mag-thumb chip-rose">
                                                        <i class="bi bi-file-earmark-pdf"></i>
                                                    </div>
                                                    <div>
                                                        <div class="vj-mag-title">White Papers</div>
                                                        <div class="vj-mag-desc">Unfiltered career lessons with seasoned tech leaders</div>
                                                    </div>
                                                </a>
                                                <a href="{{ url('videos') }}" class="vj-mag-article-card">
                                                    <div class="vj-mag-thumb chip-indigo">
                                                        <i class="bi bi-play-circle"></i>
                                                    </div>
                                                    <div>
                                                        <div class="vj-mag-title">Videos</div>
                                                        <div class="vj-mag-desc">Proven templates used by successful job seekers</div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Column 3: Insights Spotlight Card -->
                                        <div class="col-lg-4">
                                            <div class="vj-mega-spotlight-card vj-spotlight-bg-insights">
                                                <div>
                                                    <div class="vj-spotlight-badge">
                                                        <i class="bi bi-lightbulb"></i> Knowledge Hub
                                                    </div>
                                                    <h5 class="vj-spotlight-title">Insights That Propel You Ahead.</h5>
                                                    <p class="vj-spotlight-desc">Actionable playbooks, podcast dialogues, and research to keep you ahead in modern hiring markets.</p>
                                                </div>
                                                <a href="{{ url('explore-all-insights') }}" class="vj-spotlight-btn">
                                                    Explore All Insights <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <!-- =========================================================================
                             MEGA MENU 6 — CONTACT (Layout: Community Voice & Action Hub)
                             ========================================================================= -->
                        <li class="vj-nav-item">
                            <a href="#" class="vj-nav-link">
                                Contact <i class="bi bi-chevron-down vj-nav-caret"></i>
                            </a>
                            <div class="vj-mega-menu">
                                <div class="container-fluid px-0">
                                    <div class="row g-4 align-items-stretch">
                                        <!-- Column 1: Community Voice Promo Box -->
                                        <div class="col-lg-2">
                                            <div class="vj-voice-promo-card">
                                                <div>
                                                    <span class="vj-voice-tag"><i class="bi bi-chat-heart"></i> DIRECT CONNECT</span>
                                                    <div class="vj-voice-title">We're Here to Help</div>
                                                    <div class="lain"></div>
                                                    <p class="vj-voice-desc">Need assistance choosing a roadmap or reaching our team? Talk to our advisory counselors.</p>
                                                </div>
                                                <div>
                                                    <a href="{{ url('contact-vedrix') }}" class="vj-voice-btn-primary w-100">
                                                        <i class="bi bi-calendar-event"></i> Contact Vedrix
                                                    </a>
                                                    <a href="{{ url('view-help-faqs') }}" class="vj-voice-btn-outline w-100">
                                                        <i class="bi bi-question-circle"></i> View Help FAQs
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Column 2: 2x2 Grid of Direct Channels -->
                                        <div class="col-lg-7">
                                            <div class="vj-mega-section-head">
                                                <span class="vj-mega-eyebrow">SUPPORT & INQUIRIES</span>
                                                <h6 class="vj-mega-heading">
                                                    <span>Choose Your Channel</span>
                                                    <a href="{{ url('all-options') }}" class="vj-mega-view-all">All Options <i class="bi bi-arrow-right"></i></a>
                                                </h6>
                                            </div>
                                            <div class="vj-contact-actions-grid">
                                                <a href="{{ url('general-enquiries') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-blue">
                                                        <i class="bi bi-chat-dots"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">General Enquiries</span>
                                                </a>
                                                <a href="{{ url('employer-enquiries') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-rose">
                                                        <i class="bi bi-briefcase"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Employer Enquiries</span>
                                                </a>
                                                <a href="{{ url('institution-enquiries') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-purple">
                                                        <i class="bi bi-buildings"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Institution Enquiries</span>
                                                </a>
                                                <a href="{{ url('partnership-enquiries') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-rose">
                                                        <i class="bi bi-diagram-3"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Partnership Enquiries</span>
                                                </a>
                                                <a href="{{ url('booking-support') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-purple">
                                                        <i class="bi bi-calendar-check"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Booking Support</span>
                                                </a>
                                                <a href="{{ url('find-a-mentor-help') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-rose">
                                                        <i class="bi bi-search"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Find a Mentor Help</span>
                                                </a>
                                                <a href="{{ url('student-learner-enquiries') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-blue">
                                                        <i class="bi bi-mortarboard"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Student / Learner Enquiries</span>
                                                </a>
                                                <a href="{{ url('payment-support') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-amber">
                                                        <i class="bi bi-credit-card"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Payment Support</span>
                                                </a>
                                                <a href="{{ url('raise-a-support-request') }}" class="vj-contact-action-tile">
                                                    <div class="vj-mega-icon-chip chip-purple">
                                                        <i class="bi bi-ticket-detailed"></i>
                                                    </div>
                                                    <span class="vj-contact-action-title">Raise a Support Request</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Column 3: Priority Support Spotlight -->
                                        <div class="col-lg-3">
                                            <div class="vj-mega-spotlight-card vj-spotlight-bg-contact">
                                                <div>
                                                    <div class="vj-spotlight-badge">
                                                        <i class="bi bi-headset"></i> Get in Touch
                                                    </div>
                                                    <h5 class="vj-spotlight-title">Tell us where you want to go.</h5>
                                                    <p class="vj-spotlight-desc">We'll help you identify the right next step, match you with suitable mentors, or customize an institutional cohort.</p>
                                                </div>
                                                <a href="{{ url('open-contact-desk') }}" class="vj-spotlight-btn">
                                                    Open Contact Desk <i class="bi bi-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Right-side actions -->
                <div class="vj-nav-actions">
                   <!--  @auth
                        @php $u = auth()->user(); @endphp
                        @if($u->role === 'mentor')
                            <a href="{{ route('mentor.dashboard') }}" class="vj-btn vj-btn-primary d-none d-md-inline-flex">Dashboard</a>
                        @elseif($u->role === 'mentee')
                            <a href="{{ route('mentee.dashboard') }}" class="vj-btn vj-btn-primary d-none d-md-inline-flex">Dashboard</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="vj-btn vj-btn-primary d-none d-md-inline-flex">Admin</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="vj-login-link border-0 bg-transparent">Sign Out</button>
                        </form>
                    @else -->
                        <!-- <a href="{{ route('login') }}" class="vj-login-link">Login</a> -->
                        <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary d-none d-md-inline-flex">
                            <i class="bi bi-search"></i> Find a Mentor
                        </a>
                        <!-- <a href="{{ route('register') }}?role=mentor" class="vj-btn vj-btn-secondary d-none d-xxl-inline-flex">
                            Become a Mentor
                        </a>
                    @endauth -->

                    <!-- Mobile Hamburger Toggle -->
                    <button class="btn vj-btn-outline-mustard d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#vjMobileNav" aria-controls="vjMobileNav">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Offcanvas Drawer -->
    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="vjMobileNav" aria-labelledby="vjMobileNavLabel" style="background-color: #12192C !important;">
        <div class="offcanvas-header border-bottom border-secondary">
            <div class="d-flex align-items-center">
                <img src="{{ asset('frontend/images/logo-dark.png') }}" alt="Vedrix" style="height: 36px; width: auto;">
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-4">
            <div class="d-grid gap-2 mb-4">
                <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary justify-content-center">Find a Mentor</a>
                <a href="{{ route('register') }}?role=mentor" class="vj-btn vj-btn-secondary justify-content-center">Become a Mentor</a>
            </div>
            <ul class="list-unstyled">
                <li class="py-2 border-bottom border-secondary"><a href="{{ url('home') }}" class="text-white text-decoration-none fw-bold">Home</a></li>
                <li class="py-2 border-bottom border-secondary"><a href="{{ url('about') }}" class="text-white text-decoration-none fw-bold">Discover Vedrix</a></li>
                <li class="py-2 border-bottom border-secondary"><a href="{{ url('home') }}#journeys" class="text-white text-decoration-none fw-bold">Your Journey</a></li>
                <li class="py-2 border-bottom border-secondary"><a href="{{ route('mentors.search') }}" class="text-white text-decoration-none fw-bold">Mentors</a></li>
                <li class="py-2 border-bottom border-secondary"><a href="{{ url('home') }}#organisations" class="text-white text-decoration-none fw-bold">For Organisations</a></li>
                <li class="py-2 border-bottom border-secondary"><a href="{{ route('insights.blogs.index') }}" class="text-white text-decoration-none fw-bold">Insights</a></li>
                <li class="py-2 border-bottom border-secondary"><a href="{{ url('contact') }}" class="text-white text-decoration-none fw-bold">Contact</a></li>
                @guest
                    <li class="py-2 mt-3"><a href="{{ route('login') }}" class="vj-btn vj-btn-outline-mustard d-block text-center">Login / Sign Up</a></li>
                @endguest
            </ul>
        </div>
    </div>

    <!-- =========================================================================
         BODY CONTENT WRAPPER
         ========================================================================= -->
    <main>
        @yield('content')
    </main>

    <!-- =========================================================================
         FOOTER STRUCTURE
         ========================================================================= -->
    <footer class="vj-footer">
        <div class="container-fluid px-lg-5">
            <div class="row g-4">
                <!-- Brand Column -->
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('frontend/images/logo-dark.png') }}" alt="Vedrix" style="height: 60px; width: auto;">
                    </div>
                    <p class="fw-bold text-white mb-2" style="font-size: 17px; color: #FFA502 !important;">Mentors Shape Possibilities.</p>
                    <p style="color: #CBD5E1; font-size: 14.5px; line-height: 1.6;">
                        Vedrix connects ambition with experience through structured mentorship, career-readiness tools and measurable development journeys.
                    </p>
                    <div class="d-flex gap-2 mt-4">
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-sm vj-btn-outline-mustard"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <!-- Column 2: Discover -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Discover</h4>
                    <ul>
                        <li><a href="{{ url('about') }}">About Vedrix</a></li>
                        <li><a href="{{ url('about') }}#why">Why Vedrix</a></li>
                        <li><a href="{{ url('about') }}#how-it-works">How It Works</a></li>
                        <li><a href="{{ url('about') }}#mission">Our Philosophy</a></li>
                        <li><a href="{{ url('about') }}#trust">Trust & Safety</a></li>
                        <li><a href="{{ url('about') }}#faqs">FAQs</a></li>
                    </ul>
                </div>

                <!-- Column 3: Your Journey -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Your Journey</h4>
                    <ul>
                        <li><a href="{{ url('home') }}#assessments">Assessments</a></li>
                        <li><a href="{{ url('home') }}#paths">Career Paths</a></li>
                        <li><a href="{{ url('home') }}#journeys">Mentorship Journeys</a></li>
                        <li><a href="{{ url('home') }}#studio">Career Readiness</a></li>
                        <li><a href="{{ url('home') }}#studio">Interview Gym</a></li>
                        <li><a href="{{ url('home') }}#studio">Resume Studio</a></li>
                        <li><a href="{{ url('home') }}#studio">LinkedIn Studio</a></li>
                        <li><a href="{{ url('home') }}#studio">Project Vault</a></li>
                    </ul>
                </div>

                <!-- Column 4: Mentors & Organisations -->
                <div class="col-lg-2 col-md-6 col-6">
                    <h4>Mentors</h4>
                    <ul class="mb-4">
                        <li><a href="{{ route('mentors.search') }}">Find a Mentor</a></li>
                        <li><a href="{{ route('mentors.search') }}">Mentor Categories</a></li>
                        <li><a href="{{ url('home') }}#experiences">Office Hours</a></li>
                        <li><a href="{{ route('register') }}?role=mentor">Become a Mentor</a></li>
                        <li><a href="{{ url('home') }}#mentors">Mentor Resources</a></li>
                    </ul>

                    <h4>Organisations</h4>
                    <ul>
                        <li><a href="{{ url('home') }}#organisations">Colleges & Universities</a></li>
                        <li><a href="{{ url('home') }}#organisations">Placement Cells</a></li>
                        <li><a href="{{ url('home') }}#organisations">Employers</a></li>
                        <li><a href="{{ url('home') }}#organisations">Partnerships</a></li>
                    </ul>
                </div>

                <!-- Column 5: Insights & Support -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h4>Insights</h4>
                    <ul class="mb-4">
                        <li><a href="{{ route('insights.blogs.index') }}">Articles & Career Guides</a></li>
                        <li><a href="{{ route('insights.white-papers.index') }}">Research & Reports</a></li>
                        <li><a href="{{ route('insights.webinars.index') }}">Webinars & Events</a></li>
                        <li><a href="{{ route('insights.podcasts.index') }}">Podcasts & Videos</a></li>
                        <li><a href="{{ route('insights.download-centre.index') }}">Knowledge Centre</a></li>
                    </ul>

                    <h4>Support & Legal</h4>
                    <ul>
                        <li><a href="{{ url('contact') }}">Contact & Help Centre</a></li>
                        <li><a href="{{ url('privacy') }}">Privacy Policy</a></li>
                        <li><a href="{{ url('terms') }}">Terms of Use</a></li>
                        <li><a href="{{ url('terms') }}">Mentor Terms & Refund Policy</a></li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="vj-footer-bottom">
                <div>
                    © {{ date('Y') }} <strong>VEDRIX</strong>. All rights reserved. Mentors Shape Possibilities.
                </div>
                <div>
                    <span>hello@vedrix.com</span> • <span>New Delhi, India</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Interactive Components Script (HTML Tabs Switcher, Dashboard Sim, Carousel Helper) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // General Tab Switcher for .vj-tabs-wrapper
            document.querySelectorAll('.vj-tabs-wrapper').forEach(wrapper => {
                const buttons = wrapper.querySelectorAll('.vj-tab-btn');
                const panes = wrapper.querySelectorAll('.vj-tab-pane');

                buttons.forEach(btn => {
                    btn.addEventListener('click', function () {
                        const targetId = this.getAttribute('data-tab-target');
                        buttons.forEach(b => b.classList.remove('active'));
                        panes.forEach(p => p.classList.remove('active'));

                        this.classList.add('active');
                        const targetPane = wrapper.querySelector('#' + targetId);
                        if (targetPane) {
                            targetPane.classList.add('active');
                        }
                    });
                });
            });

            // Interactive Dashboard Tab Switcher in Section 10
            const dashNavButtons = document.querySelectorAll('.vj-dash-nav-item');
            const dashPanes = document.querySelectorAll('.vj-dash-content-pane');
            if (dashNavButtons.length && dashPanes.length) {
                dashNavButtons.forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const target = this.getAttribute('data-dash-target');
                        dashNavButtons.forEach(b => b.classList.remove('active'));
                        dashPanes.forEach(p => {
                            p.classList.remove('active');
                            p.style.display = 'none';
                        });

                        this.classList.add('active');
                        const targetPane = document.getElementById(target);
                        if (targetPane) {
                            targetPane.classList.add('active');
                            targetPane.style.display = 'block';
                        }
                    });
                });
            }

            // Mega Menu Smooth Hover Handler (Flicker-free transition & grace delay)
            const navItems = document.querySelectorAll('.vj-nav-item');
            navItems.forEach(item => {
                const megaMenu = item.querySelector('.vj-mega-menu');
                if (!megaMenu) return;

                let leaveTimer = null;

                const openMenu = () => {
                    if (leaveTimer) clearTimeout(leaveTimer);
                    navItems.forEach(other => {
                        if (other !== item) other.classList.remove('is-active');
                    });
                    item.classList.add('is-active');
                };

                const closeMenu = () => {
                    leaveTimer = setTimeout(() => {
                        item.classList.remove('is-active');
                    }, 220);
                };

                item.addEventListener('mouseenter', openMenu);
                item.addEventListener('mouseleave', closeMenu);
            });

            // Close mega menu if clicked outside
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.vj-nav-item')) {
                    navItems.forEach(item => item.classList.remove('is-active'));
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
