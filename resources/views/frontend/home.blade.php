@extends('frontend.layouts.frontend')

@section('title', 'VEDRIX — Mentors Shape Possibilities | Career Development & Mentorship Platform')
@section('meta_description', 'Vedrix connects students and early professionals with experienced mentors, structured career journeys, practical career-readiness tools and measurable progress.')

@section('content')

<!-- =========================================================================
     SECTION 01 — HERO (Dark: Deep Navy #1A2340 / #12192C)
     Brand Thought: Mentors Shape Possibilities.
     Core Positioning: Mentorship that turns ambition into direction.
     ========================================================================= -->
<section class="vj-hero-slider-section" id="hero">
    <div class="vj-hero-slider-banner" id="heroBannerSlider">
        
        <div class="vj-hero-bg-layer active" id="hero-bg-0" style="background-image: url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=2000&q=80');"></div>
        <div class="vj-hero-bg-layer" id="hero-bg-1" style="background-image: url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=2000&q=80');"></div>
        <div class="vj-hero-bg-layer" id="hero-bg-2" style="background-image: url('https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=2000&q=80');"></div>
        <div class="vj-hero-bg-layer" id="hero-bg-3" style="background-image: url('https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=2000&q=80');"></div>

        <div class="vj-hero-overlay-layer"></div>

        <!-- Foreground Content (Contained inside a spacious responsive container) -->
        <div class="container" style="position: relative; z-index: 4; width: 100%;padding: 70px 0;">
            <div class="row align-items-center g-4 g-lg-5">
                <!-- Left Column: Vertical Interactive Tab Cards (Matching Reference Image) -->
                <div class="col-lg-3">
                    <div class="vj-hero-tabs-stack">
                        <!-- Tab Card 0 -->
                        <div class="vj-hero-tab-card active" data-hero-slide="0">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-search"></i>
                                <div>
                                    <div class="vj-hero-tab-title">Find Your Mentor</div>
                                    <div class="vj-hero-tab-sub">1:1 Industry Guidance</div>
                                </div>
                            </div>
                            <div class="vj-hero-tab-progress"></div>
                        </div>
                        <!-- Tab Card 1 -->
                        <div class="vj-hero-tab-card" data-hero-slide="1">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-people"></i>
                                <div>
                                    <div class="vj-hero-tab-title">Student & Graduate</div>
                                    <div class="vj-hero-tab-sub">Degree to Employability</div>
                                </div>
                            </div>
                            <div class="vj-hero-tab-progress"></div>
                        </div>
                        <!-- Tab Card 2 -->
                        <div class="vj-hero-tab-card" data-hero-slide="2">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                                <div>
                                    <div class="vj-hero-tab-title">Early Professional</div>
                                    <div class="vj-hero-tab-sub">Role Transitions & Growth</div>
                                </div>
                            </div>
                            <div class="vj-hero-tab-progress"></div>
                        </div>
                        <!-- Tab Card 3 -->
                        <div class="vj-hero-tab-card" data-hero-slide="3">
                            <div class="d-flex align-items-center gap-3">
                                <i class="bi bi-person-plus-fill"></i>
                                <div>
                                    <div class="vj-hero-tab-title">Become a Mentor</div>
                                    <div class="vj-hero-tab-sub">Turn Experience Into Impact</div>
                                </div>
                            </div>
                            <div class="vj-hero-tab-progress"></div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Active Slide Content (Matching Reference Image) -->
                <div class="col-lg-9">
                    <div class="vj-hero-content-slider">
                        <!-- Slide 0: Find Your Mentor -->
                        <div class="vj-hero-slide-pane active" id="hero-pane-0">
                            <div class="vj-hero-ref-badge">
                                <span class="vj-badge-dot"></span> 100% VETTED & VERIFIED MENTORS
                            </div>
                            <h1 class="vj-hero-ref-title">
                                Turn Ambition Into <br> <span>Direction.</span>
                            </h1>
                            <p class="vj-hero-ref-desc">
                                There is no shortage of information about careers. What is often missing is someone who can help you understand what matters, make better choices and keep moving forward.
                            </p>
                            <hr>
                            <div class="vj-hero-value-strip">
                                <div class="vj-hero-value-eyebrow">GET STARTED WITH PROVEN MENTORSHIP</div>
                                <div class="vj-hero-value-highlight">
                                    1:1 Focus Sessions <span class="vj-value-small">| Video Call session</span>
                                </div>
                                <div class="vj-hero-value-sub">
                                    Real Mentors | Structured Journeys | Practical Action | Measurable Progress
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary" style="font-weight: 800; font-size: 15px;">
                                    <i class="bi bi-search"></i> FIND YOUR MENTOR
                                </a>
                                <a href="#paths" class="vj-btn vj-btn-white">
                                    <i class="bi bi-signpost-2"></i> Start Your Journey
                                </a>
                                <a href="{{ route('register') }}?role=mentor" class="vj-btn-text-mustard ms-lg-2">
                                    Become a Mentor <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Slide 1: Student & Graduate -->
                        <div class="vj-hero-slide-pane" id="hero-pane-1">
                            <div class="vj-hero-ref-badge">
                                <span class="vj-badge-dot"></span> CAREER READINESS FRAMEWORK
                            </div>
                            <h1 class="vj-hero-ref-title">
                                Move From Qualification To <br> <span>Career Readiness.</span>
                            </h1>
                            <p class="vj-hero-ref-desc">
                                Prepare your resume, LinkedIn profile, case interviews and portfolio projects with hands-on guidance from leaders who understand the real world of work.
                            </p>
                            <hr>
                            <div class="vj-hero-value-strip">
                                <div class="vj-hero-value-eyebrow">COMPREHENSIVE EMPLOYABILITY SUITE</div>
                                <div class="vj-hero-value-highlight">
                                    Interview Gym & Studios <span class="vj-value-small">| Hands-on Labs</span>
                                </div>
                                <div class="vj-hero-value-sub">
                                    Mock Simulations | ATS Resume Audits | Project Vault Proof
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <a href="{{ route('mentors.search') }}?goal=first_job" class="vj-btn vj-btn-primary" style="font-weight: 800; font-size: 15px;">
                                    <i class="bi bi-briefcase-fill"></i> BECOME CAREER READY
                                </a>
                                <a href="#assessments" class="vj-btn vj-btn-white">
                                    <i class="bi bi-clipboard2-check"></i> Take Assessment
                                </a>
                                <a href="{{ route('mentors.search') }}?goal=student" class="vj-btn-text-mustard ms-lg-2">
                                    Explore Student Paths <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Slide 2: Early Professional -->
                        <div class="vj-hero-slide-pane" id="hero-pane-2">
                            <div class="vj-hero-ref-badge">
                                <span class="vj-badge-dot"></span> STRATEGIC CAREER ACCELERATION
                            </div>
                            <h1 class="vj-hero-ref-title">
                                Don't Let Your Career <br> <span>Happen By Accident.</span>
                            </h1>
                            <p class="vj-hero-ref-desc">
                                Navigate role choices, skill gaps, workplace politics, career transitions and compensation reviews with mentors from tier-1 global companies.
                            </p>
                            <hr>
                            <div class="vj-hero-value-strip">
                                <div class="vj-hero-value-eyebrow">PROMOTION & TRANSITION ROADMAPS</div>
                                <div class="vj-hero-value-highlight">
                                    Staff+ & Leadership Tracks <span class="vj-value-small">| 1:1 Advisory</span>
                                </div>
                                <div class="vj-hero-value-sub">
                                    Executive Coaching | Architecture Sprints | Goal Accountability
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <a href="{{ route('mentors.search') }}?goal=professional" class="vj-btn vj-btn-primary" style="font-weight: 800; font-size: 15px;">
                                    <i class="bi bi-graph-up-arrow"></i> ACCELERATE YOUR CAREER
                                </a>
                                <a href="#journeys" class="vj-btn vj-btn-white">
                                    <i class="bi bi-compass"></i> View Journeys
                                </a>
                                <a href="{{ route('mentors.search') }}" class="vj-btn-text-mustard ms-lg-2">
                                    Browse Top Mentors <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <!-- Slide 3: Become a Mentor -->
                        <div class="vj-hero-slide-pane" id="hero-pane-3">
                            <div class="vj-hero-ref-badge">
                                <span class="vj-badge-dot vj-badge-dot-mustard"></span> SHAPE WHAT COMES NEXT
                            </div>
                            <h1 class="vj-hero-ref-title">
                                Your Experience Could Be <br> <span>Someone's Turning Point.</span>
                            </h1>
                            <p class="vj-hero-ref-desc">
                                Behind every experienced professional are decisions, failures, lessons and insights no textbook can capture. Pass that experience forward through structured journeys.
                            </p>
                            <hr>
                            <div class="vj-hero-value-strip">
                                <div class="vj-hero-value-eyebrow">FLEXIBLE & HIGH-IMPACT MENTORING</div>
                                <div class="vj-hero-value-highlight">
                                    Pass Experience Forward <span class="vj-value-small">| On Your Schedule</span>
                                </div>
                                <div class="vj-hero-value-sub">
                                    Dedicated Mentor Dashboard | Verified Recognition | Meaningful Impact
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <a href="{{ route('register') }}?role=mentor" class="vj-btn vj-btn-primary" style="font-weight: 800; font-size: 15px;">
                                    <i class="bi bi-person-plus-fill"></i> BECOME A MENTOR
                                </a>
                                <a href="{{ url('/about') }}#how-it-works" class="vj-btn vj-btn-white">
                                    <i class="bi bi-info-circle"></i> How Mentoring Works
                                </a>
                                <a href="#for-mentors" class="vj-btn-text-mustard ms-lg-2">
                                    Mentor FAQs <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =========================================================================
     SECTION 02 — CHOOSE YOUR VEDRIX PATH (Light: #F8FAFC)
     Eyebrow: START WHERE YOU ARE
     Title: Different Ambitions. Different Journeys. One Platform.
     Image Cards for all 4 paths
     ========================================================================= -->
<section class="vj-section-light" id="paths">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-4">
            <span class="vj-eyebrow vj-eyebrow-mustard">START WHERE YOU ARE</span>
            <h2 class="vj-font-heading display-6 mb-3 text-dark">
                Different Ambitions. Different Journeys. One Platform.
            </h2>
            <div class="lain center"></div>
            <p class="lead" style="color: #334155;">
                Vedrix adapts around who you are today and where you want to go next.
            </p>
        </div>

        <div class="row g-4">
            <!-- Path 1: Student -->
            <div class="col-lg-3 col-md-6">
                <div class="vj-image-card">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=600&q=80" alt="Students Path" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <span class="badge bg-primary text-dark position-absolute">01 / Student</span>
                    </div>
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading mb-2 text-dark">Discover before you decide.</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Understand career possibilities, identify your strengths, develop relevant skills and make better education and career decisions.
                            </p>
                        </div>
                        <div class="pt-3 border-image-top">
                            <a href="{{ route('mentors.search') }}?goal=student" class="vj-btn-text-mustard">
                                Explore for Students <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Path 2: Graduate / First Job -->
            <div class="col-lg-3 col-md-6">
                <div class="vj-image-card">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=600&q=80" alt="Graduates Path" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <span class="badge bg-primary text-white position-absolute">02 / Graduate / First Job</span>
                    </div>
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading mb-2 text-dark">Move from qualification to career readiness.</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Prepare your resume, LinkedIn profile, interviews, projects and professional skills with guidance from people who understand the real world of work.
                            </p>
                        </div>
                        <div class="pt-3 border-image-top">
                            <a href="{{ route('mentors.search') }}?goal=first_job" class="vj-btn-text-mustard">
                                Become Career Ready <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Path 3: Early Professional -->
            <div class="col-lg-3 col-md-6">
                <div class="vj-image-card">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=600&q=80" alt="Early Professional Path" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <span class="badge bg-primary text-white position-absolute">03 / Early Professional</span>
                    </div>
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading mb-2 text-dark">Don't let your career happen by accident.</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Navigate role choices, skill gaps, workplace challenges, career transitions and growth decisions with experienced mentors.
                            </p>
                        </div>
                        <div class="pt-3 border-image-top">
                            <a href="{{ route('mentors.search') }}?goal=professional" class="vj-btn-text-mustard">
                                Accelerate Your Career <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Path 4: Mentor -->
            <div class="col-lg-3 col-md-6">
                <div class="vj-image-card">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=600&q=80" alt="Mentor Path" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <span class="badge bg-primary text-warning position-absolute">04 / Mentor</span>
                    </div>
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading mb-2 text-dark">Turn experience into impact.</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Help the next generation make stronger decisions while building your own professional mentoring presence and personal brand.
                            </p>
                        </div>
                        <div class="pt-3 border-image-top">
                            <a href="{{ route('register') }}?role=mentor" class="vj-btn-text-mustard">
                                Become a Mentor <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =========================================================================
     SECTION 03 — WHY VEDRIX EXISTS (Dark: #1A2340)
     Eyebrow: THE DIRECTION GAP
     Title: Talent Is Everywhere. Direction Isn't.
     Includes comparison Image Box & 4 Value Cards
     ========================================================================= -->
<section class="vj-section-dark" id="why-vedrix">
    <div class="container">
        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <span class="vj-eyebrow vj-eyebrow-mustard">THE DIRECTION GAP</span>
                <h2 class="vj-font-heading display-6 mb-4 text-white">
                    Talent Is Everywhere.<br><span style="color: var(--vj-primary);">Direction Isn't.</span>
                </h2>
                <p class="lead text-light mb-3">
                    Students today have access to more information than any generation before them.
                </p>
                <div class="lain"></div>
                <p class="text-light mb-3">
                    Yet important career decisions are often still made through:
                </p>
                <ul class="list-unstyled text-light mb-4">
                    <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Fragmented internet advice</li>
                    <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Social pressure and peer comparison</li>
                    <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Incomplete and outdated information</li>
                    <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Costly trial and error</li>
                    <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Generic counselling without real domain context</li>
                    <li class="mb-2"><i class="bi bi-x-circle text-danger me-2"></i> Conversations with people who have never worked in the actual field</li>
                </ul>
            </div>

            <!-- Image Box & Ecosystem Perspective -->
            <div class="col-lg-6">
                <div class="position-relative mb-4">
                    <div class="vj-img-box" style="height: 320px;">
                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80" alt="The Direction Gap" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <div class="vj-img-box-overlay">
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">The Vedrix Ecosystem</span>
                            <h4 class="text-white font-weight-bold mb-1">Guidance That Becomes A Journey</h4>
                            <p class="text-light small mb-0">Discover → Decide → Connect → Learn → Practice → Progress</p>
                        </div>
                    </div>
                </div>

                <div class="p-4" style="background: #12192C; border: 1px solid rgba(255,165,2,0.3); border-radius: 12px !important;">
                    <p class="text-white mb-2" style="font-size: 15.5px; line-height: 1.6;">
                        <strong>The problem isn't always lack of ambition.</strong> It is lack of context, direction and sustained guidance.
                    </p>
                    <p class="text-light small mb-0">
                        Vedrix brings together mentorship, assessment, career planning, learning, practice and accountability so that guidance becomes a journey rather than a one-time conversation.
                    </p>
                </div>
            </div>
        </div>

        <!-- Four Value Cards -->
        <div class="row g-4">
            <div class="col-lg-3 col-sm-6">
                <div class="vj-value-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-eye"></i>
                        <div>
                            <div class="vj-value-num">01 / CLARITY</div>
                            <h4 class="vj-font-heading text-white mb-2">Clarity</h4>
                        </div>
                    </div>
                    <p class="text-light mb-0" style="font-size: 14px;">
                        Understand where you are and where you could go with clear, objective self-awareness and diagnostics.
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="vj-value-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-compass"></i>
                        <div>
                            <div class="vj-value-num">02 / DIRECTION</div>
                            <h4 class="vj-font-heading text-white mb-2">Direction</h4>
                        </div>
                    </div>
                    <p class="text-light mb-0" style="font-size: 14px;">
                        Convert ambition into an actionable, milestone-based career path designed with practicing mentors.
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="vj-value-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-lightning-charge"></i>
                        <div>
                            <div class="vj-value-num">03 / CAPABILITY</div>
                            <h4 class="vj-font-heading text-white mb-2">Capability</h4>
                        </div>
                    </div>
                    <p class="text-light mb-0" style="font-size: 14px;">
                        Build the skills, project artifacts, and interview readiness required to move forward in top roles.
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="vj-value-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <div class="vj-value-num">04 / CONFIDENCE</div>
                            <h4 class="vj-font-heading text-white mb-2">Confidence</h4>
                        </div>
                    </div>
                    <p class="text-light mb-0" style="font-size: 14px;">
                        Make important career decisions with stronger context, verified feedback and proven mentor backing.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
.gallery{display:grid;grid-template-columns:1.1fr .9fr .9fr;gap:16px;}
.gallery-card{min-height:290px;border-radius:12px;background-size: cover !important;overflow:hidden;position:relative;padding:24px;display:flex;align-items:flex-end;color:#fff;background-position: center !important;}
.gallery-card.tall{min-height:516px;grid-row:span 2;}
.gallery-card h3{font-size:28px;line-height:1.1;letter-spacing:-.04em;position:relative;z-index:1; color: #fff !important}
.gallery-card p{color:#fff !important;font-size:14px;margin-top:8px;position:relative;z-index:1;margin-bottom: 0;}
@media(max-width:1100px){
  .gallery{grid-template-columns:1fr 1fr}
  .gallery-card.tall{grid-row:span 1;min-height:250px}
}
@media(max-width:720px){
   .gallery{grid-template-columns:1fr}
}
</style>
<section class="vj-impact-section vj-section-white position-relative" id="how-it-works">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center max-w-750 mx-auto mb-4">
            <span class="vj-eyebrow">For students</span>
            <h2 class="vj-font-heading display-6 mb-2 text-dark">Everything you wish someone had told you in school.</h2>
            <div class="lain center"></div>
            <p class="lead mt-3 mb-0" style="color: #475569; font-size: 1.1rem; line-height: 1.6;">One focused journey. No more 47 Chrome tabs and zero direction.</p>
        </div>

        <div class="gallery">
          <article class="gallery-card tall" style="background:linear-gradient(#00000060), url(https://startupgardner.com/img/hr-as-a-service.webp);">
            <div>
                <h3>AI Career Counsellor</h3>
                <p>Powered by Claude — get a stream-aware second opinion 24/7 between mentor sessions.</p>
            </div>
          </article>
          <article class="gallery-card green" style="background:linear-gradient(#00000060), url(https://startupgardner.com/img/sales.webp);">
            <div>
                <h3>Resume Builder + ATS scoring</h3>
                <p>Drop your resume, get JD-tailored rewrites and an ATS pass-rate score in 30 seconds.</p>
            </div>
          </article>
          <article class="gallery-card brown" style="background:linear-gradient(#00000060), url(https://startupgardner.com/img/it-infra.webp);">
            <div>
                <h3>AI Mock Interviews</h3>
                <p>Voice-based, role-specific, with scored feedback on content, tone and structure.</p>
            </div>
          </article>
          <article class="gallery-card" style="background:linear-gradient(#00000060), url(https://startupgardner.com/img/property.webp);">
            <div>
                <h3>Assessments & Streams</h3>
                <p>Aptitude, interest, values — combined into a personal Career-Fit Heatmap across 12 streams.</p>
            </div>
          </article>
          <article class="gallery-card brown" style="background:linear-gradient(#00000060), url(https://startupgardner.com/img/manufacturing.webp);">
            <div>
                <h3>Peer community by stream</h3>
                <p>Discuss, study together, share offers — a focused, moderated cohort, not a noisy Discord.</p>
            </div>
          </article>
        </div>

        
    </div>
</section>

<section class="vj-framework-section" id="framework">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center max-w-850 mx-auto mb-4">
            <div class="vj-framework-pill">
                <span class="vj-badge-dot"></span> THE CORE FRAMEWORK
            </div>
            <h2 class="vj-font-heading display-6 mb-3 text-white">
                VEDRIX converts uncertainty into a structured growth path.
            </h2>
            <div class="lain center"></div>
            <p class="lead text-light mb-0" style="font-size: 1.1rem; line-height: 1.6;">
                Inspired by enterprise solution tabs, this section works like an interactive decision framework where each stage opens a focused explanation and action.
            </p>
        </div>

        <!-- 6 Tabs Left + Content Card Right -->
        <div class="row g-4 align-items-stretch">
            <!-- Left: 6 Vertical Framework Tabs -->
            <div class="col-md-3">
                <div class="vj-framework-nav d-flex flex-column">
                    <!-- Tab 1: Discover -->
                    <button type="button" class="vj-framework-tab active" data-framework-target="fw-pane-discover">
                        <div class="vj-framework-tab-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <div>
                            <div class="vj-framework-tab-title">Discover</div>
                            <div class="vj-framework-tab-sub">Find opportunities & fit</div>
                        </div>
                    </button>

                    <!-- Tab 2: Decide -->
                    <button type="button" class="vj-framework-tab" data-framework-target="fw-pane-decide">
                        <div class="vj-framework-tab-icon">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <div>
                            <div class="vj-framework-tab-title">Decide</div>
                            <div class="vj-framework-tab-sub">Validate direction & track</div>
                        </div>
                    </button>

                    <!-- Tab 3: Connect -->
                    <button type="button" class="vj-framework-tab" data-framework-target="fw-pane-connect">
                        <div class="vj-framework-tab-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <div class="vj-framework-tab-title">Connect</div>
                            <div class="vj-framework-tab-sub">Find your verified mentor</div>
                        </div>
                    </button>

                    <!-- Tab 4: Learn -->
                    <button type="button" class="vj-framework-tab" data-framework-target="fw-pane-learn">
                        <div class="vj-framework-tab-icon">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div>
                            <div class="vj-framework-tab-title">Learn</div>
                            <div class="vj-framework-tab-sub">Practical role blueprints</div>
                        </div>
                    </button>

                    <!-- Tab 5: Practice -->
                    <button type="button" class="vj-framework-tab" data-framework-target="fw-pane-practice">
                        <div class="vj-framework-tab-icon">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                        </div>
                        <div>
                            <div class="vj-framework-tab-title">Practice</div>
                            <div class="vj-framework-tab-sub">Execute & simulate live</div>
                        </div>
                    </button>

                    <!-- Tab 6: Progress -->
                    <button type="button" class="vj-framework-tab mb-0" data-framework-target="fw-pane-progress">
                        <div class="vj-framework-tab-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div>
                            <div class="vj-framework-tab-title">Progress</div>
                            <div class="vj-framework-tab-sub">Track verified movement</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Right: Content Showcase Card with Two Columns (Text Left, Image Right) -->
            <div class="col-md-9">
                <div class="vj-framework-card">
                    <!-- Pane 1: Discover -->
                    <div class="vj-framework-pane active" id="fw-pane-discover">
                        <div class="row g-0 h-100">
                            <div class="col-md-6 p-4 p-xl-5 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="vj-font-heading text-dark mb-2" style="font-size: 1.85rem; font-weight: 800;">
                                        Discover Opportunities
                                    </h3>
                                    <div class="lain"></div>
                                    <p style="color: #334155; font-size: 14.5px; line-height: 1.6;" class="mb-4">
                                        For people who want to start or accelerate their career but are not sure what to pursue. We identify practical opportunities using market demand trends, capability analysis and diagnostic benchmarking.
                                    </p>
                                    <ul class="vj-framework-checklist">
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Career opportunity discovery & diagnostic mapping</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Industry trend, hiring velocity & role benchmarking</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Capability-based track & opportunity selection</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="pt-3">
                                    <a href="#assessments" class="vj-btn vj-btn-primary px-4 py-2" style="font-size: 14px;">
                                        Start Diagnostic Assessment <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 p-0">
                                <div class="vj-framework-img-wrap">
                                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=900&q=80" alt="Discover Opportunities" class="vj-framework-img" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 2: Decide -->
                    <div class="vj-framework-pane" id="fw-pane-decide">
                        <div class="row g-0 h-100">
                            <div class="col-md-6 p-4 p-xl-5 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="vj-font-heading text-dark mb-2" style="font-size: 1.85rem; font-weight: 800;">
                                        Decide With Certainty
                                    </h3>
                                    <div class="lain"></div>
                                    <p style="color: #334155; font-size: 14.5px; line-height: 1.6;" class="mb-4">
                                        Stop wasting years guessing between conflicting advice. Evaluate realistic career tracks, prerequisite ladders, compensation projections and required commitments before taking the leap.
                                    </p>
                                    <ul class="vj-framework-checklist">
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Parallel role comparison & career trajectory matrix</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Prerequisite skill mapping & realistic timelines</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Step-by-step career roadmap tailored to your background</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="pt-3">
                                    <a href="#paths" class="vj-btn vj-btn-primary px-4 py-2" style="font-size: 14px;">
                                        Explore Career Paths <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 p-0">
                                <div class="vj-framework-img-wrap">
                                    <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=900&q=80" alt="Decide With Certainty" class="vj-framework-img" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 3: Connect -->
                    <div class="vj-framework-pane" id="fw-pane-connect">
                        <div class="row g-0 h-100">
                            <div class="col-md-6 p-4 p-xl-5 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="vj-font-heading text-dark mb-2" style="font-size: 1.85rem; font-weight: 800;">
                                        Connect With Mentors
                                    </h3>
                                    <div class="lain"></div>
                                    <p style="color: #334155; font-size: 14.5px; line-height: 1.6;" class="mb-4">
                                        Guidance changes when it comes from people who actually live the reality of your target role. Match directly with vetted mentors from tier-1 firms for 1:1 sessions, office hours and sprints.
                                    </p>
                                    <ul class="vj-framework-checklist">
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>100% verified mentors across tech, product & strategy</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Flexible session formats: 1:1 video, office hours & sprints</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Honest, unvarnished feedback on your portfolio & profile</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="pt-3">
                                    <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary px-4 py-2" style="font-size: 14px;">
                                        Find Your Mentor <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 p-0">
                                <div class="vj-framework-img-wrap">
                                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=900&q=80" alt="Connect With Mentors" class="vj-framework-img" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 4: Learn -->
                    <div class="vj-framework-pane" id="fw-pane-learn">
                        <div class="row g-0 h-100">
                            <div class="col-md-6 p-4 p-xl-5 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="vj-font-heading text-dark mb-2" style="font-size: 1.85rem; font-weight: 800;">
                                        Learn Practical Frameworks
                                    </h3>
                                    <div class="lain"></div>
                                    <p style="color: #334155; font-size: 14.5px; line-height: 1.6;" class="mb-4">
                                        Skip abstract academic lectures. Master the actual decision-making frameworks, architecture patterns, communication models and workflows that high-performing modern teams execute.
                                    </p>
                                    <ul class="vj-framework-checklist">
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Curated role playbooks, system designs & teardowns</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Real-world workplace dynamics & unwritten career rules</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Practical assignments reviewed directly by senior leaders</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="pt-3">
                                    <a href="#learning" class="vj-btn vj-btn-primary px-4 py-2" style="font-size: 14px;">
                                        Explore Learning Studio <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 p-0">
                                <div class="vj-framework-img-wrap">
                                    <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80" alt="Learn Practical Frameworks" class="vj-framework-img" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 5: Practice -->
                    <div class="vj-framework-pane" id="fw-pane-practice">
                        <div class="row g-0 h-100">
                            <div class="col-md-6 p-4 p-xl-5 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="vj-font-heading text-dark mb-2" style="font-size: 1.85rem; font-weight: 800;">
                                        Practice Under Simulation
                                    </h3>
                                    <div class="lain"></div>
                                    <p style="color: #334155; font-size: 14.5px; line-height: 1.6;" class="mb-4">
                                        Knowledge breaks down without live rehearsal. Practice under realistic interview conditions in our Interview Gym, build vetted project proof, and audit your resume before applying.
                                    </p>
                                    <ul class="vj-framework-checklist">
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Interactive technical & behavioral Interview Gyms</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>ATS Resume Studio & portfolio audit benchmarks</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Vetted project assignments backed by mentor reviews</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="pt-3">
                                    <a href="#readiness-studio" class="vj-btn vj-btn-primary px-4 py-2" style="font-size: 14px;">
                                        Enter Readiness Studio <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 p-0">
                                <div class="vj-framework-img-wrap">
                                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80" alt="Practice Under Simulation" class="vj-framework-img" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 6: Progress -->
                    <div class="vj-framework-pane" id="fw-pane-progress">
                        <div class="row g-0 h-100">
                            <div class="col-md-6 p-4 p-xl-5 d-flex flex-column justify-content-between">
                                <div>
                                    <h3 class="vj-font-heading text-dark mb-2" style="font-size: 1.85rem; font-weight: 800;">
                                        Track Verified Progress
                                    </h3>
                                    <div class="lain"></div>
                                    <p style="color: #334155; font-size: 14.5px; line-height: 1.6;" class="mb-4">
                                        Because mentorship should create movement. Your Vedrix command centre tracks completed milestones, verified mentor feedback, competency scorecards, and velocity metrics to prove readiness.
                                    </p>
                                    <ul class="vj-framework-checklist">
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Unified learner dashboard with task & milestone tracking</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Verified competency scorecard shareable with employers</span>
                                        </li>
                                        <li>
                                            <i class="bi bi-check-lg"></i>
                                            <span>Tangible movement indicators: from gap to job readiness</span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="pt-3">
                                    <a href="#dashboard" class="vj-btn vj-btn-primary px-4 py-2" style="font-size: 14px;">
                                        View Learner Dashboard <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6 p-0">
                                <div class="vj-framework-img-wrap">
                                    <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=900&q=80" alt="Track Verified Progress" class="vj-framework-img" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =========================================================================
     SECTION 04 — HOW VEDRIX WORKS (Light: #FFFFFF)
     Eyebrow: YOUR VEDRIX JOURNEY
     Title: Six Steps From Uncertainty To Progress.
     Interactive visual roadmap + premium animated step cards with deliverables
     ========================================================================= -->
<section class="vj-section-white position-relative" id="how-it-works">
    <div class="container">
        <!-- Section Header -->
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow">YOUR VEDRIX JOURNEY</span>
            <h2 class="vj-font-heading display-6 mb-2 text-dark">
                Six Steps From Uncertainty To Progress.
            </h2>
            <div class="lain center"></div>
            <p class="lead mt-3 mb-0" style="color: #475569; font-size: 1.1rem; line-height: 1.6;">
                A continuous, guided loop designed to transform high aspirations into tangible, career-defining milestones.
            </p>
        </div>

        <!-- Visual Roadmap Progress Stepper Bar -->
        <div class="vj-steps-timeline-bar d-none d-md-flex">
            <div class="vj-steps-timeline-line"></div>
            <div class="vj-steps-timeline-node active" title="Step 01: Discover">01</div>
            <div class="vj-steps-timeline-node" title="Step 02: Readiness">02</div>
            <div class="vj-steps-timeline-node" title="Step 03: Direction">03</div>
            <div class="vj-steps-timeline-node" title="Step 04: Mentor Match">04</div>
            <div class="vj-steps-timeline-node" title="Step 05: Practice">05</div>
            <div class="vj-steps-timeline-node" title="Step 06: Progress">06</div>
        </div>

        <!-- 6 Premium Step Cards Grid -->
        <div class="row g-4 mb-5">
            <!-- Step 01 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-step-card">
                    <div class="vj-step-img-box">
                        <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=800&q=80" alt="Discover Yourself" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <div class="vj-step-number-badge">
                            <i class="bi bi-compass-fill"></i> STEP 01
                        </div>
                        <div class="vj-step-img-overlay">
                            <span class="vj-step-tag">Phase 01 • Baseline Profile</span>
                        </div>
                    </div>
                    <div class="vj-step-body">
                        <div>
                            <h4 class="vj-step-title">Discover Yourself</h4>
                            <p class="vj-step-desc">
                                Tell Vedrix about your aspirations, education, experience, interests and target career goals to establish your benchmark profile.
                            </p>
                        </div>
                        <div class="vj-step-deliverable">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Output: Comprehensive Baseline Competency Audit</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 02 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-step-card">
                    <div class="vj-step-img-box">
                        <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=800&q=80" alt="Understand Readiness" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <div class="vj-step-number-badge">
                            <i class="bi bi-clipboard2-pulse-fill"></i> STEP 02
                        </div>
                        <div class="vj-step-img-overlay">
                            <span class="vj-step-tag">Phase 02 • Diagnostic Evaluation</span>
                        </div>
                    </div>
                    <div class="vj-step-body">
                        <div>
                            <h4 class="vj-step-title">Understand Your Readiness</h4>
                            <p class="vj-step-desc">
                                Use structured assessments and diagnostics to uncover strengths, capability gaps and role-specific areas requiring immediate focus.
                            </p>
                        </div>
                        <div class="vj-step-deliverable">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Output: 5-Dimension Verified Diagnostic Scorecard</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 03 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-step-card">
                    <div class="vj-step-img-box">
                        <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=800&q=80" alt="Define Direction" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <div class="vj-step-number-badge">
                            <i class="bi bi-signpost-2-fill"></i> STEP 03
                        </div>
                        <div class="vj-step-img-overlay">
                            <span class="vj-step-tag">Phase 03 • Strategic Roadmap</span>
                        </div>
                    </div>
                    <div class="vj-step-body">
                        <div>
                            <h4 class="vj-step-title">Define Your Direction</h4>
                            <p class="vj-step-desc">
                                Explore validated industry career tracks and transform broad ambition into a clear, quarter-by-quarter milestone execution roadmap.
                            </p>
                        </div>
                        <div class="vj-step-deliverable">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Output: Tailored Role Roadmap & Skill Ladders</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 04 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-step-card">
                    <div class="vj-step-img-box">
                        <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80" alt="Meet The Right Mentor" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <div class="vj-step-number-badge">
                            <i class="bi bi-people-fill"></i> STEP 04
                        </div>
                        <div class="vj-step-img-overlay">
                            <span class="vj-step-tag">Phase 04 • Practitioner Matching</span>
                        </div>
                    </div>
                    <div class="vj-step-body">
                        <div>
                            <h4 class="vj-step-title">Meet The Right Mentor</h4>
                            <p class="vj-step-desc">
                                Connect or get paired with vetted senior mentors whose actual corporate track record directly aligns with your dream role and company.
                            </p>
                        </div>
                        <div class="vj-step-deliverable">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Output: Direct Access to 1:1 Senior Practitioner</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 05 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-step-card">
                    <div class="vj-step-img-box">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80" alt="Learn Practice Act" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <div class="vj-step-number-badge">
                            <i class="bi bi-lightning-charge-fill"></i> STEP 05
                        </div>
                        <div class="vj-step-img-overlay">
                            <span class="vj-step-tag">Phase 05 • Capability Sprints</span>
                        </div>
                    </div>
                    <div class="vj-step-body">
                        <div>
                            <h4 class="vj-step-title">Learn, Practice & Act</h4>
                            <p class="vj-step-desc">
                                Attend private 1:1 video sessions, execute assignments, rehearse in the Interview Gym, and build verified artifacts in Project Vault.
                            </p>
                        </div>
                        <div class="vj-step-deliverable">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Output: Mock Interview Video & Vetted Project Proof</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 06 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-step-card">
                    <div class="vj-step-img-box">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80" alt="Track Your Progress" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <div class="vj-step-number-badge">
                            <i class="bi bi-graph-up-arrow"></i> STEP 06
                        </div>
                        <div class="vj-step-img-overlay">
                            <span class="vj-step-tag">Phase 06 • Verified Velocity</span>
                        </div>
                    </div>
                    <div class="vj-step-body">
                        <div>
                            <h4 class="vj-step-title">Track Your Progress</h4>
                            <p class="vj-step-desc">
                                Review verified milestone completions, mentor session transcripts, readiness score gains, and your next recommended sprint actions.
                            </p>
                        </div>
                        <div class="vj-step-deliverable">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Output: Shareable Verified Competency Scorecard</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Closing Action Area -->
        <div class="text-center pt-2">
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 mb-4">
                <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary px-4 py-3" style="font-weight: 700;">
                    Start Your Vedrix Journey <i class="bi bi-arrow-right ms-1"></i>
                </a>
                <a href="#assessments" class="btn btn-outline-dark px-4 py-3" style="border-radius: 10px !important; font-weight: 700;">
                    <i class="bi bi-clipboard2-check me-1"></i> Take Diagnostic Assessment
                </a>
            </div>
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-4 text-secondary small" style="color: #475569 !important; font-weight: 600;">
                <span><i class="bi bi-check2-circle text-success me-1"></i> 100% Vetted Practitioners</span>
                <span><i class="bi bi-check2-circle text-success me-1"></i> Structured Sprints</span>
                <span><i class="bi bi-check2-circle text-success me-1"></i> Verifiable Readiness Metrics</span>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================================
     SECTION 05 — KNOW BEFORE YOU CHOOSE (Dark: #12192C)
     Eyebrow: CAREER & READINESS ASSESSMENTS
     Title: Understand Where You Stand Before Deciding Where To Go.
     Scorecard Diagnostic Image Box & Assessment Dimensions
     ========================================================================= -->
<section class="vj-section-dark-alt" id="assessments">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-md-12 text-center">
                <span class="vj-eyebrow vj-eyebrow-mustard">CAREER & READINESS ASSESSMENTS</span>
                <h2 class="vj-font-heading display-6 mb-3 text-white">
                    Understand Where You Stand <br><span style="color: var(--vj-primary);">Before Deciding Where To Go.</span>
                </h2>
                <div class="lain center"></div>
            </div>
            <div class="col-lg-6">
                <p class="text-light lead mb-4" style="font-size: 1.15rem;">
                    Good career decisions begin with better self-awareness. Vedrix assessments help learners understand their current position and identify areas that deserve attention.
                </p>

                <!-- 5 Assessment Dimensions -->
                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="vj-score-item">
                        <div>
                            <strong class="text-white d-block">1. Career Direction</strong>
                            <span class="text-light small">How clear are you about the career you want to pursue?</span>
                        </div>
                        <span class="badge bg-warning text-dark font-weight-bold" style="border-radius: 6px !important;">Diagnostic</span>
                    </div>
                    <div class="vj-score-item">
                        <div>
                            <strong class="text-white d-block">2. Skills Readiness</strong>
                            <span class="text-light small">How closely do your capabilities match your intended career?</span>
                        </div>
                        <span class="badge bg-warning text-dark font-weight-bold" style="border-radius: 6px !important;">Benchmarked</span>
                    </div>
                    <div class="vj-score-item">
                        <div>
                            <strong class="text-white d-block">3. Professional Readiness</strong>
                            <span class="text-light small">Are your resume, LinkedIn profile, communication and presence helping you?</span>
                        </div>
                        <span class="badge bg-warning text-dark font-weight-bold" style="border-radius: 6px !important;">Audit Ready</span>
                    </div>
                    <div class="vj-score-item">
                        <div>
                            <strong class="text-white d-block">4. Interview Readiness</strong>
                            <span class="text-light small">How prepared are you to communicate your knowledge, experience and potential?</span>
                        </div>
                        <span class="badge bg-warning text-dark font-weight-bold" style="border-radius: 6px !important;">Simulation</span>
                    </div>
                    <div class="vj-score-item">
                        <div>
                            <strong class="text-white d-block">5. Goal Readiness</strong>
                            <span class="text-light small">Have you converted ambition into measurable next steps?</span>
                        </div>
                        <span class="badge bg-warning text-dark font-weight-bold" style="border-radius: 6px !important;">Actionable</span>
                    </div>
                </div>

                <a href="{{ route('mentors.search') }}?type=assessment" class="vj-btn vj-btn-primary px-4 py-3">
                    <i class="bi bi-clipboard2-check-fill"></i> Take Your Assessment
                </a>
            </div>

            <!-- Scorecard Image & Metric Breakdown Panel -->
            <div class="col-lg-6">
                <!-- Visual Assessment Image Box -->
                <div class="vj-img-box mb-4" style="height: 220px;">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=800&q=80" alt="Assessment Report" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-img-box-overlay">
                        <span class="badge bg-warning text-dark font-weight-bold mb-1" style="border-radius: 6px !important; width: fit-content;">Real-Time Diagnostic</span>
                        <h5 class="text-white font-weight-bold mb-0">Standardized Career Readiness Framework</h5>
                    </div>
                </div>

                <div class="vj-scorecard-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary">
                        <div>
                            <h4 class="vj-font-heading text-white mb-0">Personalised Scorecard</h4>
                            <span class="text-warning small font-weight-bold">Career Readiness Diagnostic Score</span>
                        </div>
                        <span class="badge bg-warning text-dark font-weight-bold px-3 py-2" style="font-size: 14px; border-radius: 8px !important;">Overall: 82/100</span>
                    </div>

                    <!-- Progress Bars -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-white small mb-1">
                            <span>Strategic Direction</span>
                            <span class="text-warning font-weight-bold">88%</span>
                        </div>
                        <div class="vj-score-bar-bg"><div class="vj-score-bar-fill" style="width: 88%;"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-white small mb-1">
                            <span>Technical & Functional Skills</span>
                            <span class="text-warning font-weight-bold">75%</span>
                        </div>
                        <div class="vj-score-bar-bg"><div class="vj-score-bar-fill" style="width: 75%;"></div></div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-white small mb-1">
                            <span>Professional Branding (Resume/LinkedIn)</span>
                            <span class="text-warning font-weight-bold">90%</span>
                        </div>
                        <div class="vj-score-bar-bg"><div class="vj-score-bar-fill" style="width: 90%;"></div></div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between text-white small mb-1">
                            <span>Interview & Communication Preparedness</span>
                            <span class="text-warning font-weight-bold">74%</span>
                        </div>
                        <div class="vj-score-bar-bg"><div class="vj-score-bar-fill" style="width: 74%;"></div></div>
                    </div>

                    <div class="p-3 bg-dark border border-secondary" style="border-radius: 10px !important;">
                        <h6 class="text-warning mb-2 font-weight-bold">Your Scorecard Delivers:</h6>
                        <ul class="text-light small mb-0 ps-3">
                            <li>Verified strengths and critical development areas</li>
                            <li>Personalised weekly action recommendations</li>
                            <li>Curated learning modules and practice templates</li>
                            <li>Direct matching with domain mentors in your target roles</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =========================================================================
     SECTION 06 — FIND A MENTOR (Light: #F8FAFC)
     Eyebrow: VEDRIX MENTOR NETWORK
     Title: Learn From People Who Have Actually Been There.
     Slider / Carousel + Mentor Profile Image Cards
     ========================================================================= -->
<section class="vj-section-light" id="mentors">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-4">
            <span class="vj-eyebrow vj-eyebrow-mustard">VEDRIX MENTOR NETWORK</span>
            <h2 class="vj-font-heading display-6 mb-3 text-dark">
                Learn From People Who Have Actually Been There.
            </h2>
            <div class="lain center"></div>
            <p class="lead" style="color: #334155;">
                Advice changes when it comes from someone who understands the decisions, challenges and realities of the career you want to build.
            </p>
        </div>

        <!-- Filter Tags -->
        <div class="d-flex justify-content-center mb-4">
            <div class="vj-filter-tags">
                <a href="{{ route('mentors.search') }}" class="vj-filter-tag active"><i class="bi bi-grid-fill"></i> All Mentors</a>
                <a href="{{ route('mentors.search') }}?filter=industry" class="vj-filter-tag">Industry</a>
                <a href="{{ route('mentors.search') }}?filter=career_goal" class="vj-filter-tag">Career Goal</a>
                <a href="{{ route('mentors.search') }}?filter=job_function" class="vj-filter-tag">Job Function</a>
                <a href="{{ route('mentors.search') }}?filter=role" class="vj-filter-tag">Role</a>
                <a href="{{ route('mentors.search') }}?filter=skill" class="vj-filter-tag">Skill</a>
                <a href="{{ route('mentors.search') }}?filter=experience" class="vj-filter-tag">Experience</a>
                <a href="{{ route('mentors.search') }}?filter=format" class="vj-filter-tag">Session Format</a>
            </div>
        </div>

        <!-- Mentor Cards Slider / Grid -->
        <div class="row g-4 mb-5">
            <!-- Mentor 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=700&q=80" alt="Dr. Alisha Verma" style="height: 240px;" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <!-- <span class="badge bg-warning text-dark position-absolute font-weight-bold" style="top: 12px; right: 12px; border-radius: 6px !important;">Verified Mentor</span> -->
                        <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(180deg, transparent 0%, rgba(12, 18, 34, 0.9) 100%);">
                            <h5 class="text-white mb-0 font-weight-bold">Dr. Alisha Verma</h5>
                            <span class="text-warning small font-weight-semibold">Lead Staff Engineer • Ex-Microsoft</span>
                        </div>
                    </div>
                    <div class="vj-image-card-body">
                        <div>
                            <p style="color: #334155; font-size: 14px;" class="mb-3">
                                Specializes in Distributed Systems, Staff+ Engineering transitions, and breaking into tier-1 international tech firms.
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">System Design</span>
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Career Transition</span>
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">1:1 Mentoring</span>
                            </div>
                        </div>
                        <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center">
                            <span class="text-dark font-weight-bold"><i class="bi bi-star-fill text-warning"></i> 4.98 (64 reviews)</span>
                            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary py-2 px-3" style="font-size: 13.5px;">
                                Book Session
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mentor 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80" alt="Arjun Mehta" style="height: 240px;" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <!-- <span class="badge bg-warning text-dark position-absolute font-weight-bold" style="top: 12px; right: 12px; border-radius: 6px !important;">Verified Mentor</span> -->
                        <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(180deg, transparent 0%, rgba(12, 18, 34, 0.9) 100%);">
                            <h5 class="text-white mb-0 font-weight-bold">Arjun Mehta</h5>
                            <span class="text-warning small font-weight-semibold">Director of Product • Ex-Google</span>
                        </div>
                    </div>
                    <div class="vj-image-card-body">
                        <div>
                            <p style="color: #334155; font-size: 14px;" class="mb-3">
                                Product Strategy, Zero-to-One execution, and interview defense for Senior & Principal Product Management roles.
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Product Management</span>
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Executive Hiring</span>
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Office Hours</span>
                            </div>
                        </div>
                        <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center">
                            <span class="text-dark font-weight-bold"><i class="bi bi-star-fill text-warning"></i> 4.97 (48 reviews)</span>
                            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary py-2 px-3" style="font-size: 13.5px;">
                                Book Session
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mentor 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=700&q=80" alt="Neha Sharma" style="height: 240px;" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                        <!-- <span class="badge bg-warning text-dark position-absolute font-weight-bold" style="top: 12px; right: 12px; border-radius: 6px !important;">Verified Mentor</span> -->
                        <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(180deg, transparent 0%, rgba(12, 18, 34, 0.9) 100%);">
                            <h5 class="text-white mb-0 font-weight-bold">Neha Sharma</h5>
                            <span class="text-warning small font-weight-semibold">Engineering Director • Amazon</span>
                        </div>
                    </div>
                    <div class="vj-image-card-body">
                        <div>
                            <p style="color: #334155; font-size: 14px;" class="mb-3">
                                Guides software engineers through managerial tracks, high-scale team architectures, and workplace performance reviews.
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Team Leadership</span>
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Executive Prep</span>
                                <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Goal-Based</span>
                            </div>
                        </div>
                        <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center">
                            <span class="text-dark font-weight-bold"><i class="bi bi-star-fill text-warning"></i> 5.0 (62 reviews)</span>
                            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary py-2 px-3" style="font-size: 13.5px;">
                                Book Session
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary px-4 py-3">
                Browse All Mentors
            </a>
            <a href="{{ url('/contact') }}" class="vj-btn vj-btn-outline-navy px-4 py-3">
                Help Me Find a Mentor <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="vj-section-dark" id="ecosystem">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-4">
            <span class="vj-eyebrow vj-eyebrow-mustard">THE VEDRIX LEARNING ECOSYSTEM</span>
            <h2 class="vj-font-heading display-6 mb-3 text-white">
                Mentorship Gives Direction. <br><span style="color: var(--vj-primary);">Practice Builds Capability.</span>
            </h2>
            <div class="lain center"></div>
            <p class="lead text-light">
                Between mentoring sessions, Vedrix keeps learners moving through an active ecosystem of continuous participation.
            </p>
        </div>

        <!-- HTML Tabs Switcher -->
        <div class="vj-tabs-wrapper">
            <div class="d-flex justify-content-center mb-4">
                <ul class="vj-tabs vj-tabs-dark">
                    <li><button type="button" class="vj-tab-btn active" data-tab-target="eco-tab-learning"><i class="bi bi-journal-code"></i> Learning</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="eco-tab-practice"><i class="bi bi-cpu-fill"></i> Practice</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="eco-tab-community"><i class="bi bi-people-fill"></i> Community</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="eco-tab-knowledge"><i class="bi bi-lightbulb-fill"></i> Knowledge</button></li>
                </ul>
            </div>

            <!-- Tab Panes -->
            <div class="vj-tab-content">
                <!-- Pillar 1: Learning -->
                <div id="eco-tab-learning" class="vj-tab-pane active">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-6">
                            <h3 class="vj-font-heading text-warning mb-3">Structured, Modular Learning</h3>
                            <p class="text-light mb-4" style="font-size: 15.5px; line-height: 1.7;">
                                Curated learning paths aligned directly with modern industry demand. No filler courses—only high-signal modules verified by working practitioners.
                            </p>
                            <ul class="list-unstyled text-white d-flex flex-column gap-2 mb-4">
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Micro-learning:</strong> 15-minute conceptual sprints focused on immediate applications</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Guided learning paths:</strong> Sequential step-by-step role roadmaps</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Specialized courses:</strong> Technical, leadership, and product strategy</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Mentor-recommended material:</strong> Reading lists and code repositories from verified mentors</li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80" alt="Learning Ecosystem" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Curated Modular Track</span>
                                    <h4 class="text-white font-weight-bold mb-1">System Architecture for Early Tech Leads</h4>
                                    <p class="text-light small mb-0">6 Modules • Micro-assessments • Mentor Task Pack included</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pillar 2: Practice -->
                <div id="eco-tab-practice" class="vj-tab-pane">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-6">
                            <h3 class="vj-font-heading text-warning mb-3">Hands-on Execution & Action</h3>
                            <p class="text-light mb-4" style="font-size: 15.5px; line-height: 1.7;">
                                Passive consumption doesn't build careers. Practice drills, weekly accountability, and concrete outputs prove real capability.
                            </p>
                            <ul class="list-unstyled text-white d-flex flex-column gap-2 mb-4">
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Assignments:</strong> Mentor-designed challenges replicating workplace scenarios</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Weekly actions:</strong> Concrete sprint checklists submitted for review</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Project builds:</strong> Production-ready portfolio additions</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Interview practice:</strong> Regular live video and peer simulations</li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80" alt="Practice Lab" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Real-World Simulation</span>
                                    <h4 class="text-white font-weight-bold mb-1">Live Case Defense & Code Critique</h4>
                                    <p class="text-light small mb-0">Submit assignments, receive async video reviews from senior leaders.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pillar 3: Community -->
                <div id="eco-tab-community" class="vj-tab-pane">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-6">
                            <h3 class="vj-font-heading text-warning mb-3">Peer & Mentor Circles</h3>
                            <p class="text-light mb-4" style="font-size: 15.5px; line-height: 1.7;">
                                Learn in high-trust circles alongside ambitious peers, guided by experienced professionals who facilitate open discussion.
                            </p>
                            <ul class="list-unstyled text-white d-flex flex-column gap-2 mb-4">
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Mentor-led conversations:</strong> Candid fireside chats and AMAs</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Peer learning cohorts:</strong> Small groups tackling common growth milestones</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Career circles:</strong> Industry-specific channels for ongoing sharing</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Group challenges:</strong> Quarterly team hackathons and sprints</li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=800&q=80" alt="Peer Community" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Peer Cohort Sessions</span>
                                    <h4 class="text-white font-weight-bold mb-1">Interactive Circle: Tech Lead Transitions</h4>
                                    <p class="text-light small mb-0">Small cohort discussion moderated by 2 principal engineers.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pillar 4: Knowledge -->
                <div id="eco-tab-knowledge" class="vj-tab-pane">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-6">
                            <h3 class="vj-font-heading text-warning mb-3">Rich Media & Thought Leadership</h3>
                            <p class="text-light mb-4" style="font-size: 15.5px; line-height: 1.7;">
                                Continuous updates on employability trends, compensation shifts, interview intelligence, and leadership transitions.
                            </p>
                            <ul class="list-unstyled text-white d-flex flex-column gap-2 mb-4">
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Webinars & Workshops:</strong> Bi-weekly expert masterclasses</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Career guides:</strong> In-depth field manuals for 40+ professional tracks</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Expert conversations:</strong> Deep dives into unwritten career rules</li>
                                <li><i class="bi bi-check-circle-fill text-warning me-2"></i> <strong>Podcasts & Videos:</strong> On-demand mentor stories and strategies</li>
                            </ul>
                        </div>
                        <div class="col-lg-6">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=800&q=80" alt="Masterclass Studio" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Exclusive Whitepaper</span>
                                    <h4 class="text-white font-weight-bold mb-1">The Vedrix Career Intelligence Report</h4>
                                    <p class="text-light small mb-0">48-page manual analyzing shifting hiring demands across Indian tech hubs.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('insights.blogs.index') }}" class="vj-btn vj-btn-primary px-4 py-3">
                Explore the Vedrix Knowledge Ecosystem <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="vj-section-light" id="dashboard">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow vj-eyebrow-mustard">YOUR VEDRIX DASHBOARD</span>
            <h2 class="vj-font-heading display-6 mb-3 text-dark">
                Because Mentorship Should Create Movement.
            </h2>
            <div class="lain center"></div>
            <p class="lead" style="color: #334155;">
                Your Vedrix dashboard brings your entire career development journey together in one intuitive command centre.
            </p>
            <div class="p-3 bg-white border border-secondary-subtle d-inline-block shadow-sm" style="border-radius: 10px !important;">
                <span class="text-dark font-weight-bold">Know what you've completed.</span> &nbsp; | &nbsp; 
                <span class="text-dark font-weight-bold">Know what has improved.</span> &nbsp; | &nbsp; 
                <span class="text-warning font-weight-bold" style="color: #B45309 !important;">Know what you should do next.</span>
            </div>
        </div>

        <!-- Interactive Dashboard Replica -->
        <div class="vj-dash-container">
            <div class="vj-dash-header">
                <div>
                    <div class="vj-dash-title">Learner Progress Command Centre</div>
                    <div class="text-secondary small" style="color: #475569 !important;">Welcome back, Alex • Target Goal: Senior Product Designer</div>
                </div>
                <div class="vj-dash-badge">
                    <i class="bi bi-activity"></i> Active Milestone: Q3 Execution
                </div>
            </div>

            <div class="vj-dash-layout">
                <!-- Dashboard Sidebar Navigation -->
                <div class="vj-dash-sidebar">
                    <button type="button" class="vj-dash-nav-item active" data-dash-target="dash-pane-overview">
                        <i class="bi bi-grid-fill"></i> Overview
                    </button>
                    <button type="button" class="vj-dash-nav-item" data-dash-target="dash-pane-goals">
                        <i class="bi bi-flag-fill"></i> Goals & Milestones
                    </button>
                    <button type="button" class="vj-dash-nav-item" data-dash-target="dash-pane-tasks">
                        <i class="bi bi-check2-circle"></i> Tasks & Actions
                    </button>
                    <button type="button" class="vj-dash-nav-item" data-dash-target="dash-pane-sessions">
                        <i class="bi bi-camera-video-fill"></i> Mentor Sessions
                    </button>
                    <button type="button" class="vj-dash-nav-item" data-dash-target="dash-pane-score">
                        <i class="bi bi-graph-up"></i> Progress Score
                    </button>
                </div>

                <!-- Dashboard Content Area -->
                <div class="vj-dash-content">
                    <!-- Pane 1: Overview -->
                    <div id="dash-pane-overview" class="vj-dash-content-pane active">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="vj-dash-chart-card">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div class="vj-dash-chart-title mb-0">Competency Growth by Week</div>
                                        <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Last 6 Weeks</span>
                                    </div>
                                    <div class="vj-dash-bar-chart">
                                        <div class="vj-dash-bar-col">
                                            <div class="vj-dash-bar vj-dash-bar-light" style="height: 40px;"></div>
                                            <div class="vj-dash-bar-label">W1</div>
                                        </div>
                                        <div class="vj-dash-bar-col">
                                            <div class="vj-dash-bar vj-dash-bar-light" style="height: 65px;"></div>
                                            <div class="vj-dash-bar-label">W2</div>
                                        </div>
                                        <div class="vj-dash-bar-col">
                                            <div class="vj-dash-bar vj-dash-bar-light" style="height: 90px;"></div>
                                            <div class="vj-dash-bar-label">W3</div>
                                        </div>
                                        <div class="vj-dash-bar-col">
                                            <div class="vj-dash-bar vj-dash-bar-light" style="height: 120px;"></div>
                                            <div class="vj-dash-bar-label">W4</div>
                                        </div>
                                        <div class="vj-dash-bar-col">
                                            <div class="vj-dash-bar vj-dash-bar-light" style="height: 145px;"></div>
                                            <div class="vj-dash-bar-label">W5</div>
                                        </div>
                                        <div class="vj-dash-bar-col">
                                            <div class="vj-dash-bar vj-dash-bar-accent" style="height: 170px;"></div>
                                            <div class="vj-dash-bar-label">W6</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="vj-dash-metrics">
                                    <div class="vj-dash-metric-card">
                                        <div class="vj-dash-metric-label">Readiness Score</div>
                                        <div class="vj-dash-metric-value text-warning">84%</div>
                                        <span class="badge bg-success-subtle text-success small font-weight-bold" style="border-radius: 6px !important;">+16% this month</span>
                                    </div>
                                    <div class="vj-dash-metric-card">
                                        <div class="vj-dash-metric-label">Tasks Completed</div>
                                        <div class="vj-dash-metric-value">18/22</div>
                                        <span class="text-secondary small" style="color: #475569 !important;">4 actions pending</span>
                                    </div>
                                    <div class="vj-dash-metric-card">
                                        <div class="vj-dash-metric-label">Mentor Sessions</div>
                                        <div class="vj-dash-metric-value">6 Hours</div>
                                        <span class="text-secondary small" style="color: #475569 !important;">Next: Thursday 4 PM</span>
                                    </div>
                                    <div class="vj-dash-metric-card">
                                        <div class="vj-dash-metric-label">Successful Careers</div>
                                        <div class="vj-dash-metric-value text-warning">90%</div>
                                        <span class="badge bg-success-subtle text-success small font-weight-bold" style="border-radius: 6px !important;">Students and Lerners</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 2: Goals & Milestones -->
                    <div id="dash-pane-goals" class="vj-dash-content-pane">
                        <div class="bg-white p-4 border border-light-subtle rounded" style="border-radius: 12px !important;">
                            <h5 class="vj-font-heading text-dark mb-1">Quarterly Goals & Milestones</h5>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <div>
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <strong>Milestone 1: Complete Portfolio Case Study #1</strong>
                                        <div class="text-secondary small" style="color: #475569 !important;">Reviewed by Arjun Mehta • Exceeded expectations</div>
                                    </div>
                                    <span class="badge bg-success" style="border-radius: 6px !important;">Completed</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <div>
                                        <i class="bi bi-record-circle-fill text-warning me-2"></i>
                                        <strong>Milestone 2: System Architecture Mock Simulation</strong>
                                        <div class="text-secondary small" style="color: #475569 !important;">Due in 4 days • Mentor assigned</div>
                                    </div>
                                    <span class="badge bg-warning text-dark" style="border-radius: 6px !important;">In Progress</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-3">
                                    <div>
                                        <i class="bi bi-circle text-secondary me-2"></i>
                                        <strong>Milestone 3: Behavioural & Executive Interview Defense</strong>
                                        <div class="text-secondary small" style="color: #475569 !important;">Scheduled for Week 8</div>
                                    </div>
                                    <span class="badge bg-light text-dark border" style="border-radius: 6px !important;">Upcoming</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 3: Tasks & Actions -->
                    <div id="dash-pane-tasks" class="vj-dash-content-pane">
                        <div class="bg-white p-4 border border-light-subtle rounded" style="border-radius: 12px !important;">
                            <h5 class="vj-font-heading text-dark mb-3">Assigned Mentor Tasks</h5>
                            <div class="d-flex flex-column gap-2">
                                <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center" style="border-radius: 8px !important;">
                                    <div>
                                        <strong>Review ATS resume keywords for Staff Product Designer</strong>
                                        <div class="text-secondary small" style="color: #475569 !important;">Assigned by Neha Sharma</div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-dark" style="border-radius: 6px !important;">Submit Action</button>
                                </div>
                                <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center" style="border-radius: 8px !important;">
                                    <div>
                                        <strong>Practice 5 executive behavioural prompts in Interview Gym</strong>
                                        <div class="text-secondary small" style="color: #475569 !important;">Assigned by Arjun Mehta</div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-dark" style="border-radius: 6px !important;">Start Practice</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 4: Mentor Sessions -->
                    <div id="dash-pane-sessions" class="vj-dash-content-pane">
                        <div class="bg-white p-4 border border-light-subtle rounded" style="border-radius: 12px !important;">
                            <h5 class="vj-font-heading text-dark mb-3">Past & Upcoming Sessions</h5>
                            <div class="p-3 border border-warning rounded bg-warning-subtle mb-3" style="border-radius: 8px !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-dark">Upcoming 1:1 Video Mentorship</strong>
                                        <div class="text-dark small">Thursday, Sept 10 • 4:00 PM - 5:00 PM IST</div>
                                        <div class="text-secondary small mt-1" style="color: #334155 !important;">Mentor: Dr. Alisha Verma</div>
                                    </div>
                                    <button class="btn btn-dark btn-sm" style="border-radius: 8px !important;">Join Room</button>
                                </div>
                            </div>
                            <div class="p-3 border rounded bg-light" style="border-radius: 8px !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-dark">Completed: Career Strategy & Role Transitions</strong>
                                        <div class="text-secondary small" style="color: #475569 !important;">Aug 28 • Session Notes & Recording Available</div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary" style="border-radius: 6px !important;">View Notes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pane 5: Progress Score -->
                    <div id="dash-pane-score" class="vj-dash-content-pane">
                        <div class="bg-white p-4 border border-light-subtle rounded" style="border-radius: 12px !important;">
                            <h5 class="vj-font-heading text-dark mb-2">Verified Competency Diagnostic</h5>
                            <p class="text-secondary small mb-4" style="color: #475569 !important;">Verified against industry standards for product and technology tracks.</p>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded" style="border-radius: 8px !important;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <strong>Domain Competency</strong>
                                            <span class="text-warning font-weight-bold">92%</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 4px !important;">
                                            <div class="progress-bar bg-warning" style="width: 92%; border-radius: 4px !important;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 border rounded" style="border-radius: 8px !important;">
                                        <div class="d-flex justify-content-between mb-1">
                                            <strong>Interview Preparedness</strong>
                                            <span class="text-warning font-weight-bold">85%</span>
                                        </div>
                                        <div class="progress" style="height: 6px; border-radius: 4px !important;">
                                            <div class="progress-bar bg-warning" style="width: 85%; border-radius: 4px !important;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary px-4 py-3">
                See How Progress Works <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="vj-section-dark" id="insights">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow vj-eyebrow-mustard">IDEAS FOR BETTER CAREER DECISIONS</span>
            <h2 class="vj-font-heading display-6 mb-3 text-white">
                Knowledge That Helps You Move Forward.
            </h2>
            <div class="lain center"></div>
            <p class="lead text-light">
                Careers are changing faster than traditional advice can keep up. Vedrix Insights brings together ideas, research, practical guidance and conversations from people closest to the world of work.
            </p>
        </div>

        <!-- 6 Content Tabs -->
        <div class="vj-tabs-wrapper">
            <div class="d-flex justify-content-center mb-4">
                <ul class="vj-tabs vj-tabs-dark">
                    <li><button type="button" class="vj-tab-btn active" data-tab-target="ins-tab-latest"><i class="bi bi-newspaper"></i> Latest Insights</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="ins-tab-guides"><i class="bi bi-compass"></i> Career Guides</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="ins-tab-research"><i class="bi bi-file-earmark-bar-graph"></i> Research & Reports</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="ins-tab-convos"><i class="bi bi-chat-quote"></i> Mentor Conversations</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="ins-tab-webinars"><i class="bi bi-calendar2-event"></i> Webinars & Events</button></li>
                    <li><button type="button" class="vj-tab-btn" data-tab-target="ins-tab-podcasts"><i class="bi bi-mic"></i> Podcasts & Videos</button></li>
                </ul>
            </div>

            <!-- Tab Contents with Real Imagery -->
            <div class="vj-tab-content">
                <!-- Tab 1: Latest Insights -->
                <div id="ins-tab-latest" class="vj-tab-pane active">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=600&q=80" alt="Mentorship Outperforms Advice" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Career Strategy</span>
                                        <h5 class="vj-font-heading text-white mb-2">Why Mentorship Outperforms Generic Career Advice</h5>
                                        <p class="text-light small mb-3">How tailored contextual guidance helps early professionals navigate ambiguous workplace promotions.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Read Article →</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=600&q=80" alt="Tech Hiring in 2026" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Hiring Trends</span>
                                        <h5 class="vj-font-heading text-white mb-2">The Unwritten Rules of Tech Hiring in 2026</h5>
                                        <p class="text-light small mb-3">What engineering managers actually look for in portfolio projects beyond basic GitHub repos.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Read Article →</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1611944212129-29977ae1398c?auto=format&fit=crop&w=600&q=80" alt="Audit Your LinkedIn" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Personal Brand</span>
                                        <h5 class="vj-font-heading text-white mb-2">Audit Your LinkedIn: 7 High-Impact Adjustments</h5>
                                        <p class="text-light small mb-3">Step-by-step guidance on transforming your profile from a passive resume into an inbound magnet.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Read Article →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Career Guides -->
                <div id="ins-tab-guides" class="vj-tab-pane">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=600&q=80" alt="Product Management Guide" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Field Guide</span>
                                        <h5 class="vj-font-heading text-white mb-2">Breaking into Product Management</h5>
                                        <p class="text-light small mb-3">Comprehensive breakdown of case interviews, execution frameworks, and key reading lists.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Open Guide →</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=600&q=80" alt="First 90 Days" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Field Guide</span>
                                        <h5 class="vj-font-heading text-white mb-2">First 90 Days in a Tech Consulting Role</h5>
                                        <p class="text-light small mb-3">How to manage client expectations, structure analytical findings, and build credibility.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Open Guide →</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=600&q=80" alt="System Design Handbook" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Field Guide</span>
                                        <h5 class="vj-font-heading text-white mb-2">System Design Interview Handbook</h5>
                                        <p class="text-light small mb-3">Core architectures, caching patterns, database sharding, and interview pacing rules.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Open Guide →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Research & Reports -->
                <div id="ins-tab-research" class="vj-tab-pane">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=800&q=80" alt="Research Report" style="height: 220px;" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Research Report</span>
                                        <h4 class="text-white mb-2 font-weight-bold">Employability Outlook 2026: The Skill-Context Gap</h4>
                                        <p class="text-light small mb-3">Analysis of 1,200+ graduate hiring assessments evaluating why standard degrees fall short in modern roles.</p>
                                    </div>
                                    <a href="{{ route('insights.white-papers.index') }}" class="vj-btn-text-mustard">Download Whitepaper →</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1551836022-deb4988cc6c0?auto=format&fit=crop&w=800&q=80" alt="Mentorship Study" style="height: 220px;" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Industry Study</span>
                                        <h4 class="text-white mb-2 font-weight-bold">The Mentorship Multiplier in Early-Career Retention</h4>
                                        <p class="text-light small mb-3">How structured internal and external mentoring accelerates promotion velocity by 2.4x.</p>
                                    </div>
                                    <a href="{{ route('insights.white-papers.index') }}" class="vj-btn-text-mustard">Download Whitepaper →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Mentor Conversations -->
                <div id="ins-tab-convos" class="vj-tab-pane">
                    <div class="row g-4">
                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80" alt="Arjun Mehta" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <h5 class="vj-font-heading text-white mb-1">"Before Becoming a Director"</h5>
                                        <p class="text-warning small font-weight-bold mb-2">Conversation with Arjun Mehta</p>
                                        <p class="text-light small mb-3">Lessons on letting go of individual coding and mastering team enablement.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Read Transcript →</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=600&q=80" alt="Neha Sharma" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <h5 class="vj-font-heading text-white mb-1">"Navigating Imposter Syndrome"</h5>
                                        <p class="text-warning small font-weight-bold mb-2">Conversation with Neha Sharma</p>
                                        <p class="text-light small mb-3">Practical rituals to separate temporary uncertainty from actual competence.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Read Transcript →</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=600&q=80" alt="Dr. Alisha Verma" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <h5 class="vj-font-heading text-white mb-1">"Tier-3 to Microsoft Staff"</h5>
                                        <p class="text-warning small font-weight-bold mb-2">Conversation with Dr. Alisha Verma</p>
                                        <p class="text-light small mb-3">The exact sequence of projects, mentors, and open-source milestones.</p>
                                    </div>
                                    <a href="{{ route('insights.blogs.index') }}" class="vj-btn-text-mustard mt-auto">Read Transcript →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Webinars & Events -->
                <div id="ins-tab-webinars" class="vj-tab-pane">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=800&q=80" alt="Upcoming Webinar" style="height: 220px;" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-warning text-dark font-weight-bold" style="border-radius: 6px !important;">Upcoming Webinar</span>
                                            <span class="text-white small">Sept 18 • 6:00 PM IST</span>
                                        </div>
                                        <h4 class="text-white mb-2 font-weight-bold">Mastering Product Execution: From PRD to Launch</h4>
                                        <p class="text-light small mb-3">Live walkthrough with interactive Q&A featuring senior product leaders.</p>
                                    </div>
                                    <a href="{{ route('insights.webinars.index') }}" class="vj-btn-text-mustard">Reserve Free Spot →</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="vj-image-card vj-image-card-dark">
                                <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80" alt="Recorded Workshop" style="height: 220px;" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-image-card-body">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-light text-dark font-weight-bold" style="border-radius: 6px !important;">Recorded Workshop</span>
                                            <span class="text-white small">65 Minutes</span>
                                        </div>
                                        <h4 class="text-white mb-2 font-weight-bold">Executive Resume Teardown & Live Rebuild</h4>
                                        <p class="text-light small mb-3">Watch 3 real resumes rebuilt live with clear ATS and recruiter impact rules.</p>
                                    </div>
                                    <a href="{{ route('insights.webinars.index') }}" class="vj-btn-text-mustard">Watch Recording →</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 6: Podcasts & Videos -->
                <div id="ins-tab-podcasts" class="vj-tab-pane">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="vj-video-box mb-3">
                                <img src="https://images.unsplash.com/photo-1590602847861-f357a9332bbc?auto=format&fit=crop&w=800&q=80" alt="Podcast Episode" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <a href="{{ route('insights.podcasts.index') }}" class="vj-play-btn">
                                    <i class="bi bi-play-fill"></i>
                                </a>
                            </div>
                            <h5 class="text-white font-weight-bold mb-1">The Vedrix Mentorcast: Episode 24</h5>
                            <p class="text-light small">Turning Career Setbacks Into Tactical Direction with Global Tech Mentors.</p>
                        </div>
                        <div class="col-lg-6">
                            <div class="vj-video-box mb-3">
                                <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=800&q=80" alt="Masterclass Episode" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <a href="{{ route('insights.videos.index') }}" class="vj-play-btn">
                                    <i class="bi bi-play-fill"></i>
                                </a>
                            </div>
                            <h5 class="text-white font-weight-bold mb-1">Career Clinics: Live Mock System Design</h5>
                            <p class="text-light small">Real-time breakdown of architectural trade-offs with immediate mentor feedback.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('insights.blogs.index') }}" class="vj-btn vj-btn-primary px-4 py-3">
                Explore Vedrix Insights <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="vj-section-white" id="stories">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow vj-eyebrow-mustard">VEDRIX STORIES</span>
            <h2 class="vj-font-heading display-6 mb-3 text-dark">
                Every Career Journey Starts Somewhere.
            </h2>
            <div class="lain center"></div>
            <p class="lead" style="color: #334155;">
                The most powerful proof of mentorship is not the number of conversations held. It is what happens after them.
            </p>
        </div>

        <!-- Bootstrap Interactive Slider Carousel -->
        <div id="storiesCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
            <!-- Carousel Indicators -->
            <div class="carousel-indicators" style="bottom: -40px;">
                <button type="button" data-bs-target="#storiesCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                <button type="button" data-bs-target="#storiesCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#storiesCarousel" data-bs-slide-to="2"></button>
                <button type="button" data-bs-target="#storiesCarousel" data-bs-slide-to="3"></button>
            </div>

            <!-- Carousel Slides -->
            <div class="carousel-inner pb-2">
                <!-- Slide 1: Confusion to Direction -->
                <div class="carousel-item active">
                    <div class="row align-items-center g-5 p-4 bg-light rounded" style="border-radius: 14px !important; border: 1px solid #E2E8F0;">
                        <div class="col-lg-5">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=700&q=80" alt="Kavya Iyer" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-1" style="border-radius: 6px !important; width: fit-content;">Learner Story</span>
                                    <h5 class="text-white font-weight-bold mb-0">Kavya Iyer</h5>
                                    <span class="text-light small">Final Year B.Tech → Product Analyst at FinTech Unicorn</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <span class="badge bg-primary text-dark font-weight-bold mb-3 py-2 px-3" style="border-radius: 6px !important;">Theme: Direction</span>
                            <h3 class="vj-font-heading text-dark mb-3">From Confusion To Direction</h3>
                            <p class="lead" style="color: #334155; font-size: 1.1rem; line-height: 1.7;">
                                "I had 5 different career interests and zero clarity on what to prioritize. Over 6 structured weeks, my mentor broke down my strengths, eliminated noise, and helped me build one standout portfolio project that landed my first role."
                            </p>
                            <div class="vj-prog-indicator mt-4">
                                <i class="bi bi-arrow-up-right-circle-fill text-success fs-4"></i>
                                <div>
                                    <strong class="d-block text-dark">Outcome Milestone:</strong>
                                    <span class="text-secondary" style="color: #475569 !important;">Target role secured within 7 weeks of journey completion</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Degree to Employability -->
                <div class="carousel-item">
                    <div class="row align-items-center g-5 p-4 bg-light rounded" style="border-radius: 14px !important; border: 1px solid #E2E8F0;">
                        <div class="col-lg-5">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=700&q=80" alt="Rahul Nair" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-1" style="border-radius: 6px !important; width: fit-content;">Learner Story</span>
                                    <h5 class="text-white font-weight-bold mb-0">Rahul Nair</h5>
                                    <span class="text-light small">Recent Graduate → Associate Consultant</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <span class="badge bg-primary text-white font-weight-bold mb-3 py-2 px-3" style="border-radius: 6px !important;">Theme: Employability</span>
                            <h3 class="vj-font-heading text-dark mb-3">From Degree To Employability</h3>
                            <p class="lead" style="color: #334155; font-size: 1.1rem; line-height: 1.7;">
                                "Academics gave me theory, but I froze in case interviews. The Interview Gym and 4 mock reviews with an ex-McKinsey mentor completely changed how I communicate business problems under pressure."
                            </p>
                            <div class="vj-prog-indicator mt-4">
                                <i class="bi bi-arrow-up-right-circle-fill text-success fs-4"></i>
                                <div>
                                    <strong class="d-block text-dark">Outcome Milestone:</strong>
                                    <span class="text-secondary" style="color: #475569 !important;">Case interview readiness score improved from 58% to 91%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Stagnation to Movement -->
                <div class="carousel-item">
                    <div class="row align-items-center g-5 p-4 bg-light rounded" style="border-radius: 14px !important; border: 1px solid #E2E8F0;">
                        <div class="col-lg-5">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=700&q=80" alt="Ananya Deshmukh" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-1" style="border-radius: 6px !important; width: fit-content;">Learner Story</span>
                                    <h5 class="text-white font-weight-bold mb-0">Ananya Deshmukh</h5>
                                    <span class="text-light small">Software Engineer → Senior Backend Lead</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <span class="badge bg-primary text-white font-weight-bold mb-3 py-2 px-3" style="border-radius: 6px !important;">Theme: Growth</span>
                            <h3 class="vj-font-heading text-dark mb-3">From Stagnation To Movement</h3>
                            <p class="lead" style="color: #334155; font-size: 1.1rem; line-height: 1.7;">
                                "Stuck at the same level for 3 years without understanding what leadership expected. My Vedrix mentor walked me through internal stakeholder alignment and architectural design leadership."
                            </p>
                            <div class="vj-prog-indicator mt-4">
                                <i class="bi bi-arrow-up-right-circle-fill text-success fs-4"></i>
                                <div>
                                    <strong class="d-block text-dark">Outcome Milestone:</strong>
                                    <span class="text-secondary" style="color: #475569 !important;">Promoted to Tech Lead within two review cycles</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Slide 4: Experience to Impact -->
                <div class="carousel-item">
                    <div class="row align-items-center g-5 p-4 bg-light rounded" style="border-radius: 14px !important; border: 1px solid #E2E8F0;">
                        <div class="col-lg-5">
                            <div class="vj-img-box" style="height: 320px;">
                                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=700&q=80" alt="Suresh Ramanathan" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                                <div class="vj-img-box-overlay">
                                    <span class="badge bg-warning text-dark font-weight-bold mb-1" style="border-radius: 6px !important; width: fit-content;">Mentor Story</span>
                                    <h5 class="text-white font-weight-bold mb-0">Suresh Ramanathan</h5>
                                    <span class="text-light small">VP of Engineering • Vedrix Verified Mentor</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <span class="badge bg-primary text-warning font-weight-bold mb-3 py-2 px-3" style="border-radius: 6px !important;">Theme: Mentorship</span>
                            <h3 class="vj-font-heading text-dark mb-3">From Experience To Impact</h3>
                            <p class="lead" style="color: #334155; font-size: 1.1rem; line-height: 1.7;">
                                "Mentoring on Vedrix isn't unstructured chatting. The journey frameworks, milestone scorecards, and task assignments allow me to provide real, lasting direction without spending hours managing logistics."
                            </p>
                            <div class="vj-prog-indicator mt-4">
                                <i class="bi bi-award-fill text-warning fs-4"></i>
                                <div>
                                    <strong class="d-block text-dark">Mentor Perspective:</strong>
                                    <span class="text-secondary" style="color: #475569 !important;">Guided 14 learners through structured career transitions</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#storiesCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#storiesCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>

        <div class="text-center pt-3">
            <a href="{{ route('insights.testimonials.index') }}" class="vj-btn vj-btn-primary px-4 py-3">
                Explore All Vedrix Stories <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="vj-section-dark" id="journeys">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow vj-eyebrow-mustard">STRUCTURED MENTORSHIP JOURNEYS</span>
            <h2 class="vj-font-heading display-6 mb-3 text-white">
                Not Just Conversations. <br><span style="color: var(--vj-primary);">Journeys Built Around Outcomes.</span>
            </h2>
            <div class="lain center"></div>
            <p class="lead text-light mb-3">
                A useful mentor conversation can give you insight. A structured mentoring journey can create change.
            </p>
            <div class="p-2 px-3 bg-dark border border-secondary d-inline-block text-warning font-weight-bold" style="border-radius: 8px !important; font-size: 14px;">
                Goals + Sessions + Tasks + Resources + Practice + Feedback + Progress
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Journey 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=600&q=80" alt="Career Clarity Journey" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Foundational</span>
                            <h4 class="vj-font-heading text-white mb-2">Career Clarity Journey</h4>
                            <p class="text-light" style="font-size: 14px;">
                                For learners unsure about their direction. Filter choices through validated assessments and structured mentor diagnostics.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">4 Weeks • 3 Sessions • 2 Milestone Reviews</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journey 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=600&q=80" alt="Career Readiness Journey" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Graduates</span>
                            <h4 class="vj-font-heading text-white mb-2">Career Readiness Journey</h4>
                            <p class="text-light" style="font-size: 14px;">
                                For students and graduates preparing to enter the workplace. Covers professional etiquette, portfolio polish and teamwork readiness.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">6 Weeks • 5 Sessions • Portfolio Audit</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journey 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=600&q=80" alt="First Job Journey" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Job Seekers</span>
                            <h4 class="vj-font-heading text-white mb-2">First Job Journey</h4>
                            <p class="text-light" style="font-size: 14px;">
                                For learners actively preparing for competitive applications, hiring pipelines and multi-round technical tests.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">8 Weeks • 6 Sessions • Resume + LinkedIn Clinic</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journey 4 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=600&q=80" alt="Professional Growth Journey" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Professionals</span>
                            <h4 class="vj-font-heading text-white mb-2">Professional Growth Journey</h4>
                            <p class="text-light" style="font-size: 14px;">
                                For early professionals wanting to accelerate their careers, negotiate promotions and build organizational influence.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">6 Weeks • 4 Sessions • Leadership Action Plan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journey 5 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=600&q=80" alt="Career Transition Journey" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Pivot</span>
                            <h4 class="vj-font-heading text-white mb-2">Career Transition Journey</h4>
                            <p class="text-light" style="font-size: 14px;">
                                For professionals considering a pivot into a different role, functional domain or emerging high-growth industry.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">8 Weeks • 6 Sessions • Capability Re-mapping</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Journey 6 -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1573496799652-408c2ac9fe98?auto=format&fit=crop&w=600&q=80" alt="Interview Readiness Journey" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark mb-2 font-weight-bold" style="border-radius: 6px !important; width: fit-content;">Intensive</span>
                            <h4 class="vj-font-heading text-white mb-2">Interview Readiness Journey</h4>
                            <p class="text-light" style="font-size: 14px;">
                                Intensive prep for important opportunities with simulated mock interviews, behavioural coaching and compensation negotiation.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">3 Weeks • 4 Mock Simulations • Video Review</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('mentors.search') }}?type=journey" class="vj-btn vj-btn-primary px-4 py-3">
                Explore Mentorship Journeys <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="vj-impact-section pb-5" id="impact">
    <div class="container">
        <!-- Section Header (Left Heading + Right Subtext) -->
        <div class="row align-items-end g-4 mb-4">
            <div class="col-lg-7">
                <div class="vj-eyebrow">WHY VEDRIX</div>
                <h2 class="vj-impact-heading mb-0">
                    Strategic guidance. Practical execution. Continuous support.
                </h2>
            </div>
            <div class="col-lg-5">
                <p class="vj-impact-lead mb-0">
                    A flexible platform for ambitious students, early career professionals, partner colleges, universities and senior industry leaders.
                </p>
            </div>
        </div>

        <!-- Main Content: Left 2x2 Grid Card + Right Outcome Framework Card -->
        <div class="row g-4 align-items-stretch">
            <!-- Left Card: Built for long-term career value + 4 Metrics -->
            <div class="col-lg-6">
                <div class="vj-impact-box">
                    <h3 class="vj-impact-box-title">Built for long-term career value</h3>
                    <p class="vj-impact-box-sub">
                        Direct mentor proximity, practical capability studios and a progress-first mindset under one unified engagement.
                    </p>

                    <!-- 2x2 Grid with user's exact content -->
                    <div class="vj-impact-grid">
                        <!-- Stat 1 -->
                        <div class="vj-impact-stat-card">
                            <div class="vj-impact-icon-badge">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="vj-impact-stat-val">3,200+</div>
                            <div class="vj-impact-stat-title">Mentees mentored</div>
                            <div class="vj-impact-stat-sub">across 9 states</div>
                        </div>

                        <!-- Stat 2 -->
                        <div class="vj-impact-stat-card">
                            <div class="vj-impact-icon-badge">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                            <div class="vj-impact-stat-val">400+</div>
                            <div class="vj-impact-stat-title">Veteran mentors</div>
                            <div class="vj-impact-stat-sub">decades of real careers</div>
                        </div>

                        <!-- Stat 3 -->
                        <div class="vj-impact-stat-card">
                            <div class="vj-impact-icon-badge">
                                <i class="bi bi-buildings"></i>
                            </div>
                            <div class="vj-impact-stat-val">23</div>
                            <div class="vj-impact-stat-title">Partner colleges</div>
                            <div class="vj-impact-stat-sub">B2B cohorts running</div>
                        </div>

                        <!-- Stat 4 -->
                        <div class="vj-impact-stat-card">
                            <div class="vj-impact-icon-badge">
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <div class="vj-impact-stat-val">4.8 / 5</div>
                            <div class="vj-impact-stat-title">Mentee rating</div>
                            <div class="vj-impact-stat-sub">after 6-month bridge</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Card: Dark Learner Outcome Framework -->
            <div class="col-lg-6">
                <div class="vj-impact-outcome-card">
                    <!-- Background Photo with Overlay -->
                    <div class="vj-impact-outcome-bg" style="background-image: url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80');"></div>
                    <div class="vj-impact-outcome-overlay"></div>

                    <!-- Top Content -->
                    <div class="vj-impact-outcome-content">
                        <div class="vj-eyebrow">LEARNER OUTCOME FRAMEWORK</div>
                        <h3 class="vj-impact-outcome-title">
                            From fragmented advice to a structured, verified career trajectory.
                        </h3>
                        <p class="vj-impact-outcome-desc">
                            Real journey case studies present the baseline challenge, mentor intervention and measurable readiness improvement without publishing unsupported claims.
                        </p>
                    </div>

                    <!-- Bottom Pillars & Action Button -->
                    <div class="vj-impact-outcome-content">
                        <div class="vj-impact-pillars">
                            <div>
                                <div class="vj-impact-pillar-num">01</div>
                                <div class="vj-impact-pillar-text">Diagnostic baseline</div>
                            </div>
                            <div>
                                <div class="vj-impact-pillar-num">02</div>
                                <div class="vj-impact-pillar-text">Sprint progression</div>
                            </div>
                            <div>
                                <div class="vj-impact-pillar-num">03</div>
                                <div class="vj-impact-pillar-text">Verified readiness</div>
                            </div>
                        </div>

                        <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary w-100 justify-content-center">
                            Explore Verified Outcomes →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="vj-trusted-marquee-section">
    <div class="container-fluid px-0">
        <div class="vj-trusted-eyebrow">
            TRUSTED BY COLLEGES • LOVED BY EMPLOYERS • POWERED BY LEADERS
        </div>

        <div class="vj-marquee-wrapper">
            <div class="vj-marquee-track">
                <!-- Group 1 -->
                <div class="vj-logo-card">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 20px; color: #1e293b; letter-spacing: -0.02em;">Your<span style="color: #e11d48;">Story</span></span>
                </div>
                <div class="vj-logo-card">
                    <img src="https://upload.wikimedia.org/wikipedia/en/6/69/IIT_Madras_Logo.svg" alt="IIT Madras" style="height: 44px; max-width: 120px; object-fit: contain;">
                </div>
                <div class="vj-logo-card">
                    <img src="https://upload.wikimedia.org/wikipedia/en/d/d3/BITS_Pilani-Logo.svg" alt="BITS Pilani" style="height: 44px; max-width: 120px; object-fit: contain;">
                </div>
                <div class="vj-logo-card">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 18.5px; color: #334155; letter-spacing: -0.01em;">NIT Trichy</span>
                </div>
                <div class="vj-logo-card">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 21px; color: #1e293b; letter-spacing: 0.05em;">TCS</span>
                </div>
                <div class="vj-logo-card">
                    <span style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: 700; font-size: 25px; color: #007cc3; letter-spacing: -0.03em;">Infosys</span>
                </div>
                <div class="vj-logo-card">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 20px; color: #2874f0; font-style: italic;">Flipkart</span>
                </div>
                <div class="vj-logo-card">
                    <span style="font-family: 'Product Sans', var(--vj-font-heading), sans-serif; font-weight: 700; font-size: 21px;"><span style="color:#4285F4;">G</span><span style="color:#EA4335;">o</span><span style="color:#FBBC05;">o</span><span style="color:#4285F4;">g</span><span style="color:#34A853;">l</span><span style="color:#EA4335;">e</span></span>
                </div>
                <div class="vj-logo-card">
                    <span style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 17.5px; color: #5E5E5E;"><svg width="19" height="19" viewBox="0 0 20 20" fill="none"><rect width="9" height="9" fill="#F25022"/><rect x="11" width="9" height="9" fill="#7FBA00"/><rect y="11" width="9" height="9" fill="#00A4EF"/><rect x="11" y="11" width="9" height="9" fill="#FFB900"/></svg> Microsoft</span>
                </div>
                <div class="vj-logo-card">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 21px; color: #232F3E;"><span style="color:#FF9900;">amazon</span></span>
                </div>
                <div class="vj-logo-card">
                    <img src="https://upload.wikimedia.org/wikipedia/en/f/fd/Indian_Institute_of_Technology_Delhi_Logo.svg" alt="IIT Delhi" style="height: 42px; max-width: 120px; object-fit: contain;">
                </div>
                <div class="vj-logo-card">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 19.5px; color: #FC8019;">SWIGGY</span>
                </div>

                <!-- Group 2 (Duplicate for Seamless Infinite Left-to-Right Loop) -->
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 20px; color: #1e293b; letter-spacing: -0.02em;">Your<span style="color: #e11d48;">Story</span></span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <img src="https://upload.wikimedia.org/wikipedia/en/6/69/IIT_Madras_Logo.svg" alt="IIT Madras" style="height: 44px; max-width: 120px; object-fit: contain;">
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <img src="https://upload.wikimedia.org/wikipedia/en/d/d3/BITS_Pilani-Logo.svg" alt="BITS Pilani" style="height: 44px; max-width: 120px; object-fit: contain;">
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 18.5px; color: #334155; letter-spacing: -0.01em;">NIT Trichy</span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 21px; color: #1e293b; letter-spacing: 0.05em;">TCS</span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: 700; font-size: 25px; color: #007cc3; letter-spacing: -0.03em;">Infosys</span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 20px; color: #2874f0; font-style: italic;">Flipkart</span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: 'Product Sans', var(--vj-font-heading), sans-serif; font-weight: 700; font-size: 21px;"><span style="color:#4285F4;">G</span><span style="color:#EA4335;">o</span><span style="color:#FBBC05;">o</span><span style="color:#4285F4;">g</span><span style="color:#34A853;">l</span><span style="color:#EA4335;">e</span></span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 17.5px; color: #5E5E5E;"><svg width="19" height="19" viewBox="0 0 20 20" fill="none"><rect width="9" height="9" fill="#F25022"/><rect x="11" width="9" height="9" fill="#7FBA00"/><rect y="11" width="9" height="9" fill="#00A4EF"/><rect x="11" y="11" width="9" height="9" fill="#FFB900"/></svg> Microsoft</span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 21px; color: #232F3E;"><span style="color:#FF9900;">amazon</span></span>
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <img src="https://upload.wikimedia.org/wikipedia/en/f/fd/Indian_Institute_of_Technology_Delhi_Logo.svg" alt="IIT Delhi" style="height: 42px; max-width: 120px; object-fit: contain;">
                </div>
                <div class="vj-logo-card" aria-hidden="true">
                    <span style="font-family: var(--vj-font-heading); font-weight: 800; font-size: 19.5px; color: #FC8019;">SWIGGY</span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- =========================================================================
     SECTION 13 — FOR MENTORS (Dark: #12192C)
     Eyebrow: SHAPE WHAT COMES NEXT
     Title: Your Experience Could Be Someone Else's Turning Point.
     Image Box Showcase + 10 Checklist Benefits
     ========================================================================= -->
<section class="vj-section-dark-alt" id="for-mentors">
    <div class="container">
        <div class="row align-items-center g-5 mb-5">
            <div class="col-lg-6">
                <span class="vj-eyebrow vj-eyebrow-mustard">SHAPE WHAT COMES NEXT</span>
                <h2 class="vj-font-heading display-6 mb-3 text-white">
                    Your Experience Could Be <br><span style="color: var(--vj-primary);">Someone Else's Turning Point.</span>
                </h2>
                <p class="lead text-light mb-4">
                    Behind every experienced professional are decisions, failures, lessons and insights that no textbook can fully capture. Vedrix gives professionals a structured way to pass that experience forward.
                </p>

                <!-- Mentor Image Box -->
                <div class="vj-img-box mb-4" style="height: 250px;">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80" alt="Pass Experience Forward" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-img-box-overlay">
                        <i class="bi bi-quote fs-2 text-warning"></i>
                        <p class="text-white font-italic mb-0" style="font-size: 16px; font-weight: 600;">
                            "You don't need to have every answer. You need experience worth sharing and the willingness to guide."
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}?role=mentor" class="vj-btn vj-btn-primary px-4 py-3">
                        <i class="bi bi-person-plus-fill"></i> Become a Vedrix Mentor
                    </a>
                    <a href="{{ url('/about') }}#how-it-works" class="vj-btn vj-btn-secondary px-4 py-3">
                        How Mentoring Works <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <h4 class="vj-font-heading text-white mb-3">As a Vedrix Mentor You Can:</h4>
                <div class="d-flex flex-column gap-2">
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Create a verified professional mentor profile</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Define your specialized areas of expertise</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Set your own calendar availability & session pricing</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Conduct 1:1 focused sessions and office hours</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Participate in structured mentoring journeys</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Assign practical exercises, resources, and tasks</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Review learner progress with standardized rubrics</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Share career articles and masterclass webinars</span>
                    </div>
                    <div class="vj-mentor-benefit-card">
                        <i class="bi bi-check2-circle text-warning fs-5"></i>
                        <span>Build meaningful, visible industry leadership impact</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="vj-section-light" id="organisations">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow vj-eyebrow-mustard">MENTORSHIP AT SCALE</span>
            <h2 class="vj-font-heading display-6 mb-3 text-dark">
                Give Every Learner More Than Career Advice.
            </h2>
            <p class="lead" style="color: #334155;">
                Vedrix helps colleges, universities, placement teams and organisations build structured career-readiness and mentoring programs at scale.
            </p>
        </div>

        <div class="row g-4 mb-5">
            <!-- Segment 1: Colleges & Universities -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=600&q=80" alt="Colleges and Universities" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Higher Education</span>
                            <h4 class="vj-font-heading text-dark mb-2">Colleges & Universities</h4>
                            <p style="color: #334155; font-size: 14px;" class="mb-3">
                                Empower your Training & Placement Cells with institutional-grade readiness infrastructure.
                            </p>
                            <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 13px; color: #1E293B;">
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Career-readiness assessments</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Student cohort onboarding at scale</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Vetted industry mentor access</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Placement-readiness sprint journeys</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Real-time cohort progress analytics</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <a href="{{ url('/contact') }}?type=university" class="vj-btn vj-btn-primary w-100 justify-content-center">
                                Partner for Institutions
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segment 2: Employers & Talent Teams -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=600&q=80" alt="Employers" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-primary text-white font-weight-bold mb-2" style="border-radius: 6px !important; background-color: #1A2340 !important; width: fit-content;">Corporate</span>
                            <h4 class="vj-font-heading text-dark mb-2">Employers & Talent Teams</h4>
                            <p style="color: #334155; font-size: 14px;" class="mb-3">
                                Accelerate graduate hires and early-career professional development with internal and external mentors.
                            </p>
                            <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 13px; color: #1E293B;">
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Graduate development onboarding</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Early-career employee retention mentoring</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Functional & technical skill journeys</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Emerging talent leadership pipelines</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Internal mentoring program management</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <a href="{{ url('/contact') }}?type=employer" class="vj-btn vj-btn-primary w-100 justify-content-center">
                                Partner for Employers
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segment 3: Knowledge & Ecosystem Partners -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1521737852567-6949f3f9f2b5?auto=format&fit=crop&w=600&q=80" alt="Ecosystem Partners" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-success text-white font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Ecosystem</span>
                            <h4 class="vj-font-heading text-dark mb-2">Partners & Ecosystems</h4>
                            <p style="color: #334155; font-size: 14px;" class="mb-3">
                                Collaborate on national employability initiatives, CSR youth programs, and industry associations.
                            </p>
                            <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 13px; color: #1E293B;">
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Industry body & association alignments</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> CSR youth skill & mentorship programs</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Training and boot-camp integrations</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Joint employability certifications</li>
                                <li><i class="bi bi-check-lg text-warning font-weight-bold me-2"></i> Regional career clinic activations</li>
                            </ul>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <a href="{{ url('/contact') }}?type=partner" class="vj-btn vj-btn-primary w-100 justify-content-center">
                                Explore Partnerships
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center d-flex flex-wrap justify-content-center gap-3">
            <a href="{{ url('/contact') }}" class="vj-btn vj-btn-primary px-4 py-3">
                Partner With Vedrix
            </a>
            <a href="{{ url('/contact') }}" class="vj-btn vj-btn-outline-navy px-4 py-3">
                Request a Discussion <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<section class="vj-section-dark-alt" id="formats">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow vj-eyebrow-mustard">MENTORSHIP EXPERIENCES</span>
            <h2 class="vj-font-heading display-6 mb-3 text-white">
                Guidance Should Fit Into Real Life.
            </h2>
            <div class="lain center"></div>
            <p class="lead text-light">
                Different questions require different kinds of mentoring. Vedrix supports multiple ways to connect.
            </p>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=600&q=80" alt="1:1 Mentoring" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading text-white mb-2">One-to-One Mentoring</h4>
                            <p class="text-light" style="font-size: 14px;">
                                Focused 45-60 minute sessions built entirely around your specific challenges and immediate decision points.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">Private Video • Direct Feedback</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80" alt="Office Hours" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading text-white mb-2">Mentor Office Hours</h4>
                            <p class="text-light" style="font-size: 14px;">
                                Access experienced leaders during open slots for quick, focused questions, resume feedback and direction checks.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">Rapid Q&A • Open Calendar Slots</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=600&q=80" alt="Goal-Based Programs" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading text-white mb-2">Goal-Based Programs</h4>
                            <p class="text-light" style="font-size: 14px;">
                                Work with dedicated mentors through multi-week development journeys towards measurable career milestones.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">Milestone Sprints • Weekly Reviews</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1531497865144-0464ef8fb9a9?auto=format&fit=crop&w=600&q=80" alt="Group Mentoring" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading text-white mb-2">Group Mentoring</h4>
                            <p class="text-light" style="font-size: 14px;">
                                Learn alongside a focused cohort facing similar career challenges, sharing perspectives and peer feedback.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">Small Cohort • Shared Problem Sets</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1515187029135-18ee286d815b?auto=format&fit=crop&w=600&q=80" alt="Expert Sessions" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading text-white mb-2">Expert Sessions</h4>
                            <p class="text-light" style="font-size: 14px;">
                                Deep-dive masterclasses on industry trends, functional skills and executive hiring practices with seasoned specialists.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">Specialized Masterclasses • Live Demos</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card vj-image-card-dark">
                    <img src="https://images.unsplash.com/photo-1588196749597-9ff075ee6b5b?auto=format&fit=crop&w=600&q=80" alt="Video Mentoring" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <h4 class="vj-font-heading text-white mb-2">Online Video Sessions</h4>
                            <p class="text-light" style="font-size: 14px;">
                                Connect seamlessly from any location through browser-based high definition video with integrated notes and screenshare.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-secondary mt-3">
                            <span class="text-warning small font-weight-bold">HD Video • Browser-Native Calling</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-primary px-4 py-3">
                <i class="bi bi-calendar-check"></i> Book a Mentor Session
            </a>
        </div>
    </div>
</section>
<section class="vj-section-white" id="studio">
    <div class="container">
        <div class="text-center max-w-750 mx-auto mb-5">
            <span class="vj-eyebrow vj-eyebrow-mustard">BUILD. PRACTICE. PROVE.</span>
            <h2 class="vj-font-heading display-6 mb-3 text-dark">
                Career Advice Matters More When You Can Act On It.
            </h2>
            <p class="lead" style="color: #334155;">
                Vedrix goes beyond mentor conversations by giving learners interactive tools to improve their real-world career readiness.
            </p>
        </div>

        <div class="row g-4 mb-5">
            <!-- Studio 1: Interview Gym -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=600&q=80" alt="Interview Gym" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Simulation Tool</span>
                            <h4 class="vj-font-heading text-dark mb-2">Interview Gym</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Practice real interview questions, analyze responses with structured frameworks, and build unmatched boardroom confidence.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <span class="text-warning font-weight-bold small" style="color: #B45309 !important;"><i class="bi bi-play-circle-fill me-1"></i> Interactive Video Simulations</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Studio 2: Resume Studio -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1586281380349-632531db7ed4?auto=format&fit=crop&w=600&q=80" alt="Resume Studio" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Audit Tool</span>
                            <h4 class="vj-font-heading text-dark mb-2">Resume Studio</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Build, review and optimize your resume around ATS requirements and target roles with mentor review notes.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <span class="text-warning font-weight-bold small" style="color: #B45309 !important;"><i class="bi bi-file-earmark-check-fill me-1"></i> ATS Scoring & Rebuilds</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Studio 3: LinkedIn Studio -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1611944212129-29977ae1398c?auto=format&fit=crop&w=600&q=80" alt="LinkedIn Studio" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Branding Tool</span>
                            <h4 class="vj-font-heading text-dark mb-2">LinkedIn Studio</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Elevate your professional positioning, headline, featured content, and digital narrative to attract top recruiters.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <span class="text-warning font-weight-bold small" style="color: #B45309 !important;"><i class="bi bi-lightning-charge-fill me-1"></i> Recruiter Magnet Positioning</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Studio 4: Project Vault -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=600&q=80" alt="Project Vault" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Portfolio Tool</span>
                            <h4 class="vj-font-heading text-dark mb-2">Project Vault</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Bring your capstone projects, assignments, problem sets and mentor-validated work together into one verifiable portfolio.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <span class="text-warning font-weight-bold small" style="color: #B45309 !important;"><i class="bi bi-folder-fill me-1"></i> Verified Project Proof</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Studio 5: Career Path Explorer -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=600&q=80" alt="Career Path Explorer" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Roadmap Tool</span>
                            <h4 class="vj-font-heading text-dark mb-2">Career Path Explorer</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Understand possible paths, role transitions, prerequisite capabilities and realistic compensation trajectories.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <span class="text-warning font-weight-bold small" style="color: #B45309 !important;"><i class="bi bi-diagram-3-fill me-1"></i> 50+ Role Pathways</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Studio 6: Task & Practice Hub -->
            <div class="col-lg-4 col-md-6">
                <div class="vj-image-card">
                    <img src="https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=600&q=80" alt="Task Hub" onerror="this.src='{{ asset('frontend/images/insights-banner.jpg') }}';">
                    <div class="vj-image-card-body">
                        <div>
                            <span class="badge bg-warning text-dark font-weight-bold mb-2" style="border-radius: 6px !important; width: fit-content;">Accountability Tool</span>
                            <h4 class="vj-font-heading text-dark mb-2">Task & Practice Hub</h4>
                            <p style="color: #334155; font-size: 14px;">
                                Complete mentor-assigned exercises, weekly challenges and practical career actions with structured accountability.
                            </p>
                        </div>
                        <div class="pt-3 border-top border-light-subtle mt-3">
                            <span class="text-warning font-weight-bold small" style="color: #B45309 !important;"><i class="bi bi-check-circle-fill me-1"></i> Sprint Accountability</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('mentors.search') }}?tab=studio" class="vj-btn vj-btn-primary px-4 py-3">
                Explore Career Tools <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>


<section class="vj-cta-strip">
    <div class="container text-center max-w-750 mx-auto position-relative" style="z-index: 2;">
        <span class="vj-hero-badge mb-3">YOUR NEXT STEP DOESN'T HAVE TO BE A GUESS</span>
        <h2 class="vj-hero-title mb-3" style="font-size: 2.8rem;">
            You Bring The Ambition. <br><span>We'll Help You Find The Direction.</span>
        </h2>
        <p class="vj-hero-desc mb-4 mx-auto" style="max-width: 650px;">
            Whether you're choosing your first career, preparing for your first job, navigating your next move or ready to mentor someone else, Vedrix gives you a place to begin.
        </p>

        <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
            <a href="#paths" class="vj-btn vj-btn-primary px-4 py-3">
                <i class="bi bi-compass"></i> Start Your Vedrix Journey
            </a>
            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-secondary px-4 py-3">
                <i class="bi bi-search"></i> Find a Mentor
            </a>
            <a href="{{ route('register') }}?role=mentor" class="vj-btn-text-mustard ms-md-2">
                Become a Mentor <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // =========================================================================
        // SECTION 01 — HERO TABBED SLIDER BANNER (Full-Width Auto-Slide Controller)
        // =========================================================================
        const heroTabCards = document.querySelectorAll('.vj-hero-tab-card');
        const heroBgLayers = document.querySelectorAll('.vj-hero-bg-layer');
        const heroSlidePanes = document.querySelectorAll('.vj-hero-slide-pane');
        const heroSliderBanner = document.getElementById('heroBannerSlider');

        let currentHeroSlide = 0;
        const totalHeroSlides = heroTabCards.length;
        const slideDuration = 5000; // 5 seconds per slide
        let heroAutoSlideTimer = null;

        function switchHeroSlide(index) {
            currentHeroSlide = index;

            // 1. Update Background Layers (Explicitly manage opacity, visibility & active class)
            heroBgLayers.forEach(function (bg, i) {
                if (i === index) {
                    bg.classList.add('active');
                    bg.style.opacity = '1';
                    bg.style.visibility = 'visible';
                    bg.style.zIndex = '2';
                } else {
                    bg.classList.remove('active');
                    bg.style.opacity = '0';
                    bg.style.visibility = 'hidden';
                    bg.style.zIndex = '1';
                }
            });

            // 2. Update Tab Cards and Animate Progress Bar
            heroTabCards.forEach(function (card, i) {
                const prog = card.querySelector('.vj-hero-tab-progress');
                if (i === index) {
                    card.classList.add('active');
                    if (prog) {
                        prog.style.transition = 'none';
                        prog.style.width = '0%';
                        // Force DOM reflow to restart CSS transition cleanly
                        void prog.offsetWidth;
                        prog.style.transition = 'width ' + slideDuration + 'ms linear';
                        prog.style.width = '100%';
                    }
                } else {
                    card.classList.remove('active');
                    if (prog) {
                        prog.style.transition = 'none';
                        prog.style.width = '0%';
                    }
                }
            });

            // 3. Update Slide Content Panes
            heroSlidePanes.forEach(function (pane, i) {
                if (i === index) {
                    pane.classList.add('active');
                    pane.style.display = 'block';
                } else {
                    pane.classList.remove('active');
                    pane.style.display = 'none';
                }
            });
        }

        function nextHeroSlide() {
            let nextIndex = (currentHeroSlide + 1) % totalHeroSlides;
            switchHeroSlide(nextIndex);
        }

        function startAutoSlide() {
            stopAutoSlide();
            heroAutoSlideTimer = setInterval(nextHeroSlide, slideDuration);
        }

        function stopAutoSlide() {
            if (heroAutoSlideTimer) {
                clearInterval(heroAutoSlideTimer);
                heroAutoSlideTimer = null;
            }
        }

        // Initialize first slide and start auto-slide timer immediately
        if (totalHeroSlides > 0) {
            switchHeroSlide(0);
            startAutoSlide();
        }

        // Click Handler on Tab Cards
        heroTabCards.forEach(function (card) {
            card.addEventListener('click', function () {
                const slideIndex = parseInt(this.getAttribute('data-hero-slide'), 10);
                switchHeroSlide(slideIndex);
                startAutoSlide(); // Reset auto-slide timer
            });
        });

        // Pause on Hover
        if (heroSliderBanner) {
            heroSliderBanner.addEventListener('mouseenter', function () {
                stopAutoSlide();
                const activeProg = document.querySelector('.vj-hero-tab-card.active .vj-hero-tab-progress');
                if (activeProg) {
                    const computedWidth = window.getComputedStyle(activeProg).width;
                    activeProg.style.transition = 'none';
                    activeProg.style.width = computedWidth;
                }
            });

            heroSliderBanner.addEventListener('mouseleave', function () {
                startAutoSlide();
                const activeProg = document.querySelector('.vj-hero-tab-card.active .vj-hero-tab-progress');
                if (activeProg) {
                    activeProg.style.transition = 'width ' + slideDuration + 'ms linear';
                    activeProg.style.width = '100%';
                }
            });
        }

        // =========================================================================
        // Dedicated Section 10 Dashboard tab switcher
        // =========================================================================
        const dashNavBtns = document.querySelectorAll('.vj-dash-nav-item');
        const dashPanes = document.querySelectorAll('.vj-dash-content-pane');

        dashNavBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-dash-target');

                dashNavBtns.forEach(function (b) {
                    b.classList.remove('active');
                });
                dashPanes.forEach(function (pane) {
                    pane.classList.remove('active');
                    pane.style.display = 'none';
                });

                this.classList.add('active');
                const activePane = document.getElementById(targetId);
                if (activePane) {
                    activePane.classList.add('active');
                    activePane.style.display = 'block';
                }
            });
        });

        // =========================================================================
        // CORE FRAMEWORK TAB SWITCHER (Discover, Decide, Connect, Learn, Practice, Progress)
        // =========================================================================
        const fwTabs = document.querySelectorAll('.vj-framework-tab');
        const fwPanes = document.querySelectorAll('.vj-framework-pane');

        fwTabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-framework-target');

                fwTabs.forEach(function (t) {
                    t.classList.remove('active');
                });
                fwPanes.forEach(function (p) {
                    p.classList.remove('active');
                    p.style.display = 'none';
                });

                this.classList.add('active');
                const activePane = document.getElementById(targetId);
                if (activePane) {
                    activePane.classList.add('active');
                    activePane.style.display = 'block';
                }
            });
        });
    });
</script>
@endpush
