{{-- resources/views/frontend/mentor/profile-edit.blade.php --}}
@extends('frontend.layouts.app')
@section('title', 'Edit Profile — AcharyaSetu Mentor')

@section('content')
<div class="dash-layout">
    <aside class="sidebar">
        <div class="sidebar-section-label">Overview</div>
        <a href="{{ route('mentor.dashboard') }}" class="sidebar-item"><span class="si-icon">📊</span> Dashboard</a>
        <div class="sidebar-section-label">Sessions</div>
        <a href="{{ route('mentor.sessions') }}" class="sidebar-item"><span class="si-icon">📅</span> My Sessions</a>
        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('mentor.wallet') }}" class="sidebar-item"><span class="si-icon">💰</span> Earnings</a>
        <a href="{{ route('mentor.profile.edit') }}" class="sidebar-item active"><span class="si-icon">✏️</span> Edit Profile</a>
        <form action="{{ route('logout') }}" method="POST" style="margin-top:auto;">@csrf<button class="sidebar-item w-full" style="background:none;cursor:pointer;color:var(--error);"><span class="si-icon">🚪</span> Sign Out</button></form>
    </aside>

    <div class="dash-content">
        <div class="dash-header flex-between">
            <div>
                <div class="dash-title">Edit Your Profile</div>
                <div class="dash-subtitle">Keep your profile up to date to attract more mentees.</div>
            </div>
            <a href="/mentors/{{ auth()->user()->id }}" target="_blank" class="btn btn-outline">👁 Preview Profile</a>
        </div>

        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:20px;">
            <span class="alert-icon">❌</span>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top:6px;font-size:12px;padding-left:16px;">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
        @endif

        <form action="{{ route('mentor.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            {{-- ── Section: Photo + Basic ──────────── --}}
            <div class="card" style="margin-bottom:20px;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Basic Information</h3>
                <div style="display:flex;gap:24px;align-items:flex-start;margin-bottom:20px;">
                    <div style="text-align:center;flex-shrink:0;">
                        <div id="avatar-preview" onclick="document.getElementById('avatar-input').click()"
                             style="width:96px;height:96px;border-radius:18px;background:var(--brand-muted);border:3px dashed var(--brand);
                                    display:flex;align-items:center;justify-content:center;font-size:36px;font-weight:800;
                                    color:var(--brand-dark);cursor:pointer;overflow:hidden;font-family:var(--font-head);">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" style="width:100%;height:100%;object-fit:cover;">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </div>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display:none;" onchange="previewImage(this,'#avatar-preview')">
                        <div style="font-size:10px;color:var(--text-3);margin-top:6px;line-height:1.5;">Click to change<br>Max 2MB · JPG/PNG</div>
                    </div>
                    <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="form-group" style="grid-column:1/-1;margin-bottom:0;">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-input" required value="{{ old('name', auth()->user()->name) }}">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Prefer not to say</option>
                                <option value="male" @selected(old('gender',auth()->user()->gender)==='male')>Male</option>
                                <option value="female" @selected(old('gender',auth()->user()->gender)==='female')>Female</option>
                                <option value="other" @selected(old('gender',auth()->user()->gender)==='other')>Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Phone</label>
                            <div class="input-prefix">
                                <span class="input-prefix-label">🇮🇳 +91</span>
                                <input type="tel" name="phone" class="form-input" maxlength="10" value="{{ old('phone', ltrim(auth()->user()->phone??'','+91')) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Bio / About You * <span style="font-weight:400;color:var(--text-3);">(min 80 chars)</span></label>
                    <textarea name="bio" class="form-textarea" rows="5" id="bio-area" required minlength="80" maxlength="2000">{{ old('bio', auth()->user()->bio) }}</textarea>
                    <div style="display:flex;justify-content:space-between;margin-top:4px;">
                        <div class="form-hint">Tell mentees who you are and how you can help them.</div>
                        <div style="font-size:11px;color:var(--text-3);"><span id="bio-count">{{ strlen(auth()->user()->bio??'') }}</span>/2000</div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">LinkedIn URL</label>
                        <input type="url" name="linkedin" class="form-input" placeholder="https://linkedin.com/in/..." value="{{ old('linkedin', auth()->user()->linkedin) }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Twitter / X</label>
                        <input type="url" name="twitter" class="form-input" placeholder="https://twitter.com/..." value="{{ old('twitter', auth()->user()->twitter) }}">
                    </div>
                </div>
            </div>

            {{-- ── Section: Professional ────────────── --}}
            <div class="card" style="margin-bottom:20px;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Professional Details</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Current Designation *</label>
                        <input type="text" name="designation" class="form-input" required placeholder="Senior Product Manager" value="{{ old('designation', auth()->user()->designation) }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Company</label>
                        <input type="text" name="company" class="form-input" placeholder="Google, Microsoft, etc." value="{{ old('company', auth()->user()->company) }}">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Years of Experience *</label>
                        <select name="experience_years" class="form-select" required>
                            @for($i=0;$i<=30;$i++)
                            <option value="{{ $i }}" @selected(old('experience_years',auth()->user()->experience_years)==$i)>{{ $i }}{{ $i==30?'+':'' }} year{{ $i!=1?'s':'' }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Industry</label>
                        <select name="industry" class="form-select">
                            @foreach(['Technology','Finance','Consulting','Healthcare','Education','Startup','Government','Other'] as $ind)
                            <option @selected(old('industry',auth()->user()->industry)===$ind)>{{ $ind }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Section: Expertise ───────────────── --}}
            <div class="card" style="margin-bottom:20px;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Expertise & Skills</h3>
                <p style="font-size:12px;color:var(--text-2);margin-bottom:16px;">These appear as searchable tags on your profile.</p>

                <div id="skills-container" style="display:flex;flex-wrap:wrap;gap:8px;min-height:40px;padding:8px;border:1.5px solid var(--border-2);border-radius:var(--radius-sm);margin-bottom:12px;background:white;">
                    @foreach(auth()->user()->expertise ?? [] as $skill)
                    <span class="skill-tag" style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--brand-muted);border:1px solid rgba(245,158,11,.3);border-radius:999px;font-size:12px;font-weight:600;color:var(--brand-dark);">
                        {{ $skill }}
                        <button type="button" onclick="removeSkillTag(this)" data-skill="{{ $skill }}" style="background:none;color:var(--brand-dark);font-size:14px;cursor:pointer;line-height:1;border:none;">×</button>
                        <input type="hidden" name="expertise[]" value="{{ $skill }}">
                    </span>
                    @endforeach
                    @if(empty(auth()->user()->expertise))
                    <span id="skills-placeholder" style="font-size:12px;color:var(--text-3);padding:4px;">Add your skills below…</span>
                    @endif
                </div>

                <div style="display:flex;gap:8px;margin-bottom:14px;">
                    <input type="text" id="skill-input" class="form-input" placeholder="Type a skill and press Enter or comma…" style="flex:1;">
                    <button type="button" class="btn btn-outline" onclick="addSkillFromInput()">+ Add</button>
                </div>

                <div class="chip-wrap">
                    @foreach(['Product Management','System Design','Data Structures','Leadership','SQL','Python','JavaScript','React','Machine Learning','Finance','Marketing','UI/UX','DevOps','AWS','Consulting','MBA Prep','UPSC','CAT Prep','NEET','JEE','Communication','Resume Review','Interview Prep','Startup','Business Development'] as $chip)
                    <span class="chip" onclick="addSkillDirect('{{ $chip }}')" style="cursor:pointer;">{{ $chip }}</span>
                    @endforeach
                </div>
            </div>

            {{-- ── Section: Pricing ─────────────────── --}}
            <div class="card" style="margin-bottom:20px;">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:4px;">Session Pricing</h3>
                <p style="font-size:12px;color:var(--text-2);margin-bottom:16px;">AcharyaSetu charges a 20% platform fee. You keep 80%.</p>

                <div class="form-group">
                    <label class="form-label">Rate per Minute (₹) *</label>
                    <input type="number" name="rate_per_minute" id="rate-input" class="form-input" required min="5" max="500" step="1"
                           value="{{ old('rate_per_minute', auth()->user()->rate_per_minute ?? 10) }}"
                           style="font-size:20px;font-weight:700;max-width:200px;">
                    <div class="form-hint">Minimum ₹5/min · Suggested ₹10–₹25/min for new mentors</div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:12px;">
                    @foreach([[30,'30 min'],[60,'60 min'],[90,'90 min']] as [$min,$label])
                    <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--radius-sm);padding:14px;text-align:center;">
                        <div style="font-size:12px;color:var(--text-2);margin-bottom:4px;">{{ $label }} session</div>
                        <div style="font-size:18px;font-weight:800;color:var(--brand-dark);" id="preview-{{ $min }}">₹{{ (auth()->user()->rate_per_minute ?? 10) * $min }}</div>
                        <div style="font-size:10px;color:var(--text-3);margin-top:3px;">You earn ₹<span id="net-{{ $min }}">{{ (auth()->user()->rate_per_minute ?? 10) * $min * 0.8 }}</span></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:12px;">
                <a href="{{ route('mentor.dashboard') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Bio counter
const bioArea  = document.getElementById('bio-area');
const bioCount = document.getElementById('bio-count');
if (bioArea) bioArea.addEventListener('input', () => {
    bioCount.textContent = bioArea.value.length;
    bioCount.style.color = bioArea.value.length < 80 ? 'var(--error)' : 'var(--text-3)';
});

// Rate previews
const rateInput = document.getElementById('rate-input');
if (rateInput) {
    rateInput.addEventListener('input', () => {
        const r = parseFloat(rateInput.value) || 0;
        [30,60,90].forEach(m => {
            const pe = document.getElementById(`preview-${m}`); if(pe) pe.textContent = '₹' + (r*m);
            const ne = document.getElementById(`net-${m}`);     if(ne) ne.textContent = (r*m*0.8).toFixed(0);
        });
    });
}

// Skills
function addSkillFromInput() {
    const inp = document.getElementById('skill-input');
    if (!inp) return;
    inp.value.split(',').map(s=>s.trim()).filter(Boolean).forEach(addSkillDirect);
    inp.value = ''; inp.focus();
}
function addSkillDirect(skill) {
    const container = document.getElementById('skills-container');
    const existing  = [...container.querySelectorAll('input[name="expertise[]"]')].map(i=>i.value.toLowerCase());
    if (existing.includes(skill.toLowerCase())) return;
    document.getElementById('skills-placeholder')?.remove();
    const span = document.createElement('span');
    span.className = 'skill-tag';
    span.style.cssText = 'display:inline-flex;align-items:center;gap:6px;padding:5px 12px;background:var(--brand-muted);border:1px solid rgba(245,158,11,.3);border-radius:999px;font-size:12px;font-weight:600;color:var(--brand-dark);';
    span.innerHTML = `${skill}<button type="button" onclick="removeSkillTag(this)" data-skill="${skill}" style="background:none;color:var(--brand-dark);font-size:14px;cursor:pointer;line-height:1;border:none;">×</button><input type="hidden" name="expertise[]" value="${skill}">`;
    container.appendChild(span);
    document.querySelectorAll('.chip').forEach(c => { if(c.textContent.trim()===skill) c.classList.add('selected'); });
}
function removeSkillTag(btn) {
    const skill = btn.dataset.skill;
    btn.closest('.skill-tag').remove();
    document.querySelectorAll('.chip').forEach(c => { if(c.textContent.trim()===skill) c.classList.remove('selected'); });
}
document.getElementById('skill-input')?.addEventListener('keydown', e => {
    if (e.key==='Enter'||e.key===',') { e.preventDefault(); addSkillFromInput(); }
});
// Mark existing chips
document.addEventListener('DOMContentLoaded', () => {
    const added = [...document.querySelectorAll('#skills-container input[name="expertise[]"]')].map(i=>i.value.toLowerCase());
    document.querySelectorAll('.chip').forEach(c => { if(added.includes(c.textContent.trim().toLowerCase())) c.classList.add('selected'); });
});
</script>
@endpush
@endsection