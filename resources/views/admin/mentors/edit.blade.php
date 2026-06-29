@extends('admin.layouts.app')
@section('title', 'Edit Mentor — ' . $mentor->name)
@section('heading', 'Edit Mentor Profile')
@section('content')

<style>
    .toggle-switch{position:relative;display:inline-flex;align-items:center;cursor:pointer;}
    .toggle-switch input{display:none;}
    .toggle-track{width:44px;height:24px;background:#d1d5db;border-radius:9999px;transition:background .2s;position:relative;flex-shrink:0;}
    .toggle-switch input:checked + .toggle-track{background:#2563eb;}
    .toggle-thumb{position:absolute;top:3px;left:3px;width:18px;height:18px;background:white;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.18);}
    .toggle-switch input:checked ~ .toggle-track .toggle-thumb{transform:translateX(20px);}
</style>

<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.mentors.review', $mentor) }}" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        {{ $mentor->name }}
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-600 font-medium">Edit Profile</span>
</div>

@if($errors->any())
<div class="flex gap-3 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
    <ul class="text-sm text-red-700 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.mentor.update', $mentor) }}" enctype="multipart/form-data" class="max-w-5xl">
    @csrf @method('PUT')

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-5">

            {{-- Account --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Account Details</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name <span class="text-red-400">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $mentor->name) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $mentor->email) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                New Password
                                <span class="text-xs text-gray-400 font-normal ml-1">(leave blank to keep current)</span>
                            </label>
                            <input type="password" name="new_password" placeholder="••••••••"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $mentor->phone) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                    </div>

                    <div class="flex gap-5 items-start pt-1">
                        {{-- Current avatar --}}
                        <div class="flex-shrink-0">
                            @if($mentor->avatar_url)
                            <img src="{{ $mentor->avatar_url }}" class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                            @else
                            <div class="w-16 h-16 rounded-xl bg-indigo-100 text-indigo-700 font-bold text-xl flex items-center justify-center">{{ strtoupper(substr($mentor->name,0,1)) }}</div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Replace Photo</label>
                            <label class="flex items-center gap-3 border-2 border-dashed border-gray-200 hover:border-indigo-300 rounded-xl px-4 py-3 cursor-pointer transition-colors bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-sm text-gray-500" id="avatar-label">Choose new photo (optional)</span>
                                <input type="file" name="avatar" accept="image/*" class="hidden" onchange="document.getElementById('avatar-label').textContent = this.files[0]?.name || 'Choose new photo'">
                            </label>
                        </div>
                        <div class="w-36">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                            <div class="relative">
                                <select name="gender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 appearance-none cursor-pointer">
                                    <option value="">Select</option>
                                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $v=>$l)
                                    <option value="{{ $v }}" {{ old('gender', $mentor->gender) === $v ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Professional --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Professional Background</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Designation <span class="text-red-400">*</span></label>
                            <input type="text" name="designation" value="{{ old('designation', $mentor->designation) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Company</label>
                            <input type="text" name="company" value="{{ old('company', $mentor->company) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Experience (years) <span class="text-red-400">*</span></label>
                            <input type="number" name="experience_years" value="{{ old('experience_years', $mentor->experience_years) }}" min="0" max="50"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">LinkedIn URL</label>
                            <input type="url" name="linkedin" value="{{ old('linkedin', $mentor->linkedin) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400" placeholder="https://linkedin.com/in/…">
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
                                @foreach(['Software Engineering','Product Management','Data Science','Design (UI/UX)','Marketing','Sales','Finance','Operations','HR','Legal','Entrepreneurship','Research','Other'] as $f)
                                <option value="{{ $f }}" {{ old('field', $mentor->field) === $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Skills / Expertise Tags</label>
                        <div id="expertise-chips" class="flex flex-wrap gap-2 mb-2 min-h-[32px]">
                            @foreach(old('expertise', $mentor->expertise ?? []) as $sk)
                            <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-indigo-100" data-field-name="expertise">
                                {{ $sk }}<button type="button" onclick="removeChip(this,'expertise')" class="text-indigo-300 hover:text-indigo-700 font-bold text-sm leading-none">×</button>
                            </span>
                            @endforeach
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="expertise-input" placeholder="Add skill (Enter or comma)"
                                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all placeholder-gray-400">
                            <button type="button" onclick="addChips('expertise-input','expertise-chips','expertise')"
                                    class="px-4 text-sm font-semibold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-xl hover:bg-indigo-100 transition-colors flex-shrink-0">Add</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Bio <span class="text-red-400">*</span></label>
                        <textarea name="bio" rows="5" minlength="30" maxlength="1000"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all resize-none leading-relaxed">{{ old('bio', $mentor->bio) }}</textarea>
                        <div class="flex justify-end"><span id="bio-count" class="text-xs text-gray-400 mt-1">{{ strlen($mentor->bio ?? '') }} / 1000</span></div>
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
                                       value="{{ old('rate_per_minute', $mentor->rate_per_minute) }}" step="0.5" min="0"
                                       class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 pb-2.5">= <span id="hourly-rate" class="font-semibold text-gray-800">₹{{ $mentor->rate_per_minute * 60 }}</span>/hour</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Session Modes</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['video'=>['📹','Video calls'],'audio'=>['🎙️','Audio only'],'chat'=>['💬','Text / chat'],'in_person'=>['🤝','In-person']] as $val=>[$icon,$label])
                            <label class="flex items-center gap-3 px-4 py-2.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="checkbox" name="preferences[]" value="{{ $val }}"
                                       {{ in_array($val, old('preferences', (array)($mentor->preferences ?? ['video']))) ? 'checked' : '' }}
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

        {{-- RIGHT SIDEBAR --}}
        <div class="space-y-5">

            {{-- Status --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Approval Status</h3>
                </div>
                <div class="p-4 space-y-2">
                    @foreach(['approved'=>['bg-green-50 border-green-200','text-green-700','Approved','Live immediately'],'pending'=>['bg-amber-50 border-amber-200','text-amber-700','Pending','Awaiting review'],'rejected'=>['bg-red-50 border-red-200','text-red-700','Rejected','Application rejected'],'suspended'=>['bg-gray-100 border-gray-300','text-gray-600','Suspended','Temporarily blocked']] as $val=>[$bg,$tc,$label,$desc])
                    <label class="flex items-start gap-3 px-3 py-2.5 border-2 rounded-xl cursor-pointer transition-all {{ old('mentor_status', $mentor->mentor_status) === $val ? $bg.' border-current' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input type="radio" name="mentor_status" value="{{ $val }}" {{ old('mentor_status', $mentor->mentor_status) === $val ? 'checked' : '' }} class="mt-0.5 text-indigo-600">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                            <p class="text-xs text-gray-400">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Active toggle --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Account Active</p>
                        <p class="text-xs text-gray-400 mt-0.5">Allow login and listing visibility</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $mentor->is_active) ? 'checked' : '' }}>
                        <div class="toggle-track"><div class="toggle-thumb"></div></div>
                    </label>
                </div>
            </div>

            {{-- Meta info --}}
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Account Info</h3>
                <div class="space-y-2 text-xs">
                    @foreach([
                        ['ID',            '#'.$mentor->id],
                        ['Joined',        $mentor->created_at->format('d M Y')],
                        ['Total Sessions',$mentor->total_sessions],
                        ['Rating',        $mentor->rating > 0 ? '★ '.number_format($mentor->rating,1) : '—'],
                        ['Wallet',        '₹'.number_format($mentor->wallet_balance,2)],
                        ['Plan',          ucfirst($mentor->subscription_plan ?? 'free')],
                    ] as [$l,$v])
                    <div class="flex justify-between py-1.5 border-b border-gray-100 last:border-0">
                        <span class="text-gray-400">{{ $l }}</span>
                        <span class="text-gray-700 font-semibold font-mono">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5 flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('admin.mentors.review', $mentor) }}" class="px-4 py-2.5 text-sm font-medium text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
const bioEl = document.querySelector('[name=bio]');
const bioCnt = document.getElementById('bio-count');
bioEl?.addEventListener('input', () => bioCnt.textContent = bioEl.value.length + ' / 1000');

const rateEl = document.getElementById('rate-input');
const hourlyEl = document.getElementById('hourly-rate');
rateEl?.addEventListener('input', () => {
    const v = parseFloat(rateEl.value) || 0;
    hourlyEl.textContent = v === 0 ? 'Free' : '₹' + (v * 60).toLocaleString('en-IN');
});

function addChips(inputId, containerId, fieldName) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(containerId);
    const raw = input.value.trim().replace(/,+$/, '');
    if (!raw) return;
    raw.split(',').map(v => v.trim()).filter(Boolean).forEach(v => {
        const span = document.createElement('span');
        span.className = 'inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-indigo-100';
        span.dataset.fieldName = fieldName;
        span.innerHTML = `${v}<button type="button" onclick="removeChip(this,'${fieldName}')" class="text-indigo-300 hover:text-indigo-700 font-bold text-sm leading-none">×</button>`;
        container.appendChild(span);
    });
    input.value = '';
    syncHiddenInputs(fieldName);
}
function removeChip(btn, fieldName) { btn.closest('span').remove(); syncHiddenInputs(fieldName); }
function syncHiddenInputs(fieldName) {
    document.querySelectorAll(`input[data-field="${fieldName}"]`).forEach(i => i.remove());
    document.querySelectorAll(`[data-field-name="${fieldName}"]`).forEach(span => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = fieldName + '[]';
        inp.value = span.childNodes[0].textContent.trim();
        inp.dataset.field = fieldName;
        document.querySelector('form').appendChild(inp);
    });
}
document.getElementById('expertise-input')?.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addChips('expertise-input','expertise-chips','expertise'); }
});
// Init existing chips
document.querySelectorAll('#expertise-chips span').forEach(s => s.dataset.fieldName = 'expertise');
syncHiddenInputs('expertise');
</script>

@endsection