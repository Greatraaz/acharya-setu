@extends('frontend.layouts.app')
@section('title', 'Become a Mentor — Step ' . $step . ' of 5 — AcharyaSetu')

@section('content')
<div style="min-height:100vh; padding:calc(var(--nav-h) + 40px) 16px 60px; background:var(--bg);">
<div style="max-width:700px; margin:0 auto;">

    {{-- ── HEADER ────────────────────────────────────────── --}}
    <div class="text-center" style="margin-bottom:32px;">
        <img src="{{ asset('images/logo.png') }}" alt="AcharyaSetu" style="height:38px; margin:0 auto 14px;">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:4px;">Become a Mentor</h1>
        <p style="font-size:13px; color:var(--text-2);">Step {{ $step }} of 5 — Complete your profile to start mentoring</p>
    </div>

    {{-- ── STEP PROGRESS BAR ──────────────────────────────── --}}
    @php
        $stepLabels = ['Basic Info','Professional','Expertise','Preferences','Review'];
    @endphp
    <div style="display:flex; align-items:center; margin-bottom:36px; overflow-x:auto; padding-bottom:4px;">
        @foreach($stepLabels as $i => $label)
        @php $num = $i + 1; $isDone = $num < $step; $isCurrent = $num === $step; @endphp
        <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0; min-width:80px;">
            <div style="
                width:38px; height:38px; border-radius:50%;
                display:flex; align-items:center; justify-content:center;
                font-size:13px; font-weight:700; font-family:var(--font-head);
                background: {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--brand)' : 'var(--bg-4)') }};
                color: {{ $isDone || $isCurrent ? '#000' : 'var(--text-3)' }};
                border:2px solid {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--brand)' : 'var(--border)') }};
                transition:all .3s;
            ">{{ $isDone ? '✓' : $num }}</div>
            <div style="font-size:10px; font-weight:600; margin-top:6px; color:{{ $isCurrent ? 'var(--brand)' : 'var(--text-3)' }}; white-space:nowrap;">{{ $label }}</div>
        </div>
        @if($i < 4)
        <div style="flex:1; height:2px; background:{{ $num < $step ? 'var(--success)' : 'var(--border)' }}; margin:0 4px; margin-bottom:22px; min-width:20px;"></div>
        @endif
        @endforeach
    </div>

    {{-- ── CARD ───────────────────────────────────────────── --}}
    <div style="background:var(--card-bg); border:1px solid var(--border); border-radius:var(--radius-xl); padding:36px;">

        {{-- ════════════════════════════════════════════════
             STEP 1 — Basic Info & Photo
             ════════════════════════════════════════════════ --}}
        @if($step == 1)
        <h2 style="font-size:20px; font-weight:800; margin-bottom:4px;">Basic Information</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">Your name, photo, and a compelling bio are the first things mentees see.</p>

        <form
            action="{{ route('mentor.onboarding.save1') }}"
            method="POST"
            enctype="multipart/form-data"
            data-ajax-form="{{ route('mentor.onboarding.save1') }}"
            data-redirect="{{ route('mentor.onboarding', ['step' => 2]) }}"
            data-success="Step 1 saved!"
        >
            @csrf

            {{-- Avatar + Name row --}}
            <div style="display:flex; gap:24px; align-items:flex-start; margin-bottom:8px;">
                {{-- Avatar --}}
                <div style="flex-shrink:0; text-align:center;">
                    <div
                        id="avatar-preview"
                        onclick="document.getElementById('avatar-input').click()"
                        style="width:88px; height:88px; border-radius:18px;
                               background:var(--brand-muted); border:2px dashed var(--brand);
                               display:flex; align-items:center; justify-content:center;
                               font-size:32px; font-weight:800; color:var(--brand);
                               cursor:pointer; overflow:hidden; font-family:var(--font-head);"
                    >
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display:none;"
                           onchange="previewImage(this, '#avatar-preview')">
                    <div style="font-size:10px; color:var(--text-3); margin-top:6px; line-height:1.4;">Click to<br>upload photo</div>
                </div>

                {{-- Name + Gender --}}
                <div style="flex:1;">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-input" required
                               value="{{ old('name', auth()->user()->name) }}"
                               placeholder="Rohit Sharma">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">Prefer not to say</option>
                            <option value="male"   @selected(old('gender', auth()->user()->gender) === 'male')>Male</option>
                            <option value="female" @selected(old('gender', auth()->user()->gender) === 'female')>Female</option>
                            <option value="other"  @selected(old('gender', auth()->user()->gender) === 'other')>Other</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Phone + LinkedIn --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <div class="input-prefix">
                        <span class="input-prefix-label">🇮🇳 +91</span>
                        <input type="tel" name="phone" class="form-input" placeholder="98765 43210"
                               maxlength="10"
                               value="{{ old('phone', ltrim(auth()->user()->phone ?? '', '+91')) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">LinkedIn Profile URL</label>
                    <input type="url" name="linkedin" class="form-input"
                           placeholder="https://linkedin.com/in/yourname"
                           value="{{ old('linkedin', auth()->user()->linkedin) }}">
                </div>
            </div>

            {{-- Bio --}}
            <div class="form-group">
                <label class="form-label">Bio / About You *</label>
                <textarea name="bio" class="form-textarea" rows="5" required minlength="80" maxlength="2000"
                          id="bio-area"
                          placeholder="Write a compelling bio. Tell mentees who you are, what you've done, and what kind of help you can offer. Be specific — this is your first impression.">{{ old('bio', auth()->user()->bio) }}</textarea>
                <div style="display:flex; justify-content:space-between; margin-top:4px;">
                    <div class="form-hint">Minimum 80 characters. Be specific about your background.</div>
                    <div style="font-size:11px; color:var(--text-3);"><span id="bio-count">{{ strlen(auth()->user()->bio ?? '') }}</span>/2000</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Save & Continue →</button>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 2 — Professional Details
             ════════════════════════════════════════════════ --}}
        @elseif($step == 2)
        <h2 style="font-size:20px; font-weight:800; margin-bottom:4px;">Professional Details</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">Your current role, experience, and your rate per minute.</p>

        <form
            action="{{ route('mentor.onboarding.save2') }}"
            method="POST"
            data-ajax-form="{{ route('mentor.onboarding.save2') }}"
            data-redirect="{{ route('mentor.onboarding', ['step' => 3]) }}"
            data-success="Step 2 saved!"
        >
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Current Designation *</label>
                    <input type="text" name="designation" class="form-input" required
                           placeholder="Senior Product Manager"
                           value="{{ old('designation', auth()->user()->designation) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Company / Organization *</label>
                    <input type="text" name="company" class="form-input" required
                           placeholder="Google, Amazon, IIT Bombay…"
                           value="{{ old('company', auth()->user()->company) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Years of Experience *</label>
                    <input type="number" name="experience_years" class="form-input" required
                           min="1" max="50" placeholder="8"
                           value="{{ old('experience_years', auth()->user()->experience_years) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Rate per Minute (₹) *</label>
                    <input type="number" name="rate_per_minute" class="form-input" required
                           min="5" max="500" step="0.5" id="rate-input"
                           placeholder="10"
                           value="{{ old('rate_per_minute', auth()->user()->rate_per_minute ?: 10) }}">
                    <div class="form-hint">Hourly equivalent: <strong id="hourly-equiv">₹{{ (auth()->user()->rate_per_minute ?: 10) * 60 }}</strong></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Primary Domain / Field *</label>
                <select name="field" class="form-select" required>
                    <option value="">— Select your primary domain —</option>
                    @foreach([
                        'Engineering & Technology',
                        'Product Management',
                        'Design & UX / UI',
                        'Finance & Investment Banking',
                        'MBA & Management Consulting',
                        'Marketing & Growth',
                        'Data Science & AI / ML',
                        'Law & Legal',
                        'Medicine & Healthcare',
                        'Arts & Humanities',
                        'Government & Public Policy',
                        'Education & Academia',
                        'Entrepreneurship & Startups',
                    ] as $field)
                    <option value="{{ $field }}" @selected(old('field', auth()->user()->field) === $field)>{{ $field }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Education Stream <span style="font-weight:400; color:var(--text-3);">(helps with mentee matching)</span></label>
                <select name="education_stream" class="form-select">
                    <option value="">— Select stream —</option>
                    @foreach($streams as $stream)
                    <option value="{{ $stream->name }}" @selected(old('education_stream', auth()->user()->education_stream) === $stream->name)>
                        {{ $stream->icon ?? '' }} {{ $stream->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Rate preview box --}}
            <div style="background:var(--bg-3); border:1px solid var(--border); border-radius:var(--radius); padding:16px; margin-bottom:20px; display:flex; gap:24px; flex-wrap:wrap;">
                <div style="flex:1; text-align:center;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-3); margin-bottom:4px;">30 min session</div>
                    <div style="font-size:22px; font-weight:800; color:var(--brand);" id="preview-30">₹{{ (auth()->user()->rate_per_minute ?: 10) * 30 }}</div>
                </div>
                <div style="flex:1; text-align:center;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-3); margin-bottom:4px;">60 min session</div>
                    <div style="font-size:22px; font-weight:800; color:var(--brand);" id="preview-60">₹{{ (auth()->user()->rate_per_minute ?: 10) * 60 }}</div>
                </div>
                <div style="flex:1; text-align:center;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-3); margin-bottom:4px;">90 min session</div>
                    <div style="font-size:22px; font-weight:800; color:var(--brand);" id="preview-90">₹{{ (auth()->user()->rate_per_minute ?: 10) * 90 }}</div>
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <a href="{{ route('mentor.onboarding', ['step' => 1]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;">Save & Continue →</button>
            </div>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 3 — Skills & Expertise
             ════════════════════════════════════════════════ --}}
        @elseif($step == 3)
        <h2 style="font-size:20px; font-weight:800; margin-bottom:4px;">Skills & Expertise</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">Add the skills you can mentor on. Be specific — mentees search by these tags.</p>

        <form
            action="{{ route('mentor.onboarding.save3') }}"
            method="POST"
            id="expertise-form"
            data-ajax-form="{{ route('mentor.onboarding.save3') }}"
            data-redirect="{{ route('mentor.onboarding', ['step' => 4]) }}"
            data-success="Expertise saved!"
        >
            @csrf

            {{-- Skill input --}}
            <div class="form-group">
                <label class="form-label">Type a skill and press Enter or comma</label>
                <div style="display:flex; gap:8px;">
                    <input type="text" id="skill-input" class="form-input"
                           placeholder="e.g. Python, System Design, CAT Prep, Leadership…">
                    <button type="button" class="btn btn-ghost" onclick="addSkillFromInput()" style="flex-shrink:0;">+ Add</button>
                </div>
            </div>

            {{-- Tags display --}}
            <div style="min-height:64px; padding:12px; background:var(--bg-3); border:1px solid var(--border); border-radius:var(--radius); display:flex; flex-wrap:wrap; gap:8px; margin-bottom:20px; align-content:flex-start;" id="skills-container">
                @foreach(auth()->user()->expertise ?? [] as $skill)
                <span class="skill-tag" style="display:inline-flex; align-items:center; gap:6px; padding:5px 12px; background:var(--brand-muted); border:1px solid rgba(245,158,11,.3); border-radius:999px; font-size:12px; font-weight:600; color:var(--brand);">
                    {{ $skill }}
                    <button type="button" onclick="removeSkillTag(this)" data-skill="{{ $skill }}"
                            style="background:none; color:var(--brand); font-size:14px; cursor:pointer; line-height:1; padding:0;">×</button>
                    <input type="hidden" name="expertise[]" value="{{ $skill }}">
                </span>
                @endforeach
                @if(empty(auth()->user()->expertise))
                <div id="skills-placeholder" style="font-size:12px; color:var(--text-3); margin:4px;">No skills added yet. Use the input above or click quick-add below.</div>
                @endif
            </div>

            {{-- Quick add chips --}}
            <div style="margin-bottom:24px;">
                <div style="font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--text-3); margin-bottom:12px;">Quick Add — Popular Skills</div>
                <div class="chip-wrap">
                    @foreach([
                        'Product Management','System Design','DSA & Algorithms','FAANG Interview Prep',
                        'Leadership & Management','Machine Learning','Data Science','SQL',
                        'Python','Java','React / Frontend','Backend Development',
                        'Finance','Investment Banking','MBA Prep','CAT / GMAT',
                        'UX Design','Brand Strategy','Digital Marketing','Growth Hacking',
                        'Communication Skills','Resume Review','Career Guidance','Startup Advice',
                    ] as $s)
                    <div class="chip" onclick="addSkillDirect('{{ addslashes($s) }}')">{{ $s }}</div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <a href="{{ route('mentor.onboarding', ['step' => 2]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;" onclick="return validateExpertise()">Save & Continue →</button>
            </div>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 4 — Preferences (Who can you help?)
             ════════════════════════════════════════════════ --}}
        @elseif($step == 4)
        <h2 style="font-size:20px; font-weight:800; margin-bottom:4px;">Mentorship Preferences</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">Help us match you with the right mentees.</p>

        <form
            action="{{ route('mentor.onboarding.save4') }}"
            method="POST"
            data-ajax-form="{{ route('mentor.onboarding.save4') }}"
            data-redirect="{{ route('mentor.onboarding', ['step' => 5]) }}"
            data-success="Preferences saved!"
        >
            @csrf

            {{-- Mentee Types --}}
            <div class="form-group">
                <label class="form-label">Who can you best help? <span style="font-weight:400; color:var(--text-3);">(select all that apply)</span></label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                    @foreach([
                        '10th–12th Students',
                        'College Undergraduates',
                        'Postgraduates / MBA Students',
                        'Working Professionals (1–5 yrs)',
                        'Working Professionals (5+ yrs)',
                        'Career Changers',
                        'Startup Founders',
                        'UPSC / Government Exam Aspirants',
                    ] as $type)
                    <label style="display:flex; align-items:center; gap:10px; padding:11px 14px; border:1.5px solid var(--border); border-radius:var(--radius); cursor:pointer; font-size:13px; transition:all .2s;"
                           onclick="this.style.borderColor = this.querySelector('input').checked ? 'var(--brand)' : 'var(--border)'; this.style.background = this.querySelector('input').checked ? 'var(--brand-muted)' : 'var(--card-bg)'">
                        <input type="checkbox" name="preferences[]" value="{{ $type }}" style="accent-color:var(--brand);"
                               @if(in_array($type, auth()->user()->preferences ?? [])) checked @endif>
                        {{ $type }}
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Strengths --}}
            <div class="form-group">
                <label class="form-label">Your strengths as a mentor <span style="font-weight:400; color:var(--text-3);">(select all that apply)</span></label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px;">
                    @foreach([
                        'Career Guidance & Planning',
                        'Technical Interview Prep',
                        'Resume & LinkedIn Review',
                        'Mock Interviews',
                        'Goal Setting & Accountability',
                        'Industry Insights & Networks',
                        'MBA / Higher Education Guidance',
                        'Study Abroad Counseling',
                        'Salary & Offer Negotiation',
                        'Startup / Entrepreneurship',
                    ] as $strength)
                    <label style="display:flex; align-items:center; gap:10px; padding:10px 14px; border:1.5px solid var(--border); border-radius:var(--radius); cursor:pointer; font-size:13px; transition:all .2s;"
                           onclick="this.style.borderColor = this.querySelector('input').checked ? 'var(--brand)' : 'var(--border)'; this.style.background = this.querySelector('input').checked ? 'var(--brand-muted)' : 'var(--card-bg)'">
                        <input type="checkbox" name="strengths[]" value="{{ $strength }}" style="accent-color:var(--brand);"
                               @if(in_array($strength, auth()->user()->strengths ?? [])) checked @endif>
                        {{ $strength }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <a href="{{ route('mentor.onboarding', ['step' => 3]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;">Save & Continue →</button>
            </div>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 5 — Review & Submit
             ════════════════════════════════════════════════ --}}
        @elseif($step == 5)
        @php
            $u = auth()->user();
            $checks = [
                ['Photo uploaded',          (bool) $u->avatar_url,                    route('mentor.onboarding', ['step'=>1])],
                ['Bio written (80+ chars)',  strlen($u->bio ?? '') >= 80,              route('mentor.onboarding', ['step'=>1])],
                ['Designation & Company',   !empty($u->designation) && !empty($u->company), route('mentor.onboarding', ['step'=>2])],
                ['Experience & Rate',       $u->experience_years > 0 && $u->rate_per_minute > 0, route('mentor.onboarding', ['step'=>2])],
                ['Expertise skills added',  !empty($u->expertise) && count($u->expertise) >= 1, route('mentor.onboarding', ['step'=>3])],
                ['Preferences selected',    !empty($u->preferences),                  route('mentor.onboarding', ['step'=>4])],
            ];
            $doneCnt  = collect($checks)->filter(fn($c) => $c[1])->count();
            $allDone  = $doneCnt === count($checks);
            $pct      = (int) round($doneCnt / count($checks) * 100);
        @endphp

        <h2 style="font-size:20px; font-weight:800; margin-bottom:4px;">Review & Submit</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:24px;">Review your profile before submitting for approval by our team.</p>

        {{-- Preview card --}}
        <div class="mentor-card" style="margin-bottom:24px; cursor:default;">
            <div class="mentor-card-head">
                <div class="mentor-avatar-lg">
                    @if($u->avatar_url)
                        <img src="{{ $u->avatar_url }}" alt="{{ $u->name }}">
                    @else
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    @endif
                </div>
                <div class="mentor-card-info">
                    <div class="mentor-card-name">{{ $u->name }}</div>
                    <div class="mentor-card-role">{{ $u->designation ?? '(no designation)' }}{{ $u->company ? ' · '.$u->company : '' }}</div>
                </div>
                <span class="badge badge-muted" style="margin-left:auto;">Preview</span>
            </div>
            <div class="mentor-card-bio">{{ Str::limit($u->bio ?? '(no bio)', 90) }}</div>
            @if(!empty($u->expertise))
            <div class="mentor-tags">
                @foreach(array_slice($u->expertise, 0, 5) as $tag)
                <span class="tag">{{ $tag }}</span>
                @endforeach
                @if(count($u->expertise) > 5)
                <span class="tag">+{{ count($u->expertise) - 5 }} more</span>
                @endif
            </div>
            @endif
            <div class="mentor-card-meta">
                <span class="mentor-rate">₹{{ $u->rate_per_minute }}/min</span>
                <span style="font-size:12px; color:var(--text-2);">{{ $u->experience_years }}+ yrs experience</span>
            </div>
        </div>

        {{-- Progress --}}
        <div style="margin-bottom:20px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:13px; font-weight:600;">Profile Completeness</span>
                <span style="font-size:13px; font-weight:700; color:{{ $allDone ? 'var(--success)' : 'var(--brand)' }};">{{ $pct }}%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width:{{ $pct }}%; background:{{ $allDone ? 'var(--success)' : 'var(--brand)' }};"></div>
            </div>
        </div>

        {{-- Checklist --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:24px;">
            @foreach($checks as [$label, $done, $link])
            <a href="{{ $link }}" style="display:flex; align-items:center; gap:10px; padding:10px 12px; border-radius:var(--radius-sm); font-size:13px; text-decoration:none; background:{{ $done ? 'var(--success-muted)' : 'var(--error-muted)' }}; color:{{ $done ? 'var(--success)' : 'var(--error)' }}; border:1px solid {{ $done ? 'rgba(34,197,94,.25)' : 'rgba(239,68,68,.25)' }};">
                {{ $done ? '✅' : '❌' }} {{ $label }}
                @if(!$done)<span style="margin-left:auto; font-size:10px;">Fix →</span>@endif
            </a>
            @endforeach
        </div>

        @if(!$allDone)
        <div class="alert alert-warning" style="margin-bottom:20px;">
            <span class="alert-icon">⚠️</span>
            <div>Please complete all items above before submitting. Click any red item to fix it.</div>
        </div>
        @endif

        {{-- Info notice --}}
        <div style="background:var(--info-muted); border:1px solid rgba(59,130,246,.25); border-radius:var(--radius); padding:14px 16px; margin-bottom:24px; font-size:13px; color:var(--text-2); line-height:1.7;">
            ℹ️ <strong style="color:var(--text);">What happens next?</strong><br>
            Our team will review your profile within <strong>24–48 hours</strong>. You'll receive an email notification once approved. After approval, your profile goes live and mentees can book sessions.
        </div>

        <div style="display:flex; gap:12px;">
            <a href="{{ route('mentor.onboarding', ['step' => 4]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
            <form action="{{ route('mentor.onboarding.submit') }}" method="POST" style="flex:1;">
                @csrf
                <button type="submit" class="btn btn-primary btn-full" style="font-size:15px; padding:14px;" @disabled(!$allDone)>
                    ✓ Submit for Approval
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
// ── Bio char counter ──────────────────────────────────────────
const bioArea = document.getElementById('bio-area');
const bioCount = document.getElementById('bio-count');
if (bioArea && bioCount) {
    bioArea.addEventListener('input', () => {
        bioCount.textContent = bioArea.value.length;
        bioCount.style.color = bioArea.value.length < 80 ? 'var(--error)' : 'var(--text-3)';
    });
}

// ── Rate → price previews ─────────────────────────────────────
const rateInput = document.getElementById('rate-input');
if (rateInput) {
    const update = () => {
        const r = parseFloat(rateInput.value) || 0;
        const fmt = v => '₹' + v.toLocaleString('en-IN');
        const el = id => document.getElementById(id);
        if (el('hourly-equiv')) el('hourly-equiv').textContent = fmt(r * 60);
        if (el('preview-30'))   el('preview-30').textContent   = fmt(r * 30);
        if (el('preview-60'))   el('preview-60').textContent   = fmt(r * 60);
        if (el('preview-90'))   el('preview-90').textContent   = fmt(r * 90);
    };
    rateInput.addEventListener('input', update);
}

// ── Skills management ─────────────────────────────────────────
function addSkillFromInput() {
    const inp = document.getElementById('skill-input');
    if (!inp) return;
    const raw = inp.value.trim();
    if (!raw) return;
    raw.split(',').map(s => s.trim()).filter(Boolean).forEach(addSkillDirect);
    inp.value = '';
    inp.focus();
}

function addSkillDirect(skill) {
    const container = document.getElementById('skills-container');
    if (!container) return;

    // No duplicates
    const existing = [...container.querySelectorAll('input[name="expertise[]"]')]
        .map(i => i.value.toLowerCase());
    if (existing.includes(skill.toLowerCase())) return;

    // Remove placeholder
    const ph = document.getElementById('skills-placeholder');
    if (ph) ph.remove();

    const span = document.createElement('span');
    span.className = 'skill-tag';
    span.style.cssText = 'display:inline-flex; align-items:center; gap:6px; padding:5px 12px; background:var(--brand-muted); border:1px solid rgba(245,158,11,.3); border-radius:999px; font-size:12px; font-weight:600; color:var(--brand);';
    span.innerHTML = `${skill}
        <button type="button" onclick="removeSkillTag(this)" data-skill="${skill}"
                style="background:none; color:var(--brand); font-size:14px; cursor:pointer; line-height:1; padding:0;">×</button>
        <input type="hidden" name="expertise[]" value="${skill}">`;
    container.appendChild(span);

    // Update chip style
    document.querySelectorAll('.chip').forEach(c => {
        if (c.textContent.trim() === skill) c.classList.add('selected');
    });
}

function removeSkillTag(btn) {
    const skill = btn.dataset.skill;
    btn.closest('.skill-tag').remove();
    document.querySelectorAll('.chip').forEach(c => {
        if (c.textContent.trim() === skill) c.classList.remove('selected');
    });
}

function validateExpertise() {
    const count = document.querySelectorAll('#skills-container input[name="expertise[]"]').length;
    if (count === 0) {
        showToast('error', 'Please add at least one skill or expertise.');
        return false;
    }
    return true;
}

// Enter / comma shortcut
document.getElementById('skill-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addSkillFromInput(); }
});

// Mark already-added chips as selected
document.addEventListener('DOMContentLoaded', () => {
    const added = [...document.querySelectorAll('#skills-container input[name="expertise[]"]')]
        .map(i => i.value.toLowerCase());
    document.querySelectorAll('.chip').forEach(c => {
        if (added.includes(c.textContent.trim().toLowerCase())) c.classList.add('selected');
    });
});
</script>
@endpush