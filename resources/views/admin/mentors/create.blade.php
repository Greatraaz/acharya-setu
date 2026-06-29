@extends('admin.layouts.app')
@section('title', 'Create Mentor')
@section('heading', 'Create Mentor Account')
@section('content')

<style>
    .toggle-switch { position:relative; display:inline-flex; align-items:center; cursor:pointer; }
    .toggle-switch input { display:none; }
    .toggle-track { width:44px; height:24px; background:#d1d5db; border-radius:9999px; transition:background .2s; position:relative; flex-shrink:0; }
    .toggle-switch input:checked + .toggle-track { background:#2563eb; }
    .toggle-thumb { position:absolute; top:3px; left:3px; width:18px; height:18px; background:white; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.18); }
    .toggle-switch input:checked ~ .toggle-track .toggle-thumb { transform:translateX(20px); }
    .section-label { font-size:10px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#94a3b8; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
    .section-label::after { content:''; flex:1; height:1px; background:#f1f5f9; }
</style>

{{-- Back --}}
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.mentors.index') }}" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Mentors
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-600 font-medium">Create Mentor</span>
</div>

@if($errors->any())
<div class="flex gap-3 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
    <ul class="text-sm text-red-700 space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.mentor.store') }}" enctype="multipart/form-data" class="max-w-5xl">
    @csrf

    <div class="grid grid-cols-3 gap-6">

        {{-- ════ LEFT 2/3 ════ --}}
        <div class="col-span-2 space-y-6">

            {{-- Account Credentials --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Account Credentials</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Dr. Priya Sharma"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address <span class="text-red-400">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="mentor@example.com"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                            <input type="password" name="password" placeholder="Min 8 characters"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                    </div>

                    {{-- Avatar + Gender --}}
                    <div class="flex gap-5 items-start pt-1">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Profile Photo</label>
                            <label class="flex items-center gap-3 border-2 border-dashed border-gray-200 hover:border-indigo-300 rounded-xl px-4 py-3 cursor-pointer transition-colors bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-sm text-gray-500" id="avatar-label">Choose photo (JPG/PNG, max 2MB)</span>
                                <input type="file" name="avatar" accept="image/*" class="hidden" onchange="document.getElementById('avatar-label').textContent = this.files[0]?.name || 'Choose photo'">
                            </label>
                        </div>
                        <div class="w-36">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                            <div class="relative">
                                <select name="gender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 appearance-none cursor-pointer">
                                    <option value="">Select</option>
                                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $v=>$l)
                                    <option value="{{ $v }}" {{ old('gender') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Professional Info --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Professional Background</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Designation <span class="text-red-400">*</span></label>
                            <input type="text" name="designation" value="{{ old('designation') }}" placeholder="Senior Product Manager"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Company</label>
                            <input type="text" name="company" value="{{ old('company') }}" placeholder="Google, TATA…"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Experience (years) <span class="text-red-400">*</span></label>
                            <input type="number" name="experience_years" value="{{ old('experience_years', 0) }}" min="0" max="50"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">LinkedIn URL</label>
                            <input type="url" name="linkedin" value="{{ old('linkedin') }}" placeholder="https://linkedin.com/in/…"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Expertise --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Expertise & Bio</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Primary Field <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <select name="field" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 appearance-none cursor-pointer">
                                <option value="">Select primary domain…</option>
                                @foreach(['Software Engineering','Product Management','Data Science','Design (UI/UX)','Marketing','Sales','Finance','Operations','HR','Legal','Entrepreneurship','Research','Other'] as $f)
                                <option value="{{ $f }}" {{ old('field') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Skills / Expertise Tags <span class="text-red-400">*</span></label>
                        <div id="expertise-chips" class="flex flex-wrap gap-2 mb-2 min-h-[32px]">
                            {{-- Expertise chips (old) --}}
                            @foreach(old('expertise', []) as $sk)
                            <span
                                class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-indigo-100"
                                data-value="{{ $sk }}"
                                data-field-name="expertise"
                            >
                                {{ $sk }}
                                <button type="button" onclick="removeChip(this,'expertise')"
                                    class="text-indigo-300 hover:text-indigo-700 font-bold text-sm leading-none">×</button>
                            </span>
                            {{-- ✅ hidden input directly in blade, NOT relying on syncHiddenInputs --}}
                            <input type="hidden" name="expertise[]" value="{{ $sk }}" data-field="expertise">
                            @endforeach
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="expertise-input" placeholder="Laravel, Product Strategy… (Enter or comma)"
                                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                            <button type="button" onclick="addChips('expertise-input','expertise-chips','expertise')"
                                    class="px-4 text-sm font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-xl hover:bg-indigo-100 transition-colors flex-shrink-0">Add</button>
                        </div>
                        <div id="expertise-hidden-container"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Bio <span class="text-red-400">*</span></label>
                        <textarea name="bio" rows="5" minlength="30" maxlength="1000"
                                  placeholder="A compelling bio — their journey, passion, and how they help mentees. Min 30 characters."
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400 resize-none leading-relaxed">{{ old('bio') }}</textarea>
                        <div class="flex justify-end"><span id="bio-count" class="text-xs text-gray-400 mt-1">0 / 1000</span></div>
                    </div>
                </div>
            </div>

            {{-- Rates --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Rates & Session Preferences</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-end gap-4">
                        <div class="w-48">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Rate per minute <span class="text-red-400">*</span></label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium select-none pointer-events-none">₹</span>
                                <input type="number" name="rate_per_minute" id="rate-input"
                                       value="{{ old('rate_per_minute', 10) }}" step="0.5" min="0"
                                       class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 pb-2.5">
                            = <span id="hourly-rate" class="font-semibold text-gray-800">₹600</span>/hour
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Session Modes</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['video'=>['📹','Video calls'],'audio'=>['🎙️','Audio only'],'chat'=>['💬','Text / chat'],'in_person'=>['🤝','In-person']] as $val=>[$icon,$label])
                            <label class="flex items-center gap-3 px-4 py-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="preferences[]" value="{{ $val }}"
                                       {{ in_array($val, old('preferences', ['video'])) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-indigo-600">
                                <span class="text-sm">{{ $icon }}</span>
                                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════ RIGHT SIDEBAR ════ --}}
        <div class="space-y-5">

            {{-- Approval status --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Account Status</h3>
                </div>
                <div class="p-5 space-y-3">
                {{-- Account Status radio cards --}}
                    @foreach([
                        ['approved', 'Approved',      'Mentor is live immediately',    'green'],
                        ['pending',  'Pending Review','Submitted but awaiting review', 'amber'],
                        ['rejected', 'Rejected',      'Application rejected',          'red'],
                    ] as [$val, $label, $desc, $color])

                    @php
                        $isSelected = old('mentor_status', 'approved') === $val;
                        $selectedCls = "bg-{$color}-50 border-{$color}-400 ring-1 ring-{$color}-300";
                        $defaultCls  = 'border-gray-200 hover:bg-gray-50';
                    @endphp

                    <label data-radio-card
                        class="flex items-start gap-3 px-4 py-3 border-2 rounded-xl cursor-pointer transition-all
                            {{ $isSelected ? $selectedCls : $defaultCls }}">
                        <input type="radio" name="mentor_status" value="{{ $val }}"
                            {{ $isSelected ? 'checked' : '' }}
                            class="sr-only">
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5
                                    {{ $isSelected ? "bg-{$color}-500 border-{$color}-500" : 'border-gray-300 bg-white' }}">
                            @if($isSelected)
                                <div class="w-2 h-2 rounded-full bg-white"></div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                        </div>
                    </label>

                    @endforeach
                </div>
            </div>

            {{-- Preview --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Live Preview</h3>
                </div>
                <div class="p-5">
                    <div class="bg-gradient-to-br from-indigo-50 to-white border border-indigo-100 rounded-xl p-4 space-y-2.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center flex-shrink-0" id="prev-avatar">M</div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 truncate" id="prev-name">Mentor Name</p>
                                <p class="text-[10px] text-gray-400" id="prev-designation">Designation · Company</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1" id="prev-expertise-badges"></div>
                        <div class="text-[10px] text-emerald-700 font-bold" id="prev-rate">₹10/min · ₹600/hr</div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5 flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Create Mentor
                </button>
                <a href="{{ route('admin.mentors.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
// ── Chip System ───────────────────────────────────────────────
function addChips(inputId, containerId, fieldName) {
    const input     = document.getElementById(inputId);
    const container = document.getElementById(containerId);
    const raw       = input.value.trim().replace(/,+$/, '');
    if (!raw) return;

    raw.split(',').map(v => v.trim()).filter(Boolean).forEach(value => {
        const existing = [...container.querySelectorAll('span[data-value]')].map(s => s.dataset.value);
        if (existing.includes(value)) return;

        const span             = document.createElement('span');
        span.className         = 'inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-indigo-100';
        span.dataset.value     = value;
        span.dataset.fieldName = fieldName;
        span.innerHTML         = `${value}<button type="button" onclick="removeChip(this,'${fieldName}')" class="text-indigo-300 hover:text-indigo-700 font-bold text-sm leading-none ml-1">×</button><input type="hidden" name="expertise[]" value="${value}" data-field="expertise">`;
        container.appendChild(span);

        // ✅ Add hidden input immediately when chip is created
        const hidden           = document.createElement('input');
        hidden.type            = 'hidden';
        hidden.name            = `${fieldName}[]`;
        hidden.value           = value;
        hidden.dataset.field   = fieldName;
        document.querySelector('form').appendChild(hidden);
    });

    input.value = '';
    updatePreview();
}

function removeChip(btn, fieldName) {
    btn.closest('span').remove();
    syncHiddenInputs(fieldName);
    updatePreview();
}

function syncHiddenInputs(fieldName) {
    // Remove old hidden inputs
    document.querySelectorAll(`input[data-field="${fieldName}"]`).forEach(i => i.remove());

    // Re-create from chips
    const container = document.getElementById(`${fieldName}-chips`);
    container.querySelectorAll('span[data-field-name]').forEach(span => {
        const input       = document.createElement('input');
        input.type        = 'hidden';
        input.name        = `${fieldName}[]`;
        input.value       = span.dataset.value;
        input.dataset.field = fieldName;
        document.querySelector('form').appendChild(input);
    });
}

// Enter / comma key trigger
document.getElementById('expertise-input')?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addChips('expertise-input', 'expertise-chips', 'expertise');
    }
});

// ── On page load: sync any pre-filled chips (edit page) ───────
document.addEventListener('DOMContentLoaded', function () {
    syncHiddenInputs('expertise');
    syncHiddenInputs('preferences');
});

// ── Validate expertise before submit ──────────────────────────
document.querySelector('form').addEventListener('submit', function (e) {
    syncHiddenInputs('expertise');   // force sync on submit

    const chips = document.querySelectorAll('#expertise-chips span[data-field-name]');
    if (chips.length === 0) {
        e.preventDefault();
        alert('Please add at least one expertise tag.');
        document.getElementById('expertise-input').focus();
    }
});

// ── Bio counter ───────────────────────────────────────────────
const bioEl  = document.querySelector('[name=bio]');
const bioCnt = document.getElementById('bio-count');
if (bioEl) {
    bioCnt.textContent = bioEl.value.length + ' / 1000';
    bioEl.addEventListener('input', () => bioCnt.textContent = bioEl.value.length + ' / 1000');
}

// ── Rate → hourly ─────────────────────────────────────────────
const rateEl   = document.getElementById('rate-input');
const hourlyEl = document.getElementById('hourly-rate');
if (rateEl) {
    rateEl.addEventListener('input', () => {
        const v    = parseFloat(rateEl.value) || 0;
        hourlyEl.textContent = v === 0 ? 'Free' : '₹' + (v * 60).toLocaleString('en-IN');
    });
}

// ── Live Preview ──────────────────────────────────────────────
function updatePreview() {
    const name  = document.querySelector('[name=name]')?.value         || 'Mentor Name';
    const des   = document.querySelector('[name=designation]')?.value  || 'Designation';
    const comp  = document.querySelector('[name=company]')?.value      || '';
    const rate  = parseFloat(document.querySelector('[name=rate_per_minute]')?.value) || 0;
    const chips = [...document.querySelectorAll('#expertise-chips span[data-field-name]')]
                    .map(s => s.dataset.value).slice(0, 4);

    document.getElementById('prev-avatar').textContent      = (name[0] || 'M').toUpperCase();
    document.getElementById('prev-name').textContent        = name;
    document.getElementById('prev-designation').textContent = des + (comp ? ' · ' + comp : '');
    document.getElementById('prev-rate').textContent        = rate === 0 ? 'Free' : `₹${rate}/min · ₹${rate * 60}/hr`;
    document.getElementById('prev-expertise-badges').innerHTML = chips
        .map(c => `<span class="text-[10px] font-medium bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">${c}</span>`)
        .join('');
}

document.querySelectorAll('[name=name],[name=designation],[name=company],[name=rate_per_minute]')
    .forEach(el => el.addEventListener('input', updatePreview));

// ── Radio card highlight ──────────────────────────────────────
document.querySelectorAll('[data-radio-card] input[type=radio]').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('[data-radio-card]').forEach(card => {
            card.classList.remove(
                'bg-green-50','border-green-400','ring-1','ring-green-300',
                'bg-amber-50','border-amber-400','ring-amber-300',
                'bg-red-50','border-red-400','ring-red-300',
            );
            card.classList.add('border-gray-200');
            const dot = card.querySelector('.radio-dot');
            if (dot) { dot.className = 'radio-dot w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5 border-gray-300 bg-white'; dot.innerHTML = ''; }
        });

        const card     = this.closest('[data-radio-card]');
        const colorMap = {
            approved: { card: ['bg-green-50','border-green-400','ring-1','ring-green-300'], dot: 'bg-green-500 border-green-500' },
            pending:  { card: ['bg-amber-50','border-amber-400','ring-1','ring-amber-300'], dot: 'bg-amber-400 border-amber-400' },
            rejected: { card: ['bg-red-50','border-red-400','ring-1','ring-red-300'],       dot: 'bg-red-500 border-red-500' },
        };
        const cfg = colorMap[this.value];
        card.classList.remove('border-gray-200');
        cfg.card.forEach(c => card.classList.add(c));

        const dot = card.querySelector('.radio-dot');
        if (dot) {
            dot.className = `radio-dot w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5 ${cfg.dot}`;
            dot.innerHTML = '<div class="w-2 h-2 rounded-full bg-white"></div>';
        }
    });
});

// ── Radio card highlight ──────────────────────────────────────
document.querySelectorAll('[data-radio-card] input[type=radio]').forEach(radio => {
    radio.addEventListener('change', function () {
        // Reset all cards
        document.querySelectorAll('[data-radio-card]').forEach(card => {
            card.classList.remove(
                'bg-green-50','border-green-400','ring-1','ring-green-300',
                'bg-amber-50','border-amber-400','ring-amber-300',
                'bg-red-50','border-red-400','ring-red-300',
            );
            card.classList.add('border-gray-200');

            // Reset dot
            const dot = card.querySelector('.rounded-full.border-2');
            dot.className = 'w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5 border-gray-300 bg-white';
            dot.innerHTML = '';
        });

        // Highlight selected card
        const card  = this.closest('[data-radio-card]');
        const value = this.value;

        const colorMap = {
            approved: { card: 'bg-green-50 border-green-400 ring-1 ring-green-300',  dot: 'bg-green-500 border-green-500' },
            pending:  { card: 'bg-amber-50 border-amber-400 ring-1 ring-amber-300',  dot: 'bg-amber-400 border-amber-400' },
            rejected: { card: 'bg-red-50 border-red-400 ring-1 ring-red-300',        dot: 'bg-red-500 border-red-500' },
        };

        const cfg = colorMap[value];
        card.classList.remove('border-gray-200');
        cfg.card.split(' ').forEach(c => card.classList.add(c));

        const dot = card.querySelector('.rounded-full.border-2');
        dot.className = `w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5 ${cfg.dot}`;
        dot.innerHTML = '<div class="w-2 h-2 rounded-full bg-white"></div>';
    });
});
</script>

@endsection