@extends('frontend.layouts.frontend')
@section('title', 'Career Guides & Transition Roadmaps — Vedrix Knowledge Hub')
@section('meta_description', 'Actionable career roadmaps, domain transition frameworks, interview prep tips, and mentor strategies for modern tech and business roles.')

@section('content')
<div class="insights-page">

    {{-- HERO BANNER (DARK) --}}
    <section class="insights-banner">
        <div class="insights-banner__bg" aria-hidden="true"></div>
        <div class="insights-banner__overlay" aria-hidden="true"></div>
        <div class="container insights-banner__inner">
            <nav class="insights-breadcrumb">
                <a href="{{ route('home') }}"><i class="bi bi-house-door-fill me-1"></i> Home</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <a href="{{ route('insights.index') }}">Insights</a>
                <span><i class="bi bi-chevron-right"></i></span>
                <span>Career Guides</span>
            </nav>
            <div class="insights-banner__eyebrow">
                <i class="bi bi-compass-fill me-1"></i> Career Navigation & Playbooks
            </div>
            <h1 class="insights-banner__title">Actionable Career Playbooks & Transition Roadmaps</h1>
            <div class="lain"></div>
            <p class="insights-banner__sub">
                Step-by-step guidance designed by senior industry leaders to help you master role transitions, ace technical evaluations, and accelerate your career trajectory.
            </p>
        </div>
    </section>

    {{-- SECTION 1: LIGHT COMBINATION — 4-Stage Career Transition Framework --}}
    <section class="cg-section cg-section-light">
        <div class="container">
            <div class="insights-section-header text-center mb-5">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-diagram-3-fill me-1"></i> PROVEN METHODOLOGY
                </span>
                <h2 class="vj-font-heading h3 mb-2">The 4-Stage Career Acceleration Blueprint</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    How high-performing candidates systematically move from direction ambiguity to high-leverage offers.
                </p>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="cg-step-card">
                                <span class="cg-step-badge">01</span>
                                <h4 class="h6 fw-bold mb-2">Self & Market Audit</h4>
                                <p class="text-muted small mb-0">Identify current skill gaps against actual job market benchmarks rather than outdated generic job descriptions.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="cg-step-card">
                                <span class="cg-step-badge">02</span>
                                <h4 class="h6 fw-bold mb-2">Target Role Calibration</h4>
                                <p class="text-muted small mb-0">Narrow down to 1-2 high-affinity career tracks and align your portfolio with current industry expectations.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="cg-step-card">
                                <span class="cg-step-badge">03</span>
                                <h4 class="h6 fw-bold mb-2">Proof-of-Work Stacking</h4>
                                <p class="text-muted small mb-0">Build end-to-end production systems, case studies, or design artifacts that indisputably demonstrate competency.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="cg-step-card">
                                <span class="cg-step-badge">04</span>
                                <h4 class="h6 fw-bold mb-2">Simulated Interviewing</h4>
                                <p class="text-muted small mb-0">Execute rigorous mock interviews with active hiring managers to calibrate behavioral and technical responses.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="position-relative ps-lg-3">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=900&q=80"
                             alt="Career Framework Discussion"
                             class="img-fluid rounded-4 shadow-sm w-100"
                             style="max-height: 452px; object-fit: cover;"
                             loading="lazy">
                        <div class="p-3 bg-white border rounded-3 shadow-sm position-absolute bottom-0 start-0 m-4 d-none d-sm-flex align-items-center gap-3">
                            <div class="bg-warning-subtle text-warning p-2 rounded-2">
                                <i class="bi bi-shield-check fs-4 text-warning"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6">92% Transition Success</div>
                                <div class="text-muted small">Guided by 1-on-1 industry mentors</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 2: DARK COMBINATION — Interactive Role Playbooks (Tabs) --}}
    <section class="cg-section cg-section-dark">
        <div class="container">
            <div class="insights-section-header text-center mb-5">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-layers-fill me-1"></i> DOMAIN ROADMAPS
                </span>
                <h2 class="cg-dark-title h3 mb-2">Explore Step-by-Step Domain Playbooks</h2>
                <div class="lain center"></div>
                <p class="cg-dark-subtitle small mb-0">
                    Select a high-growth career track to inspect required skills, milestone roadmaps, and transition strategies.
                </p>
            </div>

            {{-- Tabs Navigation --}}
            <ul class="cg-tabs-nav" id="careerTabs" role="tablist">
                <li>
                    <button class="cg-tab-btn active" id="swe-tab" data-bs-toggle="pill" data-bs-target="#swe" type="button" role="tab">
                        <i class="bi bi-code-slash"></i> Software Engineering
                    </button>
                </li>
                <li>
                    <button class="cg-tab-btn" id="pm-tab" data-bs-toggle="pill" data-bs-target="#pm" type="button" role="tab">
                        <i class="bi bi-kanban"></i> Product Management
                    </button>
                </li>
                <li>
                    <button class="cg-tab-btn" id="data-tab" data-bs-toggle="pill" data-bs-target="#data" type="button" role="tab">
                        <i class="bi bi-cpu"></i> AI & Data Science
                    </button>
                </li>
                <li>
                    <button class="cg-tab-btn" id="design-tab" data-bs-toggle="pill" data-bs-target="#design" type="button" role="tab">
                        <i class="bi bi-palette"></i> UI/UX Design
                    </button>
                </li>
                <li>
                    <button class="cg-tab-btn" id="cloud-tab" data-bs-toggle="pill" data-bs-target="#cloud" type="button" role="tab">
                        <i class="bi bi-cloud-check"></i> Cloud & DevOps
                    </button>
                </li>
            </ul>

            {{-- Tabs Content --}}
            <div class="tab-content" id="careerTabsContent">

                {{-- Tab 1: Software Engineering --}}
                <div class="tab-pane fade show active" id="swe" role="tabpanel">
                    <div class="cg-dark-card">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-3">
                                    <i class="bi bi-terminal me-1"></i> Engineering Track
                                </span>
                                <h3 class="cg-dark-title h4 mb-3">Software Engineering & Architecture Roadmap</h3>
                                <p class="cg-dark-subtitle mb-4">
                                    Master systemic programming principles, distributed architectures, and algorithms needed to clear Tier-1 engineering bars.
                                </p>
                                <div class="mb-4">
                                    <h5 class="text-white fs-6 fw-bold mb-2">Core Tech Competencies:</h5>
                                    <div>
                                        <span class="cg-skill-tag">Data Structures & Algos</span>
                                        <span class="cg-skill-tag">System Design (HLD/LLD)</span>
                                        <span class="cg-skill-tag">REST / gRPC APIs</span>
                                        <span class="cg-skill-tag">Microservices</span>
                                        <span class="cg-skill-tag">SQL & NoSQL Caching</span>
                                        <span class="cg-skill-tag">CI/CD Pipelines</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-mustard btn-sm text-white">
                                        Find SWE Mentors <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                    <span class="cg-dark-text-muted small"><i class="bi bi-clock-history me-1"></i> Avg. Timeline: 4 - 6 Months</span>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=700&q=80"
                                     alt="Software Engineering"
                                     class="img-fluid rounded-4 border border-secondary border-opacity-25 w-100"
                                     style="height: 260px; object-fit: cover;"
                                     loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 2: Product Management --}}
                <div class="tab-pane fade" id="pm" role="tabpanel">
                    <div class="cg-dark-card">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-3">
                                    <i class="bi bi-briefcase me-1"></i> Product Leadership
                                </span>
                                <h3 class="cg-dark-title h4 mb-3">Product Management & Growth Strategy</h3>
                                <p class="cg-dark-subtitle mb-4">
                                    Learn product discovery, PRD authoring, North Star metric tracking, user research, and executive stakeholder alignment.
                                </p>
                                <div class="mb-4">
                                    <h5 class="text-white fs-6 fw-bold mb-2">Core PM Competencies:</h5>
                                    <div>
                                        <span class="cg-skill-tag">Product Sense & Design</span>
                                        <span class="cg-skill-tag">Product Execution & Root Cause</span>
                                        <span class="cg-skill-tag">A/B Testing & Analytics</span>
                                        <span class="cg-skill-tag">Roadmapping & Prioritization</span>
                                        <span class="cg-skill-tag">GTM Strategy</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-mustard btn-sm text-white">
                                        Find PM Mentors <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                    <span class="cg-dark-text-muted small"><i class="bi bi-clock-history me-1"></i> Avg. Timeline: 3 - 5 Months</span>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=700&q=80"
                                     alt="Product Management"
                                     class="img-fluid rounded-4 border border-secondary border-opacity-25 w-100"
                                     style="height: 260px; object-fit: cover;"
                                     loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 3: AI & Data Science --}}
                <div class="tab-pane fade" id="data" role="tabpanel">
                    <div class="cg-dark-card">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-3">
                                    <i class="bi bi-graph-up me-1"></i> Analytics & ML
                                </span>
                                <h3 class="cg-dark-title h4 mb-3">Data Science, ML & Generative AI</h3>
                                <p class="cg-dark-subtitle mb-4">
                                    From exploratory data analysis and statistical modeling to deploying LLMs and predictive pipelines in production.
                                </p>
                                <div class="mb-4">
                                    <h5 class="text-white fs-6 fw-bold mb-2">Core Data Competencies:</h5>
                                    <div>
                                        <span class="cg-skill-tag">Python & SQL Mastery</span>
                                        <span class="cg-skill-tag">Machine Learning Algorithms</span>
                                        <span class="cg-skill-tag">LLM Fine-Tuning & RAG</span>
                                        <span class="cg-skill-tag">Feature Engineering</span>
                                        <span class="cg-skill-tag">Model Deployment (MLOps)</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-mustard btn-sm text-white">
                                        Find AI/ML Mentors <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                    <span class="cg-dark-text-muted small"><i class="bi bi-clock-history me-1"></i> Avg. Timeline: 4 - 6 Months</span>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=700&q=80"
                                     alt="Data Science"
                                     class="img-fluid rounded-4 border border-secondary border-opacity-25 w-100"
                                     style="height: 260px; object-fit: cover;"
                                     loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 4: UI/UX Design --}}
                <div class="tab-pane fade" id="design" role="tabpanel">
                    <div class="cg-dark-card">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-3">
                                    <i class="bi bi-brush me-1"></i> Design Systems
                                </span>
                                <h3 class="cg-dark-title h4 mb-3">UI/UX & Product Design Playbook</h3>
                                <p class="cg-dark-subtitle mb-4">
                                    Build high-impact portfolios featuring real product teardowns, design systems, usability evaluations, and interactive prototypes.
                                </p>
                                <div class="mb-4">
                                    <h5 class="text-white fs-6 fw-bold mb-2">Core Design Competencies:</h5>
                                    <div>
                                        <span class="cg-skill-tag">Figma & Auto-Layout</span>
                                        <span class="cg-skill-tag">Design Systems & Tokens</span>
                                        <span class="cg-skill-tag">User Research & Personas</span>
                                        <span class="cg-skill-tag">Wireframing & Prototyping</span>
                                        <span class="cg-skill-tag">Usability Testing</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-mustard btn-sm text-white">
                                        Find Design Mentors <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                    <span class="cg-dark-text-muted small"><i class="bi bi-clock-history me-1"></i> Avg. Timeline: 3 - 5 Months</span>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <img src="https://images.unsplash.com/photo-1581291518655-9523c932edcf?auto=format&fit=crop&w=700&q=80"
                                     alt="UI UX Design"
                                     class="img-fluid rounded-4 border border-secondary border-opacity-25 w-100"
                                     style="height: 260px; object-fit: cover;"
                                     loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 5: Cloud & DevOps --}}
                <div class="tab-pane fade" id="cloud" role="tabpanel">
                    <div class="cg-dark-card">
                        <div class="row g-4 align-items-center">
                            <div class="col-lg-7">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-3">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Infrastructure
                                </span>
                                <h3 class="cg-dark-title h4 mb-3">Cloud Engineering & DevOps Roadmap</h3>
                                <p class="cg-dark-subtitle mb-4">
                                    Architect resilient cloud infrastructure, configure automated Kubernetes clusters, and master Infrastructure as Code (IaC).
                                </p>
                                <div class="mb-4">
                                    <h5 class="text-white fs-6 fw-bold mb-2">Core Cloud Competencies:</h5>
                                    <div>
                                        <span class="cg-skill-tag">AWS / Azure / GCP</span>
                                        <span class="cg-skill-tag">Docker & Kubernetes</span>
                                        <span class="cg-skill-tag">Terraform & Ansible</span>
                                        <span class="cg-skill-tag">CI/CD Automation</span>
                                        <span class="cg-skill-tag">Observability & Monitoring</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-mustard btn-sm text-white">
                                        Find DevOps Mentors <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                    <span class="cg-dark-text-muted small"><i class="bi bi-clock-history me-1"></i> Avg. Timeline: 4 - 6 Months</span>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=700&q=80"
                                     alt="Cloud DevOps"
                                     class="img-fluid rounded-4 border border-secondary border-opacity-25 w-100"
                                     style="height: 260px; object-fit: cover;"
                                     loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- SECTION 3: LIGHT COMBINATION — 6 Golden Rules for Career Acceleration --}}
    <section class="cg-section cg-section-light-alt">
        <div class="container">
            <div class="insights-section-header text-center mb-5">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-award-fill me-1"></i> MENTOR STRATEGIES
                </span>
                <h2 class="vj-font-heading h3 mb-2">6 Golden Rules for Rapid Career Advancement</h2>
                <div class="lain center"></div>
                <p class="text-muted small mb-0">
                    Insights distilled from 1,000+ mentorship discussions across Fortune 500 engineering and product teams.
                </p>
            </div>

            <div class="row g-4">
                {{-- Tip 1 --}}
                <div class="col-lg-4 col-md-6">
                    <div class="cg-tip-card">
                        <div class="cg-tip-icon cg-tip-icon--gold">
                            <i class="bi bi-file-earmark-code"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">1. Proof of Work Over Generic Resumes</h4>
                        <p class="text-muted small mb-0">
                            Hiring managers skim text. Link live deployed applications, open source PRs, or public Figma prototypes directly in your header.
                        </p>
                    </div>
                </div>

                {{-- Tip 2 --}}
                <div class="col-lg-4 col-md-6">
                    <div class="cg-tip-card">
                        <div class="cg-tip-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">2. Metric-Driven Achievement Bullet Points</h4>
                        <p class="text-muted small mb-0">
                            Structure bullet points with the XYZ formula: Accomplished [X], measured by [Y], by doing [Z]. Quantify business impact.
                        </p>
                    </div>
                </div>

                {{-- Tip 3 --}}
                <div class="col-lg-4 col-md-6">
                    <div class="cg-tip-card">
                        <div class="cg-tip-icon cg-tip-icon--green">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">3. Warm Inbound Referral Networks</h4>
                        <p class="text-muted small mb-0">
                            Never apply cold. Connect with domain practitioners on Vedrix and LinkedIn for strategic referrals that skip early ATS filters.
                        </p>
                    </div>
                </div>

                {{-- Tip 4 --}}
                <div class="col-lg-4 col-md-6">
                    <div class="cg-tip-card">
                        <div class="cg-tip-icon cg-tip-icon--purple">
                            <i class="bi bi-camera-video"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">4. Record & Critique Mock Interviews</h4>
                        <p class="text-muted small mb-0">
                            You cannot fix what you do not measure. Practice whiteboard and behavioral questions under simulated time constraints.
                        </p>
                    </div>
                </div>

                {{-- Tip 5 --}}
                <div class="col-lg-4 col-md-6">
                    <div class="cg-tip-card">
                        <div class="cg-tip-icon cg-tip-icon--teal">
                            <i class="bi bi-layers"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">5. Compound Niche Skill Stacking</h4>
                        <p class="text-muted small mb-0">
                            Pair your primary technical strength with a secondary high-demand domain (e.g. Frontend + WebGL, Backend + FinTech Compliance).
                        </p>
                    </div>
                </div>

                {{-- Tip 6 --}}
                <div class="col-lg-4 col-md-6">
                    <div class="cg-tip-card">
                        <div class="cg-tip-icon cg-tip-icon--red">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h4 class="h5 fw-bold mb-2">6. Strategic Offer & Equity Negotiation</h4>
                        <p class="text-muted small mb-0">
                            Never accept the first offer immediately. Benchmark compensation with verified data and leverage competing offers professionally.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 4: DARK COMBINATION — Accordion Deep-Dive Guides --}}
    <section class="cg-section cg-section-dark-alt">
        <div class="container">
            <div class="insights-section-header text-center mb-5">
                <span class="vj-eyebrow vj-eyebrow-mustard mb-2 d-inline-block">
                    <i class="bi bi-patch-question-fill me-1"></i> TRANSITION PLAYBOOKS
                </span>
                <h2 class="cg-dark-title h3 mb-2">Frequently Consulted Career Playbooks</h2>
                <div class="lain center"></div>
                <p class="cg-dark-subtitle small mb-0">
                    Comprehensive answers to critical transition hurdles, interview barriers, and promotion strategies.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="accordion cg-accordion" id="careerAccordion">

                        {{-- Item 1 --}}
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="bi bi-arrow-repeat text-warning me-2"></i> How do I pivot into Tech or Product without direct prior experience?
                                </button>
                            </h3>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#careerAccordion">
                                <div class="accordion-body">
                                    Start by identifying <strong>transferable domain expertise</strong>. If you come from sales or finance, transitioning into Product or Engineering in FinTech/SaaS provides natural leverage. Secondly, build 2-3 production-grade personal projects solving real problems. Working 1-on-1 with an industry mentor validates your project architecture and provides high-credibility referrals.
                                </div>
                            </div>
                        </div>

                        {{-- Item 2 --}}
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="bi bi-file-earmark-check text-warning me-2"></i> What makes a resume beat the ATS and get shortlisted by Engineering Leads?
                                </button>
                            </h3>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#careerAccordion">
                                <div class="accordion-body">
                                    Modern ATS scanners search for direct contextual keyword matches. Use clean, single-column formats without tables or graphics. Ensure each bullet point highlights quantifiable business outcomes (e.g. <em>"Reduced latency by 38% using Redis caching and database indexing"</em>). Include GitHub links and live demo URLs prominently in your header.
                                </div>
                            </div>
                        </div>

                        {{-- Item 3 --}}
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <i class="bi bi-diagram-2 text-warning me-2"></i> How should I prepare for System Design and Architectural evaluations?
                                </button>
                            </h3>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#careerAccordion">
                                <div class="accordion-body">
                                    System design interviews evaluate your ability to navigate trade-offs under ambiguity. Structure your answer using the standard framework: 1) Functional & Non-Functional Requirements, 2) Capacity Estimations, 3) High-Level Architecture, 4) Database Schema & Caching Strategy, 5) Bottlenecks, Rate Limiting & Fault Tolerance. Practice realistic mock evaluations with senior architects.
                                </div>
                            </div>
                        </div>

                        {{-- Item 4 --}}
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="bi bi-currency-dollar text-warning me-2"></i> How do I negotiate compensation packages and equity grants effectively?
                                </button>
                            </h3>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#careerAccordion">
                                <div class="accordion-body">
                                    Never provide target salary numbers early in the screening process. Once an offer arrives, express genuine enthusiasm and request 48-72 hours to review the breakdown. Benchmark total compensation (Base + Annual Bonus + Stock Options/RSUs + Joining Bonus) against verified market percentiles. Ask mentors for offer evaluation to determine negotiation levers.
                                </div>
                            </div>
                        </div>

                        {{-- Item 5 --}}
                        <div class="accordion-item">
                            <h3 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <i class="bi bi-person-bounding-box text-warning me-2"></i> How does 1-on-1 mentorship accelerate my career timeline?
                                </button>
                            </h3>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#careerAccordion">
                                <div class="accordion-body">
                                    Self-learning often leads to tutorial paralysis and wasted months on non-essential skills. A seasoned mentor provides personalized roadmap clarity, code and portfolio reviews, unscripted interview prep, and direct accountability — compressing what typically takes 18-24 months of trial and error into 3-4 months of focused execution.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 5: LIGHT COMBINATION — Free Downloads & Mentor Consultation Callout --}}
    <section class="cg-section cg-section-light">
        <div class="container">
            <div class="row g-4">
                {{-- Box 1 --}}
                <div class="col-lg-6">
                    <div class="p-4 p-lg-5 bg-white border rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill mb-3">
                                <i class="bi bi-file-earmark-arrow-down me-1"></i> FREE DOWNLOADS
                            </span>
                            <h3 class="vj-font-heading h4 fw-bold mb-2">Download Career Roadmaps & Checklists</h3>
                            <p class="text-muted small mb-4">
                                Access ready-to-use resume templates, interview question banks, career pivot roadmaps, and competency scorecards in our Download Centre.
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('insights.download-centre.index') }}" class="vj-btn vj-btn-outline-mustard btn-sm">
                                <i class="bi bi-download me-1"></i> Visit Download Centre
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Box 2 --}}
                <div class="col-lg-6">
                    <div class="p-4 p-lg-5 bg-dark text-white rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between border border-secondary border-opacity-25" style="background: linear-gradient(135deg, #0B0F19 0%, #1E293B 100%);">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-3">
                                <i class="bi bi-person-check-fill me-1"></i> 1-ON-1 GUIDANCE
                            </span>
                            <h3 class="text-white h4 fw-bold mb-2">Connect With Verified Industry Mentors</h3>
                            <p style="color: #CBD5E1;" class="small mb-4">
                                Book personalized mock interviews, portfolio walkthroughs, or roadmap sessions with mentors from top global engineering and product teams.
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('mentors.search') }}" class="vj-btn vj-btn-mustard btn-sm text-white">
                                <i class="bi bi-search me-1"></i> Find Your Mentor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
