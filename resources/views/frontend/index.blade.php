<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acharya Setu - Coming Soon</title>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.svg"/>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --acharya-yellow: #f5a91b;
            --setu-navy: #1a2942;
            --dark-bg: #0f1419;
            --card-bg: #1a1f2e;
            --text-light: #e4e6eb;
            --text-muted: #9ca3af;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-light);
            overflow-x: hidden;
        }
        
        /* Navigation */
        .navbar {
            background-color: rgba(15, 20, 25, 0.80);
            backdrop-filter: blur(10px);
            padding: 1.2rem 0;
            border-bottom: 1px solid rgba(255, 215, 0, 0.1);
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-light) !important;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            color: var(--acharya-yellow) !important;
            transform: scale(1.05);
        }
        
        .nav-btn {
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            display: inline-block;
            margin-left: 0.5rem;
        }
        
        .nav-btn-primary {
            background-color: var(--acharya-yellow);
            color: var(--dark-bg);
        }
        
        .nav-btn-primary:hover {
            background-color: transparent;
            border-color: var(--acharya-yellow);
            color: var(--acharya-yellow);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        
        .nav-btn-outline {
            border: 2px solid var(--setu-navy);
            color: var(--text-light);
        }
        
        .nav-btn-outline:hover {
            background-color: var(--setu-navy);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 41, 66, 0.4);
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, rgba(15, 20, 25, 0.80) 0%, rgba(26, 41, 66, 0.80) 100%),
                        url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1600&h=900&fit=crop') center/cover;
            position: relative;
            overflow: hidden;
            padding-top: 70px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.15) 0%, transparent 70%);
            top: -250px;
            right: -250px;
            border-radius: 50%;
            animation: pulse 4s ease-in-out infinite;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(26, 41, 66, 0.3) 0%, transparent 70%);
            bottom: -200px;
            left: -200px;
            border-radius: 50%;
            animation: pulse 5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--text-light) 0%, var(--acharya-yellow) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-muted);
            line-height: 1.8;
            margin-bottom: 2.5rem;
            max-width: 700px;
        }
        
        .cta-btn {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            display: inline-block;
            margin-right: 1rem;
            margin-bottom: 1rem;
        }
        
        .cta-primary {
            background-color: var(--acharya-yellow);
            color: var(--dark-bg);
        }
        
        .cta-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
            color: var(--dark-bg);
        }
        
        .cta-secondary {
            border: 2px solid var(--acharya-yellow);
            color: var(--acharya-yellow);
            background: transparent;
        }
        
        .cta-secondary:hover {
            background-color: var(--acharya-yellow);
            color: var(--dark-bg);
            transform: translateY(-3px);
        }
        
        .hero-microcopy {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 1.5rem;
        }
        
        .hero-microcopy i {
            color: var(--acharya-yellow);
            margin-right: 0.5rem;
        }
        
        /* Value Strip */
        .value-strip {
            background: linear-gradient(135deg, rgba(26, 31, 46, 0.80) 0%, rgba(15, 20, 25, 0.80) 100%),
                        url('https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1600&h=600&fit=crop') center/cover;
            padding: 4rem 0;
            border-top: 1px solid rgba(255, 215, 0, 0.2);
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
            position: relative;
        }
        
        .value-strip::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 20, 25, 0.7);
            z-index: 0;
        }
        
        .value-strip .container {
            position: relative;
            z-index: 1;
        }
        
        .value-card {
            text-align: center;
            padding: 2rem 1.5rem;
            transition: all 0.3s ease;
            border-radius: 12px;
            background-color: rgb(246 169 27 / 22%);
        }
        
        .value-card:hover {
            transform: translateY(-10px);
            background-color: rgba(255, 215, 0, 0.05);
        }
        
        .value-icon {
            font-size: 3rem;
            color: var(--acharya-yellow);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .value-card:hover .value-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .value-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-light);
        }
        
        .value-text {
            color: var(--text-muted);
            line-height: 1.6;
        }
        
        /* Section Styles */
        .section {
            padding: 5rem 0;
        }
        
        .intro-section {
            background: linear-gradient(135deg, rgba(15, 20, 25, 0.9) 0%, rgba(26, 41, 66, 0.9) 100%),
                        url('https://images.unsplash.com/photo-1552664730-d307ca884978?w=1600&h=800&fit=crop') center/cover;
            position: relative;
        }
        
        .intro-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, transparent 0%, rgba(15, 20, 25, 0.5) 100%);
        }
        
        .intro-section .container {
            position: relative;
            z-index: 1;
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-light);
        }
        
        .section-text {
            font-size: 1.1rem;
            color: var(--text-muted);
            line-height: 1.8;
            max-width: 900px;
            margin: 0 auto;
        }
        
        /* How It Works */
        .how-it-works-section {
            background: #fff;
            position: relative;
        }
        
        .how-it-works-section .container {
            position: relative;
            z-index: 1;
        }
        
        .step-card {
            background-color: var(--card-bg);
            padding: 2.5rem;
            border-radius: 16px;
            border: 2px solid transparent;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            background-image: 
                radial-gradient(circle at 90% 10%, rgba(255, 215, 0, 0.05) 0%, transparent 50%);
        }
        
        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .step-card:hover {
            border-color: var(--acharya-yellow);
            transform: translateX(10px);
        }
        
        .step-card:hover::before {
            opacity: 1;
        }
        
        .step-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--acharya-yellow);
            opacity: 0.3;
            position: absolute;
            top: 1rem;
            right: 2rem;
        }
        
        .step-icon {
            font-size: 2.5rem;
            color: var(--acharya-yellow);
            margin-bottom: 1.5rem;
            background: #fff;
            padding: 0 10px;
            border-radius: 7px;
            height: 70px;
            width: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 1px 1px 20px var(--acharya-yellow);
        }
        
        .step-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-light);
            position: relative;
            z-index: 2;
        }
        
        .step-text {
            color: var(--text-muted);
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }
        
        /* Who It's For Cards */
        .who-section {
            background: linear-gradient(135deg, rgba(15, 20, 25, 0.80) 0%, rgba(26, 41, 66, 0.80) 100%),
                        url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=1600&h=900&fit=crop') center/cover;
            position: relative;
        }
        
        .who-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 20, 25, 0.6);
        }
        
        .who-section .container {
            position: relative;
            z-index: 1;
        }
        
        .audience-card {
            background-color: var(--card-bg);
            background-image: 
                linear-gradient(135deg, rgba(255, 215, 0, 0.05) 0%, transparent 100%),
                radial-gradient(circle at 50% 0%, rgba(26, 41, 66, 0.3) 0%, transparent 70%);
            padding: 3rem 2rem;
            border-radius: 16px;
            text-align: center;
            transition: all 0.4s ease;
            border: 2px solid transparent;
            height: 100%;
        }
        
        .audience-card:hover {
            border-color: var(--acharya-yellow);
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(255, 215, 0, 0.2);
        }
        
        .audience-icon {
            font-size: 4rem;
            color: var(--acharya-yellow);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .audience-card:hover .audience-icon {
            transform: scale(1.15);
        }
        
        .audience-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-light);
        }
        
        .audience-text {
            color: var(--text-muted);
            line-height: 1.7;
        }
        
        /* Lead Capture */
        .lead-capture {
            background: linear-gradient(180deg, #fff, rgb(15 20 25 / 0%) 100%), url(parallax-bg.jpg) bottom / contain;
            padding: 5rem 0;
            position: relative;
        }
        
        .lead-capture .container {
            position: relative;
            z-index: 1;
        }
        
        .lead-form {
            background-color: var(--card-bg);
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(255, 215, 0, 0.1) 0%, transparent 70%);
            padding: 3rem;
            border-radius: 20px;
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid rgba(255, 215, 0, 0.2);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: var(--acharya-yellow);
            box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.15);
            color: var(--text-light);
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        .form-select option {
            background-color: var(--card-bg);
            color: var(--text-light);
        }
        
        .btn-submit {
            width: 100%;
            padding: 1.2rem;
            background-color: var(--acharya-yellow);
            color: var(--dark-bg);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
        }
        
        /* Partner Section */
        .partner-section {
            background: linear-gradient(135deg, rgba(26, 31, 46, 0.80) 0%, rgba(15, 20, 25, 0.80) 100%),
                        url('https://images.unsplash.com/photo-1556761175-b413da4baf72?w=1600&h=800&fit=crop') center/cover;
            padding: 5rem 0;
            text-align: center;
            position: relative;
        }
        
        .partner-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 20, 25, 0.8);
        }
        
        .partner-section .container {
            position: relative;
            z-index: 1;
        }
        
        /* Footer */
        .footer {
            background-color: var(--dark-bg);
            background-image: 
                repeating-linear-gradient(
                    0deg,
                    transparent,
                    transparent 2px,
                    rgba(255, 215, 0, 0.02) 2px,
                    rgba(255, 215, 0, 0.02) 4px
                );
            padding: 3rem 0 2rem;
            border-top: 1px solid rgba(255, 215, 0, 0.2);
            text-align: center;
        }
        
        .footer-text {
            color: var(--text-muted);
            margin: 0.5rem 0;
        }
        
        .footer-tagline {
            color: var(--acharya-yellow);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
            
            .cta-btn {
                padding: 0.9rem 2rem;
                font-size: 1rem;
            }
            
            .nav-btn {
                font-size: 0.9rem;
                padding: 0.5rem 1rem;
                margin: 0.25rem;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fadeInUp 0.8s ease-out;
        }
        
        /* Floating Shapes */
        .floating-shape {
            position: absolute;
            opacity: 0.1;
            pointer-events: none;
        }
        
        .shape-1 {
            top: 10%;
            left: 5%;
            width: 100px;
            height: 100px;
            border: 3px solid var(--acharya-yellow);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-2 {
            top: 60%;
            right: 10%;
            width: 80px;
            height: 80px;
            border: 3px solid var(--setu-navy);
            transform: rotate(45deg);
            animation: float 8s ease-in-out infinite;
        }
        
        .shape-3 {
            bottom: 20%;
            left: 15%;
            width: 60px;
            height: 60px;
            background: var(--acharya-yellow);
            border-radius: 50%;
            animation: float 7s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        .thankyou-card {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px 30px;
            background: linear-gradient(135deg, #e8fff4, #ffffff);
            border-radius: 18px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            animation: popIn 0.5s ease;
        }

        .thankyou-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .thankyou-card h2 {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .thankyou-card p {
            color: #555;
            font-size: 15px;
            line-height: 1.6;
        }

        .thankyou-card .extra {
            margin-top: 20px;
            font-size: 14px;
            color: #198754;
            font-weight: 600;
        }

        @keyframes popIn {
            0% {
                transform: scale(0.9);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#home" style="width: 200px;"><img src="{{ asset('frontend/images/logo.png');}}" style="width: 100%;"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex flex-wrap align-items-center">
                    <a href="#waitlist" class="nav-btn nav-btn-outline">Join Waitlist</a>
                    <a href="#mentor" class="nav-btn nav-btn-outline">Become a Mentor</a>
                    <a href="#partner" class="nav-btn nav-btn-primary">Partner With Us</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="hero-content text-center animate-fade-in">
                        <h1 class="hero-title">Mentorship that turns ambition into direction.</h1>
                        <p class="hero-subtitle mx-auto">
                            Acharya Setu is a mentorship-first platform built to help students and early professionals make smarter career choices—with structured guidance, real mentors, and measurable progress.
                        </p>
                        <div class="cta-buttons">
                            <a href="#waitlist" class="cta-btn cta-primary">
                                <i class="fas fa-rocket me-2"></i>Join the Waitlist
                            </a>
                            <a href="#mentor" class="cta-btn cta-secondary">
                                <i class="fas fa-user-tie me-2"></i>Become a Mentor
                            </a>
                        </div>
                        <div class="hero-microcopy">
                            <i class="fas fa-check-circle"></i> Early access
                            <i class="fas fa-check-circle ms-3"></i> Priority mentor slots
                            <i class="fas fa-check-circle ms-3"></i> Founding member updates
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Introduction Section -->
    <section class="section intro-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <h2 class="section-title">Why Acharya Setu Exists</h2>
                    <p class="section-text">
                        Talent is everywhere—direction isn't. Many capable people still choose the wrong roles, get stuck, and lose motivation due to lack of guidance. Acharya Setu bridges this gap by offering mentorship journeys that are structured, supportive, and built for real outcomes.
                    </p>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="fas fa-users"></i></div>
                        <h3 class="value-title">Real Mentors</h3>
                        <p class="value-text">Professionals with proven experience—not generic advice.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="fas fa-route"></i></div>
                        <h3 class="value-title">Structured Journeys</h3>
                        <p class="value-text">Clear goals, guided sessions, tasks, and accountability.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-card">
                        <div class="value-icon"><i class="fas fa-bullseye"></i></div>
                        <h3 class="value-title">Outcome Focused</h3>
                        <p class="value-text">Clarity, confidence, skills, and career readiness—tracked.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="section how-it-works-section" id="how-it-work" style="background: url('white.png') center/cover;">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title" style="color: #000;">How It Works</h2>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="step-card h-100">
                        <span class="step-number">01</span>
                        <div class="step-icon"><i class="fas fa-clipboard-list"></i></div>
                        <h3 class="step-title">Join Waitlist → <br>Tell us your goals</h3>
                        <p class="step-text">Share your aspirations and where you need guidance. We'll understand your unique journey.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="step-card h-100">
                        <span class="step-number">02</span>
                        <div class="step-icon"><i class="fas fa-handshake"></i></div>
                        <h3 class="step-title">Get Matched → Mentor + journey recommended</h3>
                        <p class="step-text">We connect you with the right mentor and personalized learning path tailored to your goals.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="step-card h-100">
                        <span class="step-number">03</span>
                        <div class="step-icon"><i class="fas fa-rocket"></i></div>
                        <h3 class="step-title">Start Your Journey → Sessions, tasks, and guidance</h3>
                        <p class="step-text">Engage in structured sessions with actionable tasks and continuous support from your mentor.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="step-card h-100">
                        <span class="step-number">04</span>
                        <div class="step-icon"><i class="fas fa-chart-line"></i></div>
                        <h3 class="step-title">Track Progress → Skills, direction, and confidence</h3>
                        <p class="step-text">Monitor your growth with measurable milestones and see your transformation unfold.</p>
                    </div>
                </div>
                <div class="col-lg-8 mb-4">
                    <div class="step-card h-100">
                        <span class="step-number">05</span>
                        <div class="step-icon"><i class="fas fa-award"></i></div>
                        <h3 class="step-title">Achieve Outcomes → Clarity, growth, and next steps</h3>
                        <p class="step-text">
                            Walk away with clear direction, real-world skills, and a confident plan for your career or next big move.
                        </p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="#waitlist" class="cta-btn cta-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Get Early Access
                </a>
            </div>
        </div>
    </section>

    <!-- Who It's For -->
    <section class="section who-section" id="mentor">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Who It's For</h2>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="audience-card">
                        <div class="audience-icon"><i class="fas fa-user-graduate"></i></div>
                        <h3 class="audience-title">Students</h3>
                        <p class="audience-text">Choose the right path early and build strong fundamentals.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="audience-card">
                        <div class="audience-icon"><i class="fas fa-briefcase"></i></div>
                        <h3 class="audience-title">Early Professionals</h3>
                        <p class="audience-text">Avoid wrong job moves and accelerate growth with clarity.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="audience-card">
                        <div class="audience-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        <h3 class="audience-title">Mentors & Experts</h3>
                        <p class="audience-text">Guide the next generation and create meaningful impact.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="#waitlist" class="cta-btn cta-secondary">
                    <i class="fas fa-user-plus me-2"></i>Become a Mentor
                </a>
            </div>
        </div>
    </section>

    <!-- Lead Capture -->
    <section class="lead-capture" id="waitlist">
        <div class="container">
            <div class="lead-form">
                <div class="text-center mb-4">
                    <h2 class="section-title">Be the first to know when we launch.</h2>
                    <p class="section-text">Drop your email or WhatsApp number—we'll share early access, onboarding, and launch updates.</p>
                </div>
                <form id="submitForm">
                    <div class="row">
                        <div class="col-md-6">
                            <input type="text" name="name" class="form-control" placeholder="Your Name *" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" placeholder="Email Address *" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <input type="tel" name="phone" class="form-control" placeholder="WhatsApp Number *" required>
                        </div>
                        <div class="col-md-6">
                            <select class="form-select" name="who" required>
                                <option value="" selected disabled>I am a... *</option>
                                <option value="student">Student</option>
                                <option value="professional">Professional</option>
                                <option value="mentor">Mentor</option>
                                <option value="institution">Institution</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">
                        <span class="btn-text"><i class="fas fa-paper-plane me-2"></i>Join the Waitlist</span>
                        <span class="loader d-none"><i class="fas fa-spinner fa-spin"></i> Please wait...</span>
                    </button>
                    <div id="formMsg" class="mt-3"></div>
                </form>

            </div>

            <div id="thankYouBox" class="thankyou-card" style="display: none;">
                <div class="icon">🎉</div>
                <h2 class="text-dark">Thanks for joining <strong>Acharya Setu</strong></h2>
                <h4 class="text-dark m-3">You're on the list! 💚</h4>
                <p>We’ll reach out soon with exclusive updates and early access.</p>

                <div class="extra">
                    🚀 Meanwhile, follow us & stay inspired
                </div>

                <a href="#how-it-work" class="btn btn-success mt-3">
                    <i class="fa fa-thumbs-up me-2"></i>See How it Works
                </a>
            </div>

        </div>
    </section>

    <!-- Partner Section -->
    <section class="partner-section" id="partner">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h2 class="section-title">Institutions & companies — bring structured mentorship to your learners or teams.</h2>
                    <p class="section-text mb-4">
                        Run mentoring programs, communities, and guided development journeys—designed for scale and measurable outcomes.
                    </p>
                    <div class="mt-4">
                        <a href="#waitlist" class="cta-btn cta-primary">
                            <i class="fas fa-handshake me-2"></i>Partner With Us
                        </a>
                        <a href="#" class="cta-btn cta-secondary">
                            <i class="fas fa-phone-alt me-2"></i>Request a Call
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <a class="navbar-brand" href="#home"><img src="{{ asset('frontend/images/logo-footer.png');}}" style="width: 300px;margin-bottom: 30px;"></a>
            <p class="footer-tagline">Learning Beyond Classroom</p>
            <p class="footer-text">© 2026 Acharya Setu. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#submitForm').on('submit', function (e) {
                e.preventDefault();

                let form = $(this);
                let btn = form.find('.btn-submit');
                let btnText = btn.find('.btn-text');
                let loader = btn.find('.loader');

                btn.prop('disabled', true);
                btnText.addClass('d-none');
                loader.removeClass('d-none');
                $('#formMsg').html('');

                $.ajax({
                    url: "submit.php", 
                    type: "POST",
                    data: form.serialize(),
                    dataType: "json",
                    success: function (res) {
                        if (res.status === 1) {
                            $('.lead-form').fadeOut(300, function () {
                                $('#thankYouBox').fadeIn(300);
                            });

                            form[0].reset();
                        } else {
                            $('#formMsg').html('<div class="alert alert-danger">'+res.message+'</div>');
                        }
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        $('#formMsg').html('<div class="alert alert-danger">Server Error</div>');
                    },

                    complete: function () {
                        btn.prop('disabled', false);
                        btnText.removeClass('d-none');
                        loader.addClass('d-none');
                    }
                });
            });
        });


        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.backgroundColor = 'rgba(15, 20, 25, 0.98)';
            } else {
                navbar.style.backgroundColor = 'rgba(15, 20, 25, 0.80)';
            }
        });
    </script>
</body>
</html>
