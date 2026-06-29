@extends('frontend.layouts.app')
@section('title', 'About AcharyaSetu')

@section('content')
<div style="padding-top:var(--nav-h);">

    {{-- Hero --}}
    <section style="padding:80px 0;background:var(--bg-2);border-bottom:1px solid var(--border);">
        <div class="container text-center">
            <div class="hero-eyebrow" style="justify-content:center;margin-bottom:20px;">🇮🇳 Made in India</div>
            <h1 style="font-size:clamp(28px,5vw,52px);font-weight:800;line-height:1.1;margin-bottom:20px;">
                Learning beyond the <span class="text-brand">classroom</span>
            </h1>
            <p style="font-size:17px;color:var(--text-2);max-width:620px;margin:0 auto 36px;line-height:1.75;">
                AcharyaSetu bridges the gap between motivated learners and world-class mentors. In India, your network determines your trajectory. We're changing that.
            </p>
            <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-xl">Find Your Mentor</a>
        </div>
    </section>

    {{-- Stats --}}
    <section style="padding:60px 0;border-bottom:1px solid var(--border);">
        <div class="container">
            <div class="grid-4" style="gap:24px;text-align:center;">
                @foreach([['2,400+','Verified Mentors'],['45,000+','Sessions Completed'],['4.9/5','Average Rating'],['32','Cities']] as [$v,$l])
                <div>
                    <div style="font-size:42px;font-weight:900;color:var(--brand);font-family:var(--font-head);margin-bottom:4px;">{{ $v }}</div>
                    <div style="font-size:13px;color:var(--text-2);">{{ $l }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Mission --}}
    <section class="section">
        <div class="container">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
                <div>
                    <div class="hero-eyebrow" style="margin-bottom:20px;">Our Mission</div>
                    <h2 style="font-size:32px;font-weight:800;line-height:1.15;margin-bottom:16px;">
                        Every student deserves a <span class="text-brand">world-class mentor</span>
                    </h2>
                    <p style="font-size:15px;color:var(--text-2);line-height:1.8;margin-bottom:16px;">
                        IIT and IIM graduates have access to elite alumni networks. Brilliant students from tier-2 and tier-3 cities don't. AcharyaSetu is the equalizer.
                    </p>
                    <p style="font-size:15px;color:var(--text-2);line-height:1.8;">
                        We connect every motivated learner with the expert guidance they deserve — on-demand, affordable, and at transparent pricing. No subscriptions. No guesswork.
                    </p>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    @foreach([['🌏','Accessible','Mentors from every domain, at every price point'],['🔒','Verified','Every mentor manually vetted before joining'],['💰','Transparent','Pay-per-minute. No hidden fees. Ever.'],['📊','Trackable','Progress dashboards, session notes, and milestones']] as [$i,$t,$d])
                    <div class="feature-card">
                        <div class="feature-icon">{{ $i }}</div>
                        <h3>{{ $t }}</h3>
                        <p>{{ $d }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Team --}}
    <section class="section" style="background:var(--bg-2);">
        <div class="container">
            <div class="section-head">
                <h2>The team behind <span class="text-brand">AcharyaSetu</span></h2>
                <p>Built by people who've been mentored — and who mentor.</p>
            </div>
            <div class="grid-4" style="gap:20px;">
                @foreach([['V','Vikram Nair','Co-Founder & CEO','Ex-Google PM · IIT Bombay'],['A','Ananya Gupta','Co-Founder & CPO','Ex-McKinsey · ISB Hyderabad'],['R','Rohan Sethi','CTO','Ex-Flipkart SDE · IIT Delhi'],['P','Pooja Iyer','Head of Community','Ex-LinkedIn · XLRI Jamshedpur']] as [$i,$n,$r,$d])
                <div class="card text-center">
                    <div style="width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,var(--brand),var(--brand-dark));display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#000;margin:0 auto 14px;font-family:var(--font-head);">{{ $i }}</div>
                    <div style="font-size:15px;font-weight:700;margin-bottom:2px;">{{ $n }}</div>
                    <div style="font-size:12px;color:var(--brand);font-weight:600;margin-bottom:2px;">{{ $r }}</div>
                    <div style="font-size:11px;color:var(--text-3);">{{ $d }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="cta-section">
        <div class="container text-center">
            <h2 style="font-size:32px;font-weight:800;margin-bottom:12px;">Join the AcharyaSetu community</h2>
            <p style="color:var(--text-2);margin-bottom:28px;">45,000+ learners trust us. Your career deserves the same attention.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('register') }}" class="btn btn-primary btn-xl">Get Started Free</a>
                <a href="{{ route('register') }}?role=mentor" class="btn btn-outline btn-xl">Become a Mentor</a>
            </div>
        </div>
    </section>
</div>
@endsection