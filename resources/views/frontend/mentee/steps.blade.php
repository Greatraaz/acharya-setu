@extends('frontend.layouts.app')
@section('title', 'Set Up Your Profile — Step ' . $step . ' of 4 — Vedrix')

@section('content')
<div style="min-height:100vh; padding:calc(var(--nav-h) + 40px) 16px 60px; background:var(--bg);">
<div style="max-width:620px; margin:0 auto;">

    {{-- ── HEADER ────────────────────────────────────────── --}}
    <div class="text-center" style="margin-bottom:32px;">
        <img src="{{ asset('images/logo.png') }}" alt="Vedrix" style="height:48px;width:auto;max-width:180px;object-fit:contain;margin:0 auto 14px;">
        <h1 style="font-size:22px; font-weight:800; margin-bottom:4px;">Set Up Your Profile</h1>
        <p style="font-size:13px; color:var(--text-2);">Step {{ $step }} of 4 — Takes less than 3 minutes</p>
    </div>

    {{-- ── STEP PROGRESS BAR ──────────────────────────────── --}}
    @php $stepLabels = ['About You', 'Education', 'Tracks', 'Preferences']; @endphp
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
            enctype="multipart/form-data"
            data-ajax-form="{{ route('mentee.onboarding.save1') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 2]) }}"
            data-success="Saved!"
        >
            @csrf

            <div style="display:flex; gap:24px; align-items:flex-start; margin-bottom:8px;">
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
                            {{ strtoupper(substr(auth()->user()->name ?? 'M', 0, 1)) }}
                        @endif
                    </div>
                    <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp,image/jpg" style="display:none;"
                           onchange="previewImage(this, '#avatar-preview')">
                    <div style="font-size:10px; color:var(--text-3); margin-top:6px; line-height:1.4;">Click to<br>upload photo</div>
                </div>
                <div style="flex:1;">
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

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px;">
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

            <div class="form-group">
                <label class="form-label">Field of Study</label>
                <input type="text" name="field" class="form-input"
                       placeholder="Computer Science, Finance, Psychology…"
                       value="{{ old('field', auth()->user()->field) }}">
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
             STEP 3 — Career Tracks
             ════════════════════════════════════════════════ --}}
        @elseif($step == 3)
        @php $selectedTracks = collect($tracks ?? [])->filter()->values(); @endphp
        <h2 style="font-size:19px; font-weight:800; margin-bottom:4px;">Your Career Tracks</h2>
        <p style="font-size:13px; color:var(--text-2); margin-bottom:28px;">Pick the tracks you want help with — same as the app. We'll match you with the best-fit mentors.</p>

        <form
            action="{{ route('mentee.onboarding.save3') }}"
            method="POST"
            id="mentee-tracks-form"
            data-ajax-form="{{ route('mentee.onboarding.save3') }}"
            data-redirect="{{ route('mentee.onboarding', ['step' => 4]) }}"
            data-success="Tracks saved!"
        >
            @csrf

            <div id="tracks-hidden-inputs">
                @foreach($selectedTracks as $track)
                <input type="hidden" name="tracks[]" value="{{ $track }}" data-onboard-hidden="tracks">
                @endforeach
            </div>

            <div class="form-group">
                <label class="form-label">Selected tracks *</label>
                <div id="tracks-chips" style="min-height:48px; padding:12px; background:var(--bg-3); border:1px solid var(--border); border-radius:var(--radius); display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px;">
                    @forelse($selectedTracks as $track)
                    <span class="skill-tag" data-chip-field="tracks" data-chip-value="{{ $track }}"
                          style="display:inline-flex; align-items:center; gap:6px; padding:5px 12px; background:var(--brand-muted); border:1px solid rgba(245,158,11,.3); border-radius:999px; font-size:12px; font-weight:600; color:var(--brand);">
                        {{ $track }}
                        <button type="button" onclick="removeTrackChip(this)" style="background:none; color:var(--brand); font-size:14px; cursor:pointer; line-height:1; padding:0;">×</button>
                    </span>
                    @empty
                    <div id="tracks-placeholder" style="font-size:12px; color:var(--text-3);">No tracks yet. Tap a suggestion or add your own.</div>
                    @endforelse
                </div>
                <div style="display:flex; gap:8px;">
                    <input type="text" id="tracks-input" class="form-input" placeholder="e.g. Frontend Development, UI UX Design">
                    <button type="button" class="btn btn-ghost" onclick="addTrackFromInput()" style="flex-shrink:0;">+ Add</button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Quick add</label>
                <div class="chip-wrap">
                    @foreach(($trackSuggestions ?? collect(['Frontend Development','UI UX Design','Data Science','Product Management','MBA Prep','DSA & Algorithms','Career Switch','Startup Advice'])) as $opt)
                    @php $optName = is_object($opt) ? $opt->name : $opt; @endphp
                    <div class="chip {{ $selectedTracks->contains($optName) ? 'selected' : '' }}" onclick="addTrackDirect('{{ addslashes($optName) }}')">{{ $optName }}</div>
                    @endforeach
                    @foreach(['Cracking FAANG / Tech Interviews','Getting My First Job','Study Abroad / Masters Abroad'] as $extra)
                    <div class="chip {{ $selectedTracks->contains($extra) ? 'selected' : '' }}" onclick="addTrackDirect('{{ addslashes($extra) }}')">{{ $extra }}</div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex; gap:12px;">
                <a href="{{ route('mentee.onboarding', ['step' => 2]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;" onclick="return validateTracks()">Continue →</button>
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

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
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

            <div style="display:flex; gap:12px;">
                <a href="{{ route('mentee.onboarding', ['step' => 3]) }}" class="btn btn-ghost" style="flex-shrink:0;">← Back</a>
                <button type="submit" class="btn btn-primary" style="flex:1;">Finish & Go to Dashboard →</button>
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

function selectedTrackValues() {
    return [...document.querySelectorAll('#tracks-hidden-inputs input[name="tracks[]"]')]
        .map(i => i.value.trim())
        .filter(Boolean);
}

function syncTrackChips() {
    const container = document.getElementById('tracks-hidden-inputs');
    if (!container) return;
    container.innerHTML = '';
    document.querySelectorAll('#tracks-chips [data-chip-field="tracks"]').forEach(span => {
        const value = (span.dataset.chipValue || '').trim();
        if (!value) return;
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'tracks[]';
        inp.value = value;
        inp.dataset.onboardHidden = 'tracks';
        container.appendChild(inp);
    });
}

function addTrackDirect(track) {
    const name = (track || '').trim();
    if (!name) return;
    const existing = selectedTrackValues().map(v => v.toLowerCase());
    if (existing.includes(name.toLowerCase())) return;

    const chips = document.getElementById('tracks-chips');
    document.getElementById('tracks-placeholder')?.remove();
    const span = document.createElement('span');
    span.className = 'skill-tag';
    span.dataset.chipField = 'tracks';
    span.dataset.chipValue = name;
    span.style.cssText = 'display:inline-flex; align-items:center; gap:6px; padding:5px 12px; background:var(--brand-muted); border:1px solid rgba(245,158,11,.3); border-radius:999px; font-size:12px; font-weight:600; color:var(--brand);';
    span.innerHTML = `${name}<button type="button" onclick="removeTrackChip(this)" style="background:none; color:var(--brand); font-size:14px; cursor:pointer; line-height:1; padding:0;">×</button>`;
    chips.appendChild(span);
    syncTrackChips();
    document.querySelectorAll('.chip').forEach(c => {
        if (c.textContent.trim().toLowerCase() === name.toLowerCase()) c.classList.add('selected');
    });
}

function addTrackFromInput() {
    const input = document.getElementById('tracks-input');
    if (!input) return;
    const raw = input.value.trim().replace(/,+$/, '');
    if (!raw) return;
    raw.split(',').map(v => v.trim()).filter(Boolean).forEach(addTrackDirect);
    input.value = '';
}

function removeTrackChip(btn) {
    const span = btn.closest('[data-chip-field="tracks"]');
    const name = span?.dataset.chipValue || '';
    span?.remove();
    syncTrackChips();
    document.querySelectorAll('.chip').forEach(c => {
        if (c.textContent.trim().toLowerCase() === name.toLowerCase()) c.classList.remove('selected');
    });
    const chips = document.getElementById('tracks-chips');
    if (chips && !chips.querySelector('[data-chip-field="tracks"]') && !document.getElementById('tracks-placeholder')) {
        chips.innerHTML = '<div id="tracks-placeholder" style="font-size:12px; color:var(--text-3);">No tracks yet. Tap a suggestion or add your own.</div>';
    }
}

function validateTracks() {
    addTrackFromInput();
    syncTrackChips();
    if (selectedTrackValues().length === 0) {
        showToast('error', 'Please select at least one track.');
        return false;
    }
    return true;
}

document.getElementById('tracks-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addTrackFromInput(); }
});
document.getElementById('mentee-tracks-form')?.addEventListener('submit', () => {
    addTrackFromInput();
    syncTrackChips();
});

document.addEventListener('DOMContentLoaded', () => {
    const streamVal = document.getElementById('stream-hidden')?.value;
    if (streamVal) {
        document.querySelectorAll('.stream-option').forEach(c => {
            if (c.dataset.stream === streamVal) {
                c.style.borderColor = 'var(--brand)';
                c.style.background  = 'var(--brand-muted)';
            }
        });
    }
});
</script>
@endpush