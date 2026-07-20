@php
    $m = optional($mentee);
    $selectedSessionModes = old('session_modes', $preferences['session_modes'] ?? null);
    if ($selectedSessionModes === null && ! empty($preferences['mentoring_format'] ?? null)) {
        $selectedSessionModes = match ($preferences['mentoring_format']) {
            'hybrid' => ['in_person'],
            default  => [$preferences['mentoring_format']],
        };
    }
    $selectedSessionModes = $selectedSessionModes ?? ['video'];
@endphp

{{-- Account --}}
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
        <h3 class="text-sm font-semibold text-gray-800">Account Credentials</h3>
    </div>
    <div class="p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                <input type="text" name="name" value="{{ old('name', $m->name) }}" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                <input type="email" name="email" value="{{ old('email', $m->email) }}" required
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>
            @if($mode === 'create')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                <input type="password" name="password" required placeholder="Min 8 characters"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>
            @else
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password <span class="text-xs text-gray-400 font-normal">(optional)</span></label>
                <input type="password" name="new_password" placeholder="Leave blank to keep current"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $m->phone) }}"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Address / Location <span class="text-red-400">*</span></label>
                <input type="text" name="address" value="{{ old('address', $m->location) }}" required
                       placeholder="City, state or full address"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            </div>
        </div>
        <div class="flex gap-5 items-start">
            @if($mode === 'edit' && $m->avatar_url)
            <img src="{{ $m->avatar_url }}" class="w-16 h-16 rounded-xl object-cover border border-gray-200 flex-shrink-0">
            @endif
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Profile Photo</label>
                <label class="flex items-center gap-3 border-2 border-dashed border-gray-200 hover:border-emerald-300 rounded-xl px-4 py-3 cursor-pointer transition-colors bg-gray-50/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="text-sm text-gray-500" id="avatar-label">Choose photo (optional)</span>
                    <input type="file" name="avatar" accept="image/*" class="hidden" onchange="document.getElementById('avatar-label').textContent = this.files[0]?.name || 'Choose photo'">
                </label>
            </div>
            <div class="w-36">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                <select name="gender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select</option>
                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $v=>$l)
                    <option value="{{ $v }}" {{ old('gender', $m->gender) === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Education --}}
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
        <h3 class="text-sm font-semibold text-gray-800">Education</h3>
    </div>
    <div class="p-6 grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Education Stream <span class="text-red-400">*</span></label>
            <select name="education_stream" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                <option value="">Select stream…</option>
                @foreach($streams as $stream)
                <option value="{{ $stream }}" {{ old('education_stream', $m->education_stream) === $stream ? 'selected' : '' }}>{{ $stream }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Field of Study</label>
            <input type="text" name="field" value="{{ old('field', $m->field) }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">College / University</label>
            <input type="text" name="college" value="{{ old('college', $m->college) }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Year / Batch</label>
            <input type="text" name="year" value="{{ old('year', $m->year) }}"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
        </div>
    </div>
</div>

{{-- Career Tracks --}}
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
        <h3 class="text-sm font-semibold text-gray-800">Career Tracks</h3>
    </div>
    <div class="px-6 py-4">
        <div id="tracks-hidden-inputs">
            @foreach(old('tracks', $tracks ?? []) as $track)
            <input type="hidden" name="tracks[]" value="{{ $track }}" data-onboard-hidden="tracks">
            @endforeach
        </div>
        <div id="tracks-chips" class="flex flex-wrap gap-2 mb-2 empty:hidden">
            @foreach(old('tracks', $tracks ?? []) as $track)
            <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-emerald-100" data-chip-field="tracks" data-chip-value="{{ $track }}">{{ $track }}<button type="button" onclick="removeOnboardChip(this,'tracks')" class="text-emerald-300 hover:text-emerald-700 font-bold leading-none">×</button></span>
            @endforeach
        </div>
        <div class="flex gap-2">
            <input type="text" id="tracks-input" placeholder="Land product role at startup, UPSC 2025… (Enter or comma)"
                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
            <button type="button" onclick="addOnboardChips('tracks-input','tracks-chips','tracks')"
                    class="px-4 text-sm font-semibold text-emerald-600 bg-emerald-50 border border-emerald-100 rounded-xl hover:bg-emerald-100">Add</button>
        </div>
    </div>
</div>

{{-- Goals & Preferences --}}
<div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
        <h3 class="text-sm font-semibold text-gray-800">Goals & Preferences</h3>
    </div>
    <div class="p-6 space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Weekly Time Commitment <span class="text-red-400">*</span></label>
                <select name="weekly_time_commitment" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select…</option>
                    @foreach(['1-3 hours'=>'1–3 hours/week','3-5 hours'=>'3–5 hours/week','5-10 hours'=>'5–10 hours/week','10+ hours'=>'10+ hours/week'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('weekly_time_commitment', $preferences['weekly_time_commitment'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Monthly Budget</label>
                <select name="monthly_budget" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select…</option>
                    @foreach(['under_500'=>'Under ₹500','500-1000'=>'₹500 – ₹1,000','1000-2500'=>'₹1,000 – ₹2,500','2500+'=>'₹2,500+'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('monthly_budget', $preferences['monthly_budget'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Preferred Language <span class="text-red-400">*</span></label>
                <select name="preferred_language" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Select…</option>
                    @foreach(['English','Hindi','Tamil','Telugu','Kannada','Malayalam','Bengali','Marathi'] as $lang)
                    <option value="{{ $lang }}" {{ old('preferred_language', $preferences['preferred_language'] ?? '') === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Session Mode Preference</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['video'=>['📹','Video calls'],'audio'=>['🎙️','Audio only'],'chat'=>['💬','Text / chat'],'in_person'=>['🤝','In-person']] as $val=>[$icon,$label])
                <label class="flex items-center gap-3 px-4 py-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="session_modes[]" value="{{ $val }}"
                           {{ in_array($val, $selectedSessionModes) ? 'checked' : '' }}
                           class="w-4 h-4 rounded text-emerald-600">
                    <span class="text-sm">{{ $icon }}</span>
                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function getChipValue(span) {
    return (span.dataset.chipValue || span.textContent.replace(/×/g, '')).trim();
}
function addOnboardChips(inputId, containerId, fieldName) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(containerId);
    const raw = input.value.trim().replace(/,+$/,'');
    if (!raw) return;
    raw.split(',').map(v => v.trim()).filter(Boolean).forEach(v => {
        const span = document.createElement('span');
        span.className = 'inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-emerald-100';
        span.dataset.chipField = fieldName;
        span.dataset.chipValue = v;
        span.innerHTML = `${v}<button type="button" onclick="removeOnboardChip(this,'${fieldName}')" class="text-emerald-300 hover:text-emerald-700 font-bold leading-none">×</button>`;
        container.appendChild(span);
    });
    input.value = '';
    syncOnboardHidden(fieldName);
    if (typeof updatePreviewMentee === 'function') updatePreviewMentee();
}
function removeOnboardChip(btn, fieldName) {
    btn.closest('span').remove();
    syncOnboardHidden(fieldName);
    if (typeof updatePreviewMentee === 'function') updatePreviewMentee();
}
function getMenteeForm() {
    return document.getElementById('mentee-onboarding-form')
        || document.getElementById('tracks-chips')?.closest('form');
}
function syncOnboardHidden(fieldName) {
    const form = getMenteeForm();
    if (!form) return;

    form.querySelectorAll(`input[data-onboard-hidden="${fieldName}"]`).forEach(i => i.remove());

    const container = form.querySelector('#tracks-hidden-inputs') || form;
    form.querySelectorAll(`[data-chip-field="${fieldName}"]`).forEach(span => {
        const value = getChipValue(span);
        if (!value) return;
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = fieldName + '[]';
        inp.value = value;
        inp.dataset.onboardHidden = fieldName;
        container.appendChild(inp);
    });
}
function flushTracksInput() {
    const input = document.getElementById('tracks-input');
    if (input?.value.trim()) {
        addOnboardChips('tracks-input', 'tracks-chips', 'tracks');
    }
}
document.getElementById('tracks-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addOnboardChips('tracks-input','tracks-chips','tracks'); }
});
getMenteeForm()?.addEventListener('submit', () => {
    flushTracksInput();
    syncOnboardHidden('tracks');
});
syncOnboardHidden('tracks');
</script>
