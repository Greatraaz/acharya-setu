@extends('frontend.layouts.app')
@section('title', 'Set Up Your Profile — Step ' . $step . ' of 4 — AcharyaSetu')

@section('content')
<div style="min-height:100vh; padding:calc(var(--nav-h) + 40px) 16px 60px; background:var(--bg);">
<div style="max-width:620px; margin:0 auto;">

    {{-- ── HEADER ────────────────────────────────────────── --}}
    <div class="text-center" style="margin-bottom:32px;">
        <img src="{{ asset('images/logo.png') }}" alt="AcharyaSetu" style="height:38px; margin:0 auto 14px;">
        @if($step < 4)
        <h1 style="font-size:22px; font-weight:800; margin-bottom:4px;">Set Up Your Profile</h1>
        <p style="font-size:13px; color:var(--text-2);">Step {{ $step }} of 4 — Takes less than 3 minutes</p>
        @else
        <h1 style="font-size:24px; font-weight:800; margin-bottom:4px;">You're all set! 🎉</h1>
        @endif
    </div>

    {{-- ── STEP PROGRESS BAR ──────────────────────────────── --}}
    @php $stepLabels = ['About You', 'Education', 'Your Goals', 'Ready!']; @endphp
    <div style="display:flex; align-items:center; margin-bottom:36px;">
        @foreach($stepLabels as $i => $label)
        @php $num = $i + 1; $isDone = $num < $step; $isCurrent = $num === $step; @endphp
        <div style="display:flex; flex-direction:column; align-items:center; flex:1;">
            <div style="
                width:36px; height:36px; border-radius:50%;
                display:flex; align-items:center; justify-content:center;
                font-size:13px; font-weight:700; font-family:var(--font-head);
                background: {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--brand)' : 'var(--bg-4)') }};
                color: {{ ($isDone || $isCurrent) ? '#000' : 'var(--text-3)' }};
                border:2px solid {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--brand)' : 'var(--border)') }};
            ">{{ $isDone ? '✓' : $num }}</div>
            <div style="font-size:10px; font-weight:600; margin-top:5px; color:{{ $isCurrent ? 'var(--brand)' : 'var(--text-3)' }}; white-space:nowrap;">{{ $label }}</div>
        </div>
        @if($i < 3)
        <div style="height:2px; flex:1; background:{{ $num < $step ? 'var(--success)' : 'var(--border)' }}; margin-bottom:20px;"></div>
        @endif
        @endforeach
    </div>

    {{-- ── CARD ───────────────────────────────────────────── --}}
    <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:var(--radius-xl); padding:36px;">

        {{-- ════════════════════════════════════════════════
             STEP 1 — About You
             ════════════════════════════════════════════════ --}}
        @if($step == 1)
        <h2 style="font-size:19px; font-weight:800; margin-bottom:4px;">Tell us about yourself</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">We'll use this to personalise your mentor recommendations.</p>

        <form
            action="{{ route('mentee.onboarding.save1') }}"
            method="POST"
            data-ajax-form="{{ route('mentee.onboarding.save1') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 2]) }}"
            data-success="Saved!"
        >
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group" style="grid-column:1/-1;">
                    <label class="form-label">Your Full Name *</label>
                    <input type="text" name="name" class="form-input" required
                           value="{{ old('name', auth()->user()->name) }}"
                           placeholder="Rahul Sharma">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Prefer not to say</option>
                        <option value="male"   @selected(old('gender', auth()->user()->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', auth()->user()->gender) === 'female')>Female</option>
                        <option value="other"  @selected(old('gender', auth()->user()->gender) === 'other')>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <div class="input-prefix">
                        <span class="input-prefix-label">🇮🇳 +91</span>
                        <input type="tel" name="phone" class="form-input" placeholder="98765 43210" maxlength="10"
                               value="{{ old('phone', ltrim(auth()->user()->phone ?? '', '+91')) }}">
                    </div>
                </div>
            </div>

            {{-- User type --}}
            <div class="form-group">
                <label class="form-label">I best describe myself as *</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                    @foreach([
                        ['🎒','10th–12th Student'],
                        ['🎓','Undergraduate (College)'],
                        ['📚','Postgraduate / MBA Student'],
                        ['💼','Working Professional (1–5 yrs)'],
                        ['🏢','Working Professional (5+ yrs)'],
                        ['🔄','Career Changer'],
                        ['💡','Startup Founder'],
                        ['📋','Job Seeker / Fresher'],
                    ] as [$icon, $type])
                    <label id="type-{{ Str::slug($type) }}"
                           style="display:flex; align-items:center; gap:10px; padding:11px 14px; border:1.5px solid var(--border); border-radius:var(--radius); cursor:pointer; font-size:13px; transition:all .2s;"
                           onclick="styleTypeCard(this)">
                        <input type="radio" name="user_type" value="{{ $type }}" style="accent-color:var(--brand);"
                               @if(old('user_type') === $type) checked @endif>
                        <span>{{ $icon }}&nbsp; {{ $type }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Continue →</button>

            <p style="text-align:center; margin-top:14px; font-size:13px; color:var(--text-2);">
                Already have an account? <a href="{{ route('login') }}" style="color:var(--brand); font-weight:600;">Sign in</a>
            </p>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 2 — Education
             ════════════════════════════════════════════════ --}}
        @elseif($step == 2)
        <h2 style="font-size:19px; font-weight:800; margin-bottom:4px;">Your Education</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">Helps us match you with mentors in your career stream.</p>

        <form
            action="{{ route('mentee.onboarding.save2') }}"
            method="POST"
            data-ajax-form="{{ route('mentee.onboarding.save2') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 3]) }}"
            data-success="Saved!"
        >
            @csrf
            <input type="hidden" name="education_stream" id="stream-hidden" value="{{ old('education_stream', auth()->user()->education_stream) }}">

            {{-- Career stream selector --}}
            <div class="form-group">
                <label class="form-label">Career Stream *</label>
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px; margin-top:8px;">
                    @forelse($streams as $stream)
                    <div
                        class="stream-option"
                        data-stream="{{ $stream->name }}"
                        onclick="selectStream(this)"
                        style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                               padding:20px 12px; border:2px solid var(--border); border-radius:var(--radius-lg);
                               cursor:pointer; text-align:center; transition:all .2s;
                               {{ old('education_stream', auth()->user()->education_stream) === $stream->name ? 'border-color:var(--brand); background:var(--brand-muted);' : '' }}"
                    >
                        <span style="font-size:36px; margin-bottom:8px;">{{ $stream->icon ?? '📚' }}</span>
                        <span style="font-size:13px; font-weight:700; line-height:1.3;">{{ $stream->name }}</span>
                        @if($stream->description)
                        <span style="font-size:10px; color:var(--text-3); margin-top:4px; line-height:1.4;">{{ Str::limit($stream->description, 40) }}</span>
                        @endif
                    </div>
                    @empty
                    @foreach([
                        ['🖥️','Engineering','Computer Science, Mechanical, Civil'],
                        ['💼','Commerce','Finance, Accounting, Marketing'],
                        ['🎨','Arts','Psychology, Sociology, Literature'],
                        ['🏥','Medicine','MBBS, NEET, Pharmacy'],
                        ['⚖️','Law','LLB, Corporate Law, IP'],
                        ['📢','Marketing','Digital, Brand, Growth'],
                    ] as [$icon, $name, $desc])
                    <div
                        class="stream-option"
                        data-stream="{{ $name }}"
                        onclick="selectStream(this)"
                        style="display:flex; flex-direction:column; align-items:center; justify-content:center;
                               padding:20px 12px; border:2px solid var(--border); border-radius:var(--radius-lg);
                               cursor:pointer; text-align:center; transition:all .2s;"
                    >
                        <span style="font-size:36px; margin-bottom:8px;">{{ $icon }}</span>
                        <span style="font-size:13px; font-weight:700;">{{ $name }}</span>
                        <span style="font-size:10px; color:var(--text-3); margin-top:4px; line-height:1.4;">{{ $desc }}</span>
                    </div>
                    @endforeach
                    @endforelse
                </div>
                <div id="stream-error" class="form-error" style="display:none; margin-top:8px;">Please select a career stream.</div>
            </div>

            {{-- College & Year --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">College / University</label>
                    <input type="text" name="college" class="form-input"
                           placeholder="IIT Bombay, DU, Amity…"
                           value="{{ old('college', auth()->user()->college) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Graduation Year / Batch</label>
                    <input type="text" name="year" class="form-input"
                           placeholder="2025 / Final Year / 2021–24"
                           value="{{ old('year', auth()->user()->year) }}">
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <a href="{{ route('mentee.onboarding', ['step' => 1]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;" onclick="return validateStream()">Continue →</button>
            </div>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 3 — Career Goals
             ════════════════════════════════════════════════ --}}
        @elseif($step == 3)
        <h2 style="font-size:19px; font-weight:800; margin-bottom:4px;">Your Career Goals</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">What do you want to achieve? We'll match you with the best-fit mentors.</p>

        <form
            action="{{ route('mentee.onboarding.save3') }}"
            method="POST"
            data-ajax-form="{{ route('mentee.onboarding.save3') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 4]) }}"
            data-success="Goals saved!"
        >
            @csrf

            <div class="form-group">
                <label class="form-label">I want help with… <span style="font-weight:400; color:var(--text-3);">(select all that apply)</span></label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                    @foreach([
                        ['💻','Cracking FAANG / Tech Interviews'],
                        ['🎓','MBA Admissions (CAT / GMAT / GRE)'],
                        ['🔄','Career Switch to a New Field'],
                        ['💼','Getting My First Job'],
                        ['💡','Startup Advice & Entrepreneurship'],
                        ['🏆','Improving Leadership Skills'],
                        ['📋','UPSC / Government Exam Preparation'],
                        ['✈️','Study Abroad / Masters Abroad'],
                        ['💰','Salary Negotiation & Job Offers'],
                        ['🎨','Freelancing & Consulting'],
                        ['📈','Building Specific Technical Skills'],
                        ['🗂️','General Career Planning & Direction'],
                    ] as [$icon, $goal])
                    <label style="display:flex; align-items:center; gap:10px; padding:11px 14px; border:1.5px solid var(--border); border-radius:var(--radius); cursor:pointer; font-size:13px; transition:all .2s;"
                           onclick="this.style.borderColor = this.querySelector('input').checked ? 'var(--brand)' : 'var(--border)'; this.style.background = this.querySelector('input').checked ? 'var(--brand-muted)' : 'var(--card-bg)'">
                        <input type="checkbox" name="career_goals[]" value="{{ $goal }}" style="accent-color:var(--brand);"
                               @if(in_array($goal, auth()->user()->career_goals ?? [])) checked @endif>
                        <span>{{ $icon }}&nbsp; {{ $goal }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Short message to mentor --}}
            <div class="form-group">
                <label class="form-label">Anything else you'd like your mentor to know? <span style="font-weight:400; color:var(--text-3);">(Optional)</span></label>
                <textarea name="intro_message" class="form-textarea" rows="3"
                          placeholder="e.g. I'm preparing for FAANG interviews in 3 months and need structured DSA practice…"
                          maxlength="500">{{ old('intro_message') }}</textarea>
                <div class="form-hint">This will be visible to mentors when you book a session.</div>
            </div>

            <div style="display:flex; gap:12px;">
                <a href="{{ route('mentee.onboarding', ['step' => 2]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;" onclick="return validateGoals()">Continue →</button>
            </div>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 4 — Complete / Welcome Screen
             ════════════════════════════════════════════════ --}}
        @elseif($step == 4)
        <div class="text-center">
            <div style="font-size:72px; margin-bottom:16px;">🚀</div>
            <h2 style="font-size:24px; font-weight:800; margin-bottom:10px;">You're ready to learn!</h2>
            <p style="font-size:14px; color:var(--text-2); line-height:1.75; max-width:440px; margin:0 auto 32px;">
                Your profile is set up. Start exploring mentors, book your first session, and kick off your learning journey.
            </p>

            {{-- What's next --}}
            <div style="background:var(--bg-3); border:1px solid var(--border); border-radius:var(--radius-lg); padding:24px; text-align:left; margin-bottom:28px;">
                <div style="font-size:13px; font-weight:700; margin-bottom:16px; text-align:center;">What to do next</div>
                @foreach([
                    ['🔍','Find a Mentor','Browse and filter 2,400+ verified mentors in your domain.',                route('mentors.search'),'Find Mentors'],
                    ['💰','Add Wallet Balance','Top up your wallet to pay for sessions. Rates from ₹5/min.',        route('mentee.wallet'),'Add Money'],
                    ['📅','Book Your First Session','Choose a date, time, and duration that works for you.',         route('mentors.search'),'Book a Session'],
                    ['🗺️','Start Your Journey','Enrol in a 6-month structured learning path with milestones.',       route('mentee.journey.index'),'My Journey'],
                ] as [$icon, $title, $desc, $link, $cta])
                <div style="display:flex; gap:14px; align-items:flex-start; padding:14px 0; border-bottom:1px solid var(--border);">
                    <div style="width:40px; height:40px; border-radius:var(--radius-sm); background:var(--brand-muted); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">{{ $icon }}</div>
                    <div style="flex:1;">
                        <div style="font-size:13px; font-weight:700; margin-bottom:2px;">{{ $title }}</div>
                        <div style="font-size:12px; color:var(--text-2);">{{ $desc }}</div>
                    </div>
                    <a href="{{ $link }}" class="btn btn-outline btn-sm" style="flex-shrink:0; white-space:nowrap;">{{ $cta }}</a>
                </div>
                @endforeach
            </div>

            {{-- Submit --}}
            <form action="{{ route('mentee.onboarding.complete') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-xl btn-full" style="font-size:16px;">
                    🚀 Go to My Dashboard
                </button>
            </form>
        </div>
        @endif

    </div>{{-- /card --}}
</div>
</div>
@endsection

@push('scripts')
<script>
// ── User type card styling ────────────────────────────────────
function styleTypeCard(label) {
    document.querySelectorAll('label[id^="type-"]').forEach(l => {
        l.style.borderColor = 'var(--border)';
        l.style.background  = 'var(--card-bg)';
    });
    const inp = label.querySelector('input');
    if (inp && inp.checked) {
        label.style.borderColor = 'var(--brand)';
        label.style.background  = 'var(--brand-muted)';
    }
}

// ── Stream selection ──────────────────────────────────────────
function selectStream(card) {
    document.querySelectorAll('.stream-option').forEach(c => {
        c.style.borderColor = 'var(--border)';
        c.style.background  = 'var(--card-bg)';
    });
    card.style.borderColor = 'var(--brand)';
    card.style.background  = 'var(--brand-muted)';
    const hidden = document.getElementById('stream-hidden');
    if (hidden) hidden.value = card.dataset.stream;
    const errEl = document.getElementById('stream-error');
    if (errEl) errEl.style.display = 'none';
}

function validateStream() {
    const val = document.getElementById('stream-hidden')?.value;
    if (!val) {
        const errEl = document.getElementById('stream-error');
        if (errEl) errEl.style.display = 'block';
        showToast('error', 'Please select a career stream.');
        return false;
    }
    return true;
}

// ── Goals validation ──────────────────────────────────────────
function validateGoals() {
    const checked = document.querySelectorAll('input[name="career_goals[]"]:checked').length;
    if (checked === 0) {
        showToast('error', 'Please select at least one goal.');
        return false;
    }
    return true;
}

// ── Restore checkbox / radio border styles on load ────────────
document.addEventListener('DOMContentLoaded', () => {
    // Pre-select stream on page load (if old value exists)
    const streamVal = document.getElementById('stream-hidden')?.value;
    if (streamVal) {
        document.querySelectorAll('.stream-option').forEach(c => {
            if (c.dataset.stream === streamVal) {
                c.style.borderColor = 'var(--brand)';
                c.style.background  = 'var(--brand-muted)';
            }
        });
    }

    // Restore checkbox label styles
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(inp => {
        const label = inp.closest('label');
        if (label) {
            label.style.borderColor = 'var(--brand)';
            label.style.background  = 'var(--brand-muted)';
        }
    });

    // Restore radio label styles
    document.querySelectorAll('input[type="radio"]:checked').forEach(inp => {
        const label = inp.closest('label');
        if (label) {
            label.style.borderColor = 'var(--brand)';
            label.style.background  = 'var(--brand-muted)';
        }
    });
});
</script>
@endpush