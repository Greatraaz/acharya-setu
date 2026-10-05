@extends('frontend.layouts.app')
@section('title', 'Set Up Your Profile — Step ' . $step . ' of 4 — Vedrix')

@push('styles')
<style>
    .mentee-onboard {
        min-height: 100vh;
        min-height: 100dvh;
        padding: calc(var(--nav-h) + 40px) 16px 60px;
        background: var(--bg);
        box-sizing: border-box;
    }
    .mentee-onboard__inner {
        max-width: 620px;
        margin: 0 auto;
    }
    .mentee-onboard__hero {
        text-align: center;
        margin-bottom: 32px;
    }
    .mentee-onboard__hero img {
        height: 48px;
        width: auto;
        max-width: 180px;
        object-fit: contain;
        margin: 0 auto 14px;
        display: block;
    }
    .mentee-onboard__steps {
        display: flex;
        align-items: center;
        margin-bottom: 36px;
        gap: 0;
    }
    .mentee-onboard__step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        min-width: 0;
    }
    .mentee-onboard__step-label {
        font-size: 10px;
        font-weight: 600;
        margin-top: 5px;
        text-align: center;
        line-height: 1.2;
        white-space: nowrap;
    }
    .mentee-onboard__step-line {
        height: 2px;
        flex: 1;
        min-width: 8px;
        margin-bottom: 20px;
    }
    .mentee-onboard__card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 36px;
    }
    .mentee-onboard__profile-row {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        margin-bottom: 8px;
    }
    .mentee-onboard__avatar-wrap {
        flex-shrink: 0;
        text-align: center;
    }
    .mentee-onboard__fields {
        flex: 1;
        min-width: 0;
    }
    .mentee-onboard__grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 16px;
    }
    .mentee-onboard__grid-2.no-top {
        margin-top: 0;
    }
    .mentee-onboard__actions {
        display: flex;
        gap: 12px;
    }
    .mentee-onboard__actions .btn-primary {
        flex: 1;
    }
    .mentee-onboard__track-add {
        display: flex;
        gap: 8px;
    }
    .mentee-goals-title {
        font-size: 19px;
        font-weight: 800;
        margin-bottom: 4px;
        line-height: 1.3;
        color: var(--text);
    }
    .mentee-goals-title span {
        color: var(--brand);
    }
    .mentee-goals-sub {
        font-size: 13px;
        color: var(--text-2);
        margin-bottom: 28px;
    }
    .mentee-goals-sub span {
        color: var(--brand);
        font-weight: 600;
    }
    .mentee-goals-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 24px;
    }
    .mentee-goal-option {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        background: var(--card-bg);
        cursor: pointer;
        transition: border-color .15s, background .15s, box-shadow .15s;
        text-align: left;
        color: var(--text);
        font: inherit;
    }
    .mentee-goal-option:hover {
        border-color: rgba(245, 158, 11, .45);
    }
    .mentee-goal-option.is-selected {
        border-color: var(--brand);
        background: var(--brand-muted);
        box-shadow: 0 0 0 1px rgba(245, 158, 11, .15);
    }
    .mentee-goal-option__icon {
        font-size: 18px;
        line-height: 1;
        flex-shrink: 0;
        width: 24px;
        text-align: center;
    }
    .mentee-goal-option__label {
        flex: 1;
        min-width: 0;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
    }
    .mentee-goal-option__check {
        width: 20px;
        height: 20px;
        border-radius: 5px;
        border: 2px solid var(--border);
        background: var(--bg-3);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .15s;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }
    .mentee-goal-option.is-selected .mentee-goal-option__check {
        border-color: var(--brand);
        background: var(--brand);
    }
    .mentee-stream-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 8px;
        margin-bottom: 8px;
    }
    .mentee-stream-card {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 14px 16px;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        background: var(--card-bg);
        cursor: pointer;
        transition: border-color .15s, background .15s, box-shadow .15s;
        text-align: left;
        color: var(--text);
        font: inherit;
    }
    .mentee-stream-card:hover {
        border-color: rgba(245, 158, 11, .45);
    }
    .mentee-stream-card.is-active {
        border-color: var(--brand);
        background: var(--brand-muted);
        box-shadow: 0 0 0 1px rgba(245, 158, 11, .15);
    }
    .mentee-stream-card__icon {
        font-size: 22px;
        line-height: 1;
        width: 28px;
        text-align: center;
        flex-shrink: 0;
    }
    .mentee-stream-card__body {
        flex: 1;
        min-width: 0;
    }
    .mentee-stream-card__name {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.3;
    }
    .mentee-stream-card__field {
        font-size: 11px;
        color: var(--text-3);
        margin-top: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .mentee-stream-card__chevron {
        color: var(--text-3);
        font-size: 16px;
        flex-shrink: 0;
    }
    .mentee-stream-sheet {
        position: fixed;
        inset: 0;
        z-index: 1200;
        display: none;
        align-items: flex-end;
        justify-content: center;
    }
    .mentee-stream-sheet.is-open {
        display: flex;
    }
    .mentee-stream-sheet__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .55);
    }
    .mentee-stream-sheet__panel {
        position: relative;
        width: 100%;
        max-width: 520px;
        max-height: min(78vh, 640px);
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 20px 20px 0 0;
        padding: 12px 16px 20px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 -8px 30px rgba(0, 0, 0, .25);
        animation: menteeSheetUp .2s ease-out;
    }
    @keyframes menteeSheetUp {
        from { transform: translateY(24px); opacity: .6; }
        to { transform: translateY(0); opacity: 1; }
    }
    .mentee-stream-sheet__handle {
        width: 40px;
        height: 4px;
        border-radius: 999px;
        background: var(--border);
        margin: 2px auto 14px;
    }
    .mentee-stream-sheet__head {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }
    .mentee-stream-sheet__head-icon {
        font-size: 20px;
    }
    .mentee-stream-sheet__head-title {
        font-size: 15px;
        font-weight: 800;
    }
    .mentee-stream-sheet__options {
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding-bottom: 8px;
        -webkit-overflow-scrolling: touch;
    }
    .mentee-stream-option {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 12px 14px;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        background: var(--card-bg);
        cursor: pointer;
        transition: border-color .15s, background .15s;
        text-align: left;
        color: var(--text);
        font: inherit;
    }
    .mentee-stream-option.is-selected {
        border-color: var(--brand);
        background: var(--brand-muted);
    }
    .mentee-stream-option__icon {
        font-size: 18px;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
    }
    .mentee-stream-option__label {
        flex: 1;
        min-width: 0;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.3;
    }
    .mentee-stream-option__check {
        width: 20px;
        height: 20px;
        border-radius: 5px;
        border: 2px solid var(--border);
        background: var(--bg-3);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
    }
    .mentee-stream-option.is-selected .mentee-stream-option__check {
        border-color: var(--brand);
        background: var(--brand);
    }
    .mentee-stream-sheet__done {
        margin-top: 14px;
        width: 100%;
    }
    @media (max-width: 640px) {
        .mentee-onboard {
            padding: calc(var(--nav-h) + 20px) 14px 40px;
        }
        .mentee-onboard__hero {
            margin-bottom: 20px;
        }
        .mentee-onboard__hero img {
            height: 40px;
            margin-bottom: 8px;
        }
        .mentee-onboard__hero h1 {
            font-size: 20px !important;
        }
        .mentee-onboard__steps {
            margin-bottom: 24px;
        }
        .mentee-onboard__step-circle {
            width: 30px !important;
            height: 30px !important;
            font-size: 12px !important;
        }
        .mentee-onboard__step-label {
            font-size: 9px;
            white-space: normal;
            max-width: 64px;
        }
        .mentee-onboard__step-line {
            margin-bottom: 22px;
            min-width: 4px;
        }
        .mentee-onboard__card {
            padding: 20px 16px;
        }
        .mentee-onboard__profile-row {
            flex-direction: column;
            align-items: center;
            gap: 16px;
            text-align: center;
        }
        .mentee-onboard__fields {
            width: 100%;
            text-align: left;
        }
        .mentee-onboard__avatar-hint {
            font-size: 11px !important;
        }
        .mentee-onboard__grid-2 {
            grid-template-columns: 1fr;
            gap: 0;
            margin-top: 0;
        }
        .mentee-onboard__grid-2 .form-group {
            margin-bottom: 16px;
        }
        .mentee-onboard__actions {
            gap: 8px;
        }
        .mentee-onboard__actions .btn-ghost {
            flex-shrink: 0;
            padding-left: 14px;
            padding-right: 14px;
        }
        .mentee-onboard__track-add {
            flex-direction: column;
        }
        .mentee-onboard__track-add .btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="mentee-onboard">
<div class="mentee-onboard__inner">

    {{-- ── HEADER ────────────────────────────────────────── --}}
    <div class="mentee-onboard__hero">
        <img src="{{ asset('images/logo.png') }}" alt="Vedrix">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:4px;">Set Up Your Profile</h1>
        <p style="font-size:13px; color:var(--text-2);">Step {{ $step }} of 4 — Takes less than 3 minutes</p>
    </div>

    {{-- ── STEP PROGRESS BAR ──────────────────────────────── --}}
    @php $stepLabels = ['About You', 'Education', 'Tracks', 'Preferences']; @endphp
    <div class="mentee-onboard__steps">
        @foreach($stepLabels as $i => $label)
        @php $num = $i + 1; $isDone = $num < $step; $isCurrent = $num === $step; @endphp
        <div class="mentee-onboard__step">
            <div class="mentee-onboard__step-circle" style="
                width:36px; height:36px; border-radius:50%;
                display:flex; align-items:center; justify-content:center;
                font-size:13px; font-weight:700; font-family:var(--font-head);
                background: {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--brand)' : 'var(--bg-4)') }};
                color: {{ ($isDone || $isCurrent) ? '#000' : 'var(--text-3)' }};
                border:2px solid {{ $isDone ? 'var(--success)' : ($isCurrent ? 'var(--brand)' : 'var(--border)') }};
            ">{{ $isDone ? '✓' : $num }}</div>
            <div class="mentee-onboard__step-label" style="color:{{ $isCurrent ? 'var(--brand)' : 'var(--text-3)' }};">{{ $label }}</div>
        </div>
        @if($i < 3)
        <div class="mentee-onboard__step-line" style="background:{{ $num < $step ? 'var(--success)' : 'var(--border)' }};"></div>
        @endif
        @endforeach
    </div>

    {{-- ── CARD ───────────────────────────────────────────── --}}
    <div class="mentee-onboard__card">

        {{-- ════════════════════════════════════════════════
             STEP 1 — About You
             ════════════════════════════════════════════════ --}}
        @if($step == 1)
        <h2 style="font-size:19px; font-weight:800; margin-bottom:4px;">Tell us about yourself</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">We'll use this to personalise your mentor recommendations.</p>

        <form
            action="{{ route('mentee.onboarding.save1') }}"
            method="POST"
            enctype="multipart/form-data"
            data-ajax-form="{{ route('mentee.onboarding.save1') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 2]) }}"
            data-success="Saved!"
        >
            @csrf

            <div class="mentee-onboard__profile-row">
                <div class="mentee-onboard__avatar-wrap">
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
                            {{ strtoupper(substr(auth()->user()->name ?? 'M', 0, 1)) }}
                        @endif
                    </div>
                    <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;"
                           onchange="previewImage(this, '#avatar-preview')">
                    <div class="mentee-onboard__avatar-hint" style="font-size:10px; color:var(--text-3); margin-top:6px; line-height:1.4;">Click to upload photo</div>
                </div>
                <div class="mentee-onboard__fields">
                    <div class="form-group" style="margin-bottom:12px;">
                        <label class="form-label">Your Full Name *</label>
                        <input type="text" name="name" class="form-input" required
                               value="{{ old('name', auth()->user()->name) }}"
                               placeholder="Rahul Sharma">
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

            <div class="mentee-onboard__grid-2">
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <div class="input-prefix">
                        <span class="input-prefix-label">🇮🇳 +91</span>
                        <input type="tel" name="phone" class="form-input" placeholder="98765 43210" maxlength="10"
                               value="{{ old('phone', preg_replace('/\D/', '', substr(auth()->user()->phone ?? '', -10))) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Address / Location *</label>
                    <input type="text" name="address" class="form-input" required maxlength="200"
                           placeholder="City, state or full address"
                           value="{{ old('address', auth()->user()->location) }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Continue →</button>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 2 — Education
             ════════════════════════════════════════════════ --}}
        @elseif($step == 2)
        @php
            $streamCatalog = [
                'Engineering' => [
                    'icon' => '👷',
                    'fields' => [
                        ['Computer Science Engineering (CSE)', '💻'],
                        ['Electrical Engineering (EE)', '⚡'],
                        ['Mechanical Engineering (ME)', '⚙️'],
                        ['Civil Engineering (CE)', '🏗️'],
                        ['Chemical Engineering', '🧪'],
                    ],
                ],
                'Commerce' => [
                    'icon' => '🏢',
                    'fields' => [
                        ['Accounting', '📒'],
                        ['Finance', '💰'],
                        ['Marketing', '📈'],
                        ['Business Management', '🗂️'],
                        ['Economics', '📊'],
                    ],
                ],
                'Arts' => [
                    'icon' => '🎨',
                    'fields' => [
                        ['Psychology', '🧠'],
                        ['Political Science', '🏛️'],
                        ['Geography', '🌍'],
                        ['History', '📜'],
                        ['Sociology', '👥'],
                    ],
                ],
                'Management' => [
                    'icon' => '⚙️',
                    'fields' => [
                        ['Finance Management', '💵'],
                        ['Marketing Management', '📉'],
                        ['Operations Management', '🔄'],
                        ['Business Analytics', '📊'],
                        ['IT Management', '🖱️'],
                    ],
                ],
                'Switch Career' => [
                    'icon' => '🔀',
                    'fields' => [
                        ['Tech Transition', '💻'],
                        ['Move to Management', '👔'],
                        ['Entrepreneurship', '🚀'],
                        ['Government / Civil Services', '🏛️'],
                        ['Freelancing', '✍️'],
                    ],
                ],
            ];
            $selectedStream = old('education_stream', auth()->user()->education_stream);
            $selectedField  = old('field', auth()->user()->field);
        @endphp
        <h2 class="mentee-goals-title">Select Your <span>Stream/Domain</span></h2>
        <p class="mentee-goals-sub">Choose your area of career interest for personalized guidance</p>

        <form
            action="{{ route('mentee.onboarding.save2') }}"
            method="POST"
            data-ajax-form="{{ route('mentee.onboarding.save2') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 3]) }}"
            data-success="Saved!"
        >
            @csrf
            <input type="hidden" name="education_stream" id="stream-hidden" value="{{ $selectedStream }}">
            <input type="hidden" name="field" id="field-hidden" value="{{ $selectedField }}">

            <div class="form-group">
                <label class="form-label">Career Stream *</label>
                <div class="mentee-stream-list" id="stream-catalog">
                    @foreach($streamCatalog as $streamName => $meta)
                    @php $isActive = strcasecmp((string) $selectedStream, $streamName) === 0; @endphp
                    <button
                        type="button"
                        class="mentee-stream-card {{ $isActive ? 'is-active' : '' }}"
                        data-stream="{{ $streamName }}"
                        data-icon="{{ $meta['icon'] }}"
                        onclick="openStreamSheet('{{ $streamName }}')"
                    >
                        <span class="mentee-stream-card__icon">{{ $meta['icon'] }}</span>
                        <span class="mentee-stream-card__body">
                            <span class="mentee-stream-card__name">{{ $streamName }}</span>
                            <span class="mentee-stream-card__field" data-stream-field="{{ $streamName }}" @if(!($isActive && $selectedField)) style="display:none;" @endif>
                                {{ $isActive && $selectedField ? $selectedField : '' }}
                            </span>
                        </span>
                        <span class="mentee-stream-card__chevron">›</span>
                    </button>
                    @endforeach
                </div>
                <div id="stream-error" class="form-error" style="display:none; margin-top:8px;">Please select a stream and domain.</div>
            </div>

            {{-- College & Year --}}
            <div class="mentee-onboard__grid-2 no-top">
                <div class="form-group">
                    <label class="form-label">College / University</label>
                    <input type="text" name="college" class="form-input"
                           placeholder="Your College / Institute"
                           value="{{ old('college', auth()->user()->college) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Graduation Year / Batch</label>
                    <input type="text" name="year" class="form-input"
                           placeholder="Current Year (e.g. 3rd Year)"
                           value="{{ old('year', auth()->user()->year) }}">
                </div>
            </div>

            <div class="mentee-onboard__actions">
                <a href="{{ route('mentee.onboarding', ['step' => 1]) }}" class="btn btn-ghost">← Back</a>
                <button type="submit" class="btn btn-primary" onclick="return validateStream()">Continue →</button>
            </div>
        </form>

        {{-- Stream subdomain bottom sheet --}}
        <div class="mentee-stream-sheet" id="stream-sheet" aria-hidden="true">
            <div class="mentee-stream-sheet__backdrop" onclick="closeStreamSheet()"></div>
            <div class="mentee-stream-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="stream-sheet-title">
                <div class="mentee-stream-sheet__handle"></div>
                <div class="mentee-stream-sheet__head">
                    <span class="mentee-stream-sheet__head-icon" id="stream-sheet-icon">⚙️</span>
                    <span class="mentee-stream-sheet__head-title" id="stream-sheet-title">Select domain</span>
                </div>
                <div class="mentee-stream-sheet__options" id="stream-sheet-options"></div>
                <button type="button" class="btn btn-primary mentee-stream-sheet__done" onclick="confirmStreamField()">Done</button>
            </div>
        </div>

        <script type="application/json" id="stream-catalog-data">@json($streamCatalog)</script>

        {{-- ════════════════════════════════════════════════
             STEP 3 — Career Tracks / Goals
             ════════════════════════════════════════════════ --}}
        @elseif($step == 3)
        @php
            $careerGoals = [
                ['Job Opportunities', '💼'],
                ['Internships', '👨‍💻'],
                ['Resume Building', '📄'],
                ['Portfolio / Projects', '📁'],
                ['Interview Preparation', '✏️'],
                ['Skill Development', '🚀'],
                ['Higher Education Abroad', '🌐'],
                ['Competitive Exams', '🏆'],
            ];
            $selectedTracks = collect($tracks ?? [])->filter()->values();
            $selectedLower = $selectedTracks->map(fn ($t) => strtolower(trim($t)));
        @endphp
        <h2 class="mentee-goals-title">Select Your <span>Career Goals</span></h2>
        <p class="mentee-goals-sub">Choose what you want to <span>Achieve</span></p>

        <form
            action="{{ route('mentee.onboarding.save3') }}"
            method="POST"
            id="mentee-tracks-form"
            data-ajax-form="{{ route('mentee.onboarding.save3') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 4]) }}"
            data-success="Goals saved!"
        >
            @csrf

            <div class="mentee-goals-list" id="career-goals-list">
                @foreach($careerGoals as $i => [$label, $icon])
                @php $isSelected = $selectedLower->contains(strtolower($label)); @endphp
                <button
                    type="button"
                    class="mentee-goal-option {{ $isSelected ? 'is-selected' : '' }}"
                    data-goal="{{ $label }}"
                    onclick="toggleCareerGoal(this)"
                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                >
                    <span class="mentee-goal-option__icon">{{ $icon }}</span>
                    <span class="mentee-goal-option__label">{{ $i + 1 }}. {{ $label }}</span>
                    <span class="mentee-goal-option__check" aria-hidden="true">{{ $isSelected ? '✓' : '' }}</span>
                    @if($isSelected)
                    <input type="hidden" name="tracks[]" value="{{ $label }}">
                    @endif
                </button>
                @endforeach
            </div>

            <div class="mentee-onboard__actions">
                <a href="{{ route('mentee.onboarding', ['step' => 2]) }}" class="btn btn-ghost">← Back</a>
                <button type="submit" class="btn btn-primary" onclick="return validateTracks()">Continue →</button>
            </div>
        </form>

        {{-- ════════════════════════════════════════════════
             STEP 4 — Preferences
             ════════════════════════════════════════════════ --}}
        @elseif($step == 4)
        <h2 style="font-size:19px; font-weight:800; margin-bottom:4px;">Set Your Preferences</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">Tell us how you prefer to learn so we can match the right mentoring style.</p>

        <form
            action="{{ route('mentee.onboarding.save4') }}"
            method="POST"
            data-ajax-form="{{ route('mentee.onboarding.save4') }}"
            data-redirect="{{ route('mentee.dashboard') }}"
            data-success="Onboarding complete!"
        >
            @csrf

            <div class="mentee-onboard__grid-2 no-top">
                <div class="form-group">
                    <label class="form-label">Weekly Time Commitment *</label>
                    <select name="weekly_time_commitment" class="form-select" required>
                        <option value="">Select…</option>
                        @foreach(['1-3 hours'=>'1–3 hours/week','3-5 hours'=>'3–5 hours/week','5-10 hours'=>'5–10 hours/week','10+ hours'=>'10+ hours/week'] as $val=>$label)
                        <option value="{{ $val }}" @selected(old('weekly_time_commitment', $preferences['weekly_time_commitment'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Budget</label>
                    <select name="monthly_budget" class="form-select">
                        <option value="">Select…</option>
                        @foreach(['Under ₹500','₹500 – ₹1,000','₹1,000 – ₹3,000','₹2,500+'] as $val)
                        <option value="{{ $val }}" @selected(old('monthly_budget', $preferences['monthly_budget'] ?? '') === $val)>{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Preferred Language *</label>
                    <select name="preferred_language" class="form-select" required>
                        <option value="">Select…</option>
                        @foreach(['English','Hindi','Tamil','Telugu','Kannada','Malayalam','Bengali','Marathi'] as $lang)
                        <option value="{{ $lang }}" @selected(old('preferred_language', $preferences['preferred_language'] ?? '') === $lang)>{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mentoring Format *</label>
                    <select name="mentoring_format" class="form-select" required>
                        <option value="">Select…</option>
                        @foreach(['one_on_one'=>'1:1 sessions','group'=>'Group sessions','video'=>'Video calls','audio'=>'Audio only','hybrid'=>'Hybrid / in-person'] as $val=>$label)
                        <option value="{{ $val }}" @selected(old('mentoring_format', $preferences['mentoring_format'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mentee-onboard__actions">
                <a href="{{ route('mentee.onboarding', ['step' => 3]) }}" class="btn btn-ghost">← Back</a>
                <button type="submit" class="btn btn-primary">Finish & Go to Dashboard →</button>
            </div>
        </form>
        @endif

    </div>{{-- /card --}}
</div>
</div>
@endsection

@push('scripts')
<script>
function previewImage(input, selector) {
    const file = input.files?.[0];
    if (!file) return;
    const preview = document.querySelector(selector);
    if (!preview) return;
    const reader = new FileReader();
    reader.onload = () => {
        preview.innerHTML = `<img src="${reader.result}" style="width:100%;height:100%;object-fit:cover;">`;
    };
    reader.readAsDataURL(file);
}

const STREAM_CATALOG = (() => {
    try {
        return JSON.parse(document.getElementById('stream-catalog-data')?.textContent || '{}');
    } catch (e) {
        return {};
    }
})();

let pendingStream = '';
let pendingField = '';

function openStreamSheet(streamName) {
    const meta = STREAM_CATALOG[streamName];
    if (!meta) return;

    pendingStream = streamName;
    pendingField = (document.getElementById('stream-hidden')?.value === streamName)
        ? (document.getElementById('field-hidden')?.value || '')
        : '';

    document.getElementById('stream-sheet-title').textContent = streamName;
    document.getElementById('stream-sheet-icon').textContent = meta.icon || '📚';

    const options = document.getElementById('stream-sheet-options');
    options.innerHTML = '';

    (meta.fields || []).forEach((item, idx) => {
        const label = Array.isArray(item) ? item[0] : item;
        const icon  = Array.isArray(item) ? (item[1] || '•') : '•';
        const selected = pendingField && pendingField.toLowerCase() === String(label).toLowerCase();

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'mentee-stream-option' + (selected ? ' is-selected' : '');
        btn.innerHTML = `
            <span class="mentee-stream-option__icon">${icon}</span>
            <span class="mentee-stream-option__label">${idx + 1}. ${label}</span>
            <span class="mentee-stream-option__check">${selected ? '✓' : ''}</span>
        `;
        btn.addEventListener('click', () => selectStreamField(label, btn));
        options.appendChild(btn);
    });

    const sheet = document.getElementById('stream-sheet');
    sheet.classList.add('is-open');
    sheet.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function selectStreamField(label, btn) {
    pendingField = label;
    document.querySelectorAll('.mentee-stream-option').forEach(el => {
        el.classList.remove('is-selected');
        const check = el.querySelector('.mentee-stream-option__check');
        if (check) check.textContent = '';
    });
    btn.classList.add('is-selected');
    const check = btn.querySelector('.mentee-stream-option__check');
    if (check) check.textContent = '✓';
}

function confirmStreamField() {
    if (!pendingStream || !pendingField) {
        showToast('error', 'Please select a domain option.');
        return;
    }

    document.getElementById('stream-hidden').value = pendingStream;
    document.getElementById('field-hidden').value = pendingField;

    document.querySelectorAll('.mentee-stream-card').forEach(card => {
        const active = card.dataset.stream === pendingStream;
        card.classList.toggle('is-active', active);
        const fieldEl = card.querySelector('[data-stream-field]');
        if (fieldEl) {
            if (active && pendingField) {
                fieldEl.textContent = pendingField;
                fieldEl.style.display = '';
            } else {
                fieldEl.textContent = '';
                fieldEl.style.display = 'none';
            }
        }
    });

    const errEl = document.getElementById('stream-error');
    if (errEl) errEl.style.display = 'none';

    closeStreamSheet();
}

function closeStreamSheet() {
    const sheet = document.getElementById('stream-sheet');
    if (!sheet) return;
    sheet.classList.remove('is-open');
    sheet.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

function validateStream() {
    const stream = document.getElementById('stream-hidden')?.value?.trim();
    const field  = document.getElementById('field-hidden')?.value?.trim();
    if (!stream || !field) {
        const errEl = document.getElementById('stream-error');
        if (errEl) errEl.style.display = 'block';
        showToast('error', 'Please select a stream and domain.');
        return false;
    }
    return true;
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeStreamSheet();
});

function selectedTrackValues() {
    return [...document.querySelectorAll('#mentee-tracks-form input[name="tracks[]"]')]
        .map(i => i.value.trim())
        .filter(Boolean);
}

function toggleCareerGoal(btn) {
    const goal = (btn.dataset.goal || '').trim();
    if (!goal) return;

    const check = btn.querySelector('.mentee-goal-option__check');
    const selected = btn.classList.toggle('is-selected');
    btn.setAttribute('aria-pressed', selected ? 'true' : 'false');

    if (check) check.textContent = selected ? '✓' : '';

    const existing = btn.querySelector('input[name="tracks[]"]');
    if (selected && !existing) {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'tracks[]';
        inp.value = goal;
        btn.appendChild(inp);
    } else if (!selected && existing) {
        existing.remove();
    }
}

function validateTracks() {
    if (selectedTrackValues().length === 0) {
        showToast('error', 'Please select at least one career goal.');
        return false;
    }
    return true;
}
</script>
@endpush