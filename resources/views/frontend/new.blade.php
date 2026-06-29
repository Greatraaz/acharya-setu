@extends('frontend.layouts.app')

@section('title', 'AcharyaSetu — Connect with World-Class Mentors')

@section('content')

{{-- ═══════════════════════════════════════════════════════ HERO --}}
<section class="hero">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-grid" style="align-items:center; padding:80px 0;">
            {{-- Left --}}
            <div>
                <div class="hero-eyebrow">
                    🎓 India's #1 Career Mentorship Platform
                </div>
                <h1 class="hero-title">
                    Learn from the<br><span class="accent">Best in the Field</span><br>Grow Faster
                </h1>
                <p class="hero-desc">
                    Book 1-on-1 sessions with verified mentors from Google, Amazon, McKinsey & more.
                    Get personalized guidance on career, skills, and college — at transparent per-minute pricing.
                </p>
                <div class="hero-cta">
                    <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-xl">
                        🔍 Find Your Mentor
                    </a>
                    <a href="{{ route('register') }}?role=mentor" class="btn btn-outline btn-xl">
                        Become a Mentor →
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-value">2,400+</span>
                        <span class="stat-label">Verified Mentors</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">45K+</span>
                        <span class="stat-label">Sessions Done</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">4.9★</span>
                        <span class="stat-label">Avg. Rating</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value">32</span>
                        <span class="stat-label">Cities</span>
                    </div>
                </div>
            </div>

            {{-- Right – Banner Slider --}}
            <div>
                <div class="banner-wrap">
                    {{-- Slide 1 --}}
                    <div class="banner-slide active">
                        <div class="banner-slide-bg" style="background:linear-gradient(135deg,rgba(245,158,11,.12) 0%,var(--bg-3) 100%)"></div>
                        <div class="banner-slide-content">
                            <div class="banner-slide-icon">🧭</div>
                            <h3>Career Direction</h3>
                            <p>Lost on which path to take? Our mentors help you map the right career trajectory based on your strengths and market demand.</p>
                            <a href="{{ route('mentors.search') }}?domain=career" class="btn btn-primary btn-sm" style="margin-top:16px;">Find Career Mentors</a>
                        </div>
                    </div>
                    {{-- Slide 2 --}}
                    <div class="banner-slide">
                        <div class="banner-slide-bg" style="background:linear-gradient(135deg,rgba(59,130,246,.12) 0%,var(--bg-3) 100%)"></div>
                        <div class="banner-slide-content">
                            <div class="banner-slide-icon">💻</div>
                            <h3>Technical Skills</h3>
                            <p>DSA, System Design, FAANG prep, Cloud, ML — get hands-on mentoring from engineers who've cracked the interviews.</p>
                            <a href="{{ route('mentors.search') }}?domain=engineering" class="btn btn-primary btn-sm" style="margin-top:16px;">Find Tech Mentors</a>
                        </div>
                    </div>
                    {{-- Slide 3 --}}
                    <div class="banner-slide">
                        <div class="banner-slide-bg" style="background:linear-gradient(135deg,rgba(34,197,94,.12) 0%,var(--bg-3) 100%)"></div>
                        <div class="banner-slide-content">
                            <div class="banner-slide-icon">📈</div>
                            <h3>MBA & Finance</h3>
                            <p>CAT/GMAT prep, MBA admissions, investment banking, startup finance — mentors from IIMs, ISB and top global B-schools.</p>
                            <a href="{{ route('mentors.search') }}?domain=finance" class="btn btn-primary btn-sm" style="margin-top:16px;">Find Business Mentors</a>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <button class="banner-prev">‹</button>
                    <button class="banner-next">›</button>
                    <div class="banner-dots">
                        <div class="banner-dot active"></div>
                        <div class="banner-dot"></div>
                        <div class="banner-dot"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Advance Search Bar --}}
        <div class="search-hero" style="max-width:780px;margin:0 auto 80px;">
            <span style="font-size:18px;">🔍</span>
            <input type="text" id="hero-search" placeholder="Search by name, skill, domain (e.g. Product Manager, DSA, Finance)…"
                   onkeydown="if(event.key==='Enter') heroSearch()">
            <div class="search-filters">
                <select id="hero-domain" style="background:var(--bg-4);border:1px solid var(--border);border-radius:var(--radius-sm);padding:8px 10px;color:var(--text-2);font-size:12px;cursor:pointer;">
                    <option value="">Any Domain</option>
                    <option>Engineering</option>
                    <option>Finance</option>
                    <option>Design</option>
                    <option>Product</option>
                    <option>Marketing</option>
                    <option>Law</option>
                    <option>Medicine</option>
                </select>
                <button class="btn btn-primary" onclick="heroSearch()">Search</button>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ TRUSTED BY --}}
<section class="section-sm" style="border-top:1px solid var(--border); border-bottom:1px solid var(--border); background:var(--bg-2);">
    <div class="container">
        <p class="label-caps text-center" style="margin-bottom:24px;">Mentors from India's top companies</p>
        <div style="display:flex;align-items:center;justify-content:center;gap:48px;flex-wrap:wrap;opacity:.7;">
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">Google</span>
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">Amazon</span>
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">Microsoft</span>
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">McKinsey</span>
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">Flipkart</span>
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">Razorpay</span>
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">IIM</span>
            <span style="font-size:15px;font-weight:700;font-family:var(--font-head);">IIT</span>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ FEATURES --}}
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Everything you need to <span class="text-brand">grow your career</span></h2>
            <p>AcharyaSetu combines expert mentorship, structured learning, and community support in one platform.</p>
        </div>
        <div class="grid-3" style="gap:20px;">
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Verified Mentors</h3>
                <p>Every mentor is manually reviewed. Real professionals with proven track records — we personally verify credentials and experience.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏱️</div>
                <h3>Pay-Per-Minute</h3>
                <p>Only pay for the time you use. No subscriptions, no hidden fees. Rates from ₹5/min. Cancel sessions free up to 2 hours before.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Flexible Scheduling</h3>
                <p>Book sessions that fit your schedule. Real-time availability, instant confirmation, and video/audio/chat options.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🗺️</div>
                <h3>6-Month Journey</h3>
                <p>Structured curriculum with weekly milestones, MCQs, tasks, and check-ins. Track your progress with your mentor throughout.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>Community Channels</h3>
                <p>Connect with peers in dedicated communities. Share resources, get feedback, and learn from others on the same path.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3>Secure Payments</h3>
                <p>Razorpay-powered wallet with bank-grade encryption. Your payments are protected. Refund policy for cancelled sessions.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ HOW IT WORKS --}}
<section class="section" style="background:var(--bg-2);">
    <div class="container">
        <div class="section-head">
            <h2>How AcharyaSetu Works</h2>
            <p>Getting started takes less than 5 minutes.</p>
        </div>
        <div class="grid-4" style="gap:16px;">
                @php
                    $testimonials = [
                          ['01', '🔍', 'Discover', 'Browse 2400+ verified mentors. Filter by domain, experience, price, and availability.'],
                          ['02', '📅', 'Book', 'Choose a date & time that works for you. Instant confirmation. Pay only for the session.'],
                          ['03', '🎥', 'Connect', 'Meet via video call, audio, or chat. Get personalized, 1-on-1 guidance.'],
                          ['04', '📈', 'Grow', 'Complete action items, track progress, repeat — and watch your career accelerate.'],
                      ];
                @endphp
            @foreach($testimonials as $stepData)
                @php
                    [$step, $icon, $title, $desc] = $stepData;
                @endphp
                <div class="card text-center" style="position:relative;">
                    <div style="font-size:11px;font-weight:800;color:var(--brand);letter-spacing:.1em;margin-bottom:12px;">STEP {{ $step }}</div>
                    <div style="font-size:48px;margin-bottom:16px;">{{ $icon }}</div>
                    <h3 style="font-size:17px;margin-bottom:8px;">{{ $title }}</h3>
                    <p style="font-size:13px;color:var(--text-2);line-height:1.7;">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
   
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ FEATURED MENTORS --}}
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Meet Some of Our <span class="text-brand">Top Mentors</span></h2>
            <p>Handpicked professionals ready to guide your growth.</p>
        </div>
        <div class="grid-4" style="gap:20px;">
            @forelse($featuredMentors ?? [] as $mentor)
            <div class="mentor-card" onclick="window.location='/mentors/{{ $mentor->id }}'">
                <div class="mentor-card-head">
                    <div class="mentor-avatar-lg">
                        @if($mentor->avatar_url)<img src="{{ $mentor->avatar_url }}" alt="{{ $mentor->name }}">
                        @else {{ strtoupper(substr($mentor->name, 0, 1)) }} @endif
                    </div>
                    <div class="mentor-card-info">
                        <div class="mentor-card-name">{{ $mentor->name }}</div>
                        <div class="mentor-card-role">{{ $mentor->designation }} · {{ $mentor->company }}</div>
                    </div>
                </div>
                <div class="mentor-card-bio">{{ Str::limit($mentor->bio, 80) }}</div>
                <div class="mentor-tags">
                    @foreach(array_slice($mentor->expertise ?? [], 0, 3) as $tag)
                    <span class="tag">{{ $tag }}</span>
                    @endforeach
                </div>
                <div class="mentor-card-meta">
                    <span class="mentor-rate">₹{{ $mentor->rate_per_minute }}/min</span>
                    <span class="mentor-rating">⭐ {{ number_format($mentor->rating, 1) }}</span>
                </div>
                <div class="mentor-card-actions" onclick="event.stopPropagation()">
                    <a href="/mentors/{{ $mentor->id }}" class="btn btn-outline btn-sm">View</a>
                    <a href="/mentors/{{ $mentor->id }}#book" class="btn btn-primary btn-sm">Book</a>
                </div>
            </div>
            @empty
            {{-- Placeholder cards for preview --}}
            @foreach([
                ['R','Rohit Sharma','Senior PM · Google','Product strategy, 0→1, OKRs, FAANG interviews','₹12/min','4.9','167'],
                ['P','Priya Nair','SDE-2 · Microsoft','DSA, System Design, FAANG prep, C++, Python','₹15/min','4.8','203'],
                ['A','Ananya Gupta','Consultant · McKinsey','MBA prep, case interviews, startup strategy','₹10/min','5.0','89'],
                ['V','Vikram Menon','Director · Amazon','Leadership, Executive presence, P&L management','₹20/min','4.9','312'],
            ] as [$initial, $name, $role, $bio, $rate, $rating, $sessions])
            <div class="mentor-card">
                <div class="mentor-card-head">
                    <div class="mentor-avatar-lg">{{ $initial }}</div>
                    <div class="mentor-card-info">
                        <div class="mentor-card-name">{{ $name }}</div>
                        <div class="mentor-card-role">{{ $role }}</div>
                    </div>
                </div>
                <div class="mentor-card-bio">{{ $bio }}</div>
                <div class="mentor-tags">
                    <span class="tag">{{ explode(',', $bio)[0] }}</span>
                </div>
                <div class="mentor-card-meta">
                    <span class="mentor-rate">{{ $rate }}</span>
                    <span class="mentor-rating">⭐ {{ $rating }} ({{ $sessions }})</span>
                </div>
                <div class="mentor-card-actions">
                    <a href="{{ route('mentors.search') }}" class="btn btn-outline btn-sm">View</a>
                    <a href="{{ route('mentors.search') }}" class="btn btn-primary btn-sm">Book</a>
                </div>
            </div>
            @endforeach
            @endforelse
        </div>
        <div class="text-center" style="margin-top:36px;">
            <a href="{{ route('mentors.search') }}" class="btn btn-outline btn-lg">Browse All Mentors →</a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ STREAMS --}}
<section class="section" style="background:var(--bg-2);">
    <div class="container">
        <div class="section-head">
            <h2>Explore by <span class="text-brand">Career Domain</span></h2>
            <p>Find mentors across every field — engineering, commerce, arts, and more.</p>
        </div>
        <div class="grid-3" style="gap:16px;">
            @php
            $domains = [
                ['🖥️','Engineering','Computer Science, Mechanical, Civil, Electronics'],
                ['💼','Commerce & Finance','CA, MBA, Investment Banking, Accounting'],
                ['🎨','Arts & Design','UX/UI, Graphic Design, Psychology, Media'],
                ['⚖️','Law','Legal Research, Litigation, Corporate Law, IP'],
                ['🏥','Medicine & Health','MBBS, NEET prep, Public Health, Pharmacy'],
                ['📢','Marketing & Sales','Digital Marketing, Brand, Growth, SEO'],
            ];
            @endphp
            @foreach($domains as [$icon, $name, $sub])
            <a href="{{ route('mentors.search') }}?domain={{ urlencode($name) }}" class="card" style="display:flex;gap:16px;align-items:center;text-decoration:none;cursor:pointer;">
                <div style="font-size:36px;flex-shrink:0;">{{ $icon }}</div>
                <div>
                    <div style="font-size:15px;font-weight:700;margin-bottom:3px;">{{ $name }}</div>
                    <div style="font-size:12px;color:var(--text-2);">{{ $sub }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ TESTIMONIALS --}}
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>What Our Community <span class="text-brand">Says</span></h2>
            <p>Real results from real learners. See what our mentees have achieved.</p>
        </div>
        <div class="grid-3" style="gap:20px;">
            @foreach([
                ['K','Karan Joshi','Product Manager, Razorpay','5','3 sessions with Rohit changed my entire PM mindset. Got the Razorpay offer 6 weeks later. The value I got was 100x what I paid.'],
                ['M','Meera Krishnan','SDE-2, Meta','5','Priya&apos;s DSA mentoring was unlike anything on YouTube. She caught my blind spots instantly. Cracked FAANG in 4 months — couldn&apos;t have done it without her.'],
                ['S','Sanya Kapoor','MBA Student, IIM-A','5','The pay-per-minute model is genius. I could get targeted help on exactly what I needed without a bloated subscription. Ananya is amazing.']
            ] as [$initial, $name, $role, $rating, $text])
            <div class="testimonial-card">
                <div class="stars">{{ str_repeat('★', $rating) }}</div>
                <p class="testimonial-text">"{{ $text }}"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">{{ $initial }}</div>
                    <div>
                        <div class="author-name">{{ $name }}</div>
                        <div class="author-role">{{ $role }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
   
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ CTA --}}
<section class="cta-section">
    <div class="container">
        <div style="max-width:600px;margin:0 auto;">
            <h2 style="font-size:clamp(26px,4vw,42px);font-weight:800;margin-bottom:16px;">
                Ready to <span class="text-brand">accelerate</span> your career?
            </h2>
            <p style="font-size:16px;color:var(--text-2);margin-bottom:32px;line-height:1.7;">
                Join 45,000+ learners who are growing with AcharyaSetu. Start for free — no subscription required.
            </p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
                <a href="{{ route('register') }}" class="btn btn-primary btn-xl">Create Free Account</a>
                <a href="{{ route('mentors.search') }}" class="btn btn-outline btn-xl">Browse Mentors</a>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
function heroSearch() {
    const q = document.getElementById('hero-search').value.trim();
    const domain = document.getElementById('hero-domain').value;
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (domain) params.set('domain', domain);
    window.location.href = '{{ route("mentors.search") }}?' + params.toString();
}
document.getElementById('hero-search')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') heroSearch();
});
</script>
@endpush