@extends('admin.layouts.app')
@section('title', 'Edit Mentee — ' . $mentee->name)
@section('heading', 'Edit Mentee Profile')
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
    <a href="{{ route('admin.mentees.show', $mentee) }}" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        {{ $mentee->name }}
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

<form method="POST" action="{{ route('admin.mentee.update', $mentee) }}" enctype="multipart/form-data" class="max-w-5xl">
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
                            <input type="text" name="name" value="{{ old('name', $mentee->name) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $mentee->email) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">New Password <span class="text-xs text-gray-400 font-normal">(leave blank to keep)</span></label>
                            <input type="password" name="new_password" placeholder="••••••••"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $mentee->phone) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all">
                        </div>
                    </div>
                    <div class="flex gap-5 items-start pt-1">
                        <div class="flex-shrink-0">
                            @if($mentee->avatar_url)
                            <img src="{{ $mentee->avatar_url }}" class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                            @else
                            <div class="w-16 h-16 rounded-xl bg-emerald-100 text-emerald-700 font-bold text-xl flex items-center justify-center">{{ strtoupper(substr($mentee->name,0,1)) }}</div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Replace Photo</label>
                            <label class="flex items-center gap-3 border-2 border-dashed border-gray-200 hover:border-emerald-300 rounded-xl px-4 py-3 cursor-pointer transition-colors bg-gray-50/50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-sm text-gray-500" id="avatar-label">Choose new photo (optional)</span>
                                <input type="file" name="avatar" accept="image/*" class="hidden" onchange="document.getElementById('avatar-label').textContent = this.files[0]?.name || 'Choose new photo'">
                            </label>
                        </div>
                        <div class="w-36">
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                            <div class="relative">
                                <select name="gender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 appearance-none cursor-pointer">
                                    <option value="">Select</option>
                                    @foreach(['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $v=>$l)
                                    <option value="{{ $v }}" {{ old('gender', $mentee->gender) === $v ? 'selected' : '' }}>{{ $l }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Education --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Education</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">College / University</label>
                            <input type="text" name="college" value="{{ old('college', $mentee->college) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all placeholder-gray-400" placeholder="IIT Delhi, BITS…">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Year / Batch</label>
                            <input type="text" name="year" value="{{ old('year', $mentee->year) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all placeholder-gray-400" placeholder="3rd Year, 2025…">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Field of Study</label>
                            <input type="text" name="field" value="{{ old('field', $mentee->field) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 transition-all placeholder-gray-400" placeholder="Computer Science…">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Education Stream</label>
                            <div class="relative">
                                <select name="education_stream" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 appearance-none cursor-pointer">
                                    <option value="">Select…</option>
                                    @foreach(['Technology','Business & Management','Design & Arts','Science & Research','Healthcare','Law','Finance','Marketing','Operations','Other'] as $s)
                                    <option value="{{ $s }}" {{ old('education_stream', $mentee->education_stream) === $s ? 'selected' : '' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Goals & Strengths --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Goals & Strengths</h3>
                </div>
                <div class="p-6 space-y-5">
                    @foreach([
                        ['goals-chips','goals-input','career_goals','Career Goals','career_goals','bg-emerald-50 text-emerald-700 border-emerald-100','text-emerald-300 hover:text-emerald-700','emerald'],
                        ['strengths-chips','strengths-input','strengths','Strengths','strengths','bg-blue-50 text-blue-700 border-blue-100','text-blue-300 hover:text-blue-700','blue'],
                    ] as [$cid,$iid,$field,$label,$arr,$chipCls,$btnCls,$color])
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
                        <div id="{{ $cid }}" class="flex flex-wrap gap-2 mb-2 min-h-[32px]">
                            @foreach(old($arr, (array)($mentee->$arr ?? [])) as $item)
                            <span class="inline-flex items-center gap-1.5 {{ $chipCls }} text-xs font-semibold px-3 py-1.5 rounded-full border" data-field-name="{{ $field }}">
                                {{ $item }}<button type="button" onclick="removeMenteeChip(this,'{{ $field }}')" class="{{ $btnCls }} font-bold text-sm leading-none">×</button>
                            </span>
                            @endforeach
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="{{ $iid }}" placeholder="Add {{ strtolower($label) }} (Enter or comma)"
                                   class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-{{ $color }}-400 focus:ring-2 focus:ring-{{ $color }}-100 transition-all placeholder-gray-400">
                            <button type="button" onclick="addMenteeChips('{{ $iid }}','{{ $cid }}','{{ $field }}')"
                                    class="px-4 text-sm font-semibold text-{{ $color }}-600 bg-{{ $color }}-50 border border-{{ $color }}-100 rounded-xl hover:bg-{{ $color }}-100 transition-colors flex-shrink-0">Add</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="space-y-5">

            {{-- Active toggle --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Account Active</p>
                        <p class="text-xs text-gray-400 mt-0.5">Allow login and access</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $mentee->is_active) ? 'checked' : '' }}>
                        <div class="toggle-track"><div class="toggle-thumb"></div></div>
                    </label>
                </div>
            </div>

            {{-- Assignment & Plan --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Assignment & Plan</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assigned Mentor</label>
                        <div class="relative">
                            <select name="assigned_mentor_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 appearance-none cursor-pointer">
                                <option value="">No mentor assigned</option>
                                @foreach($mentors as $mentor)
                                <option value="{{ $mentor->id }}" {{ old('assigned_mentor_id', $mentee->assigned_mentor_id) == $mentor->id ? 'selected' : '' }}>
                                    {{ $mentor->name }} — {{ $mentor->designation ?? $mentor->field ?? 'Mentor' }}
                                </option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subscription Plan</label>
                        <div class="relative">
                            <select name="subscription_plan" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100 appearance-none cursor-pointer">
                                @foreach(['free'=>'Free','basic'=>'Basic','pro'=>'Pro','enterprise'=>'Enterprise'] as $v=>$l)
                                <option value="{{ $v }}" {{ old('subscription_plan', $mentee->subscription_plan ?? 'free') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account meta --}}
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-5">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Account Info</h3>
                <div class="space-y-2 text-xs">
                    @foreach([
                        ['ID',            '#'.$mentee->id],
                        ['Joined',        $mentee->created_at->format('d M Y')],
                        ['Onboarding',    $mentee->onboarding_completed ? 'Complete' : 'Step '.$mentee->onboarding_step.'/4'],
                        ['Sessions',      $mentee->total_sessions],
                        ['Wallet',        '₹'.number_format($mentee->wallet_balance,2)],
                        ['Last Updated',  $mentee->updated_at->format('d M Y')],
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
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('admin.mentees.show', $mentee) }}" class="px-4 py-2.5 text-sm font-medium text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
function addMenteeChips(inputId, containerId, fieldName) {
    const input = document.getElementById(inputId);
    const container = document.getElementById(containerId);
    const raw = input.value.trim().replace(/,+$/,'');
    if (!raw) return;
    const colorMap = { career_goals:'bg-emerald-50 text-emerald-700 border-emerald-100', strengths:'bg-blue-50 text-blue-700 border-blue-100' };
    const btnMap   = { career_goals:'text-emerald-300 hover:text-emerald-700', strengths:'text-blue-300 hover:text-blue-700' };
    raw.split(',').map(v=>v.trim()).filter(Boolean).forEach(v => {
        const span = document.createElement('span');
        span.className = `inline-flex items-center gap-1.5 ${colorMap[fieldName]||'bg-gray-100 text-gray-700'} text-xs font-semibold px-3 py-1.5 rounded-full border`;
        span.dataset.fieldName = fieldName;
        span.innerHTML = `${v}<button type="button" onclick="removeMenteeChip(this,'${fieldName}')" class="${btnMap[fieldName]||''} font-bold text-sm leading-none">×</button>`;
        container.appendChild(span);
    });
    input.value = '';
    syncMenteeField(fieldName);
}
function removeMenteeChip(btn, fieldName) { btn.closest('span').remove(); syncMenteeField(fieldName); }
function syncMenteeField(fieldName) {
    document.querySelectorAll(`input[data-sync-field="${fieldName}"]`).forEach(i=>i.remove());
    document.querySelectorAll(`[data-field-name="${fieldName}"]`).forEach(span => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = fieldName+'[]';
        inp.value = span.childNodes[0].textContent.trim();
        inp.dataset.syncField = fieldName;
        document.querySelector('form').appendChild(inp);
    });
}
['goals-input','strengths-input'].forEach(id => {
    document.getElementById(id)?.addEventListener('keydown', e => {
        if (e.key==='Enter'||e.key===',') {
            e.preventDefault();
            const map = {'goals-input':['goals-chips','career_goals'],'strengths-input':['strengths-chips','strengths']};
            if (map[id]) addMenteeChips(id,...map[id]);
        }
    });
});
// Init hidden inputs from existing chips
['career_goals','strengths'].forEach(syncMenteeField);
</script>

@endsection