@extends('admin.layouts.app')
@section('title', 'Create Mentee')
@section('heading', 'Create Mentee Account')
@section('content')

<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.mentees.index') }}" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Mentees
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-600 font-medium">Create Mentee</span>
</div>

@if($errors->any())
<div class="flex gap-3 bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6">
    <ul class="text-sm text-red-700 space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.mentee.store') }}" enctype="multipart/form-data" class="max-w-5xl" id="mentee-onboarding-form">
    @csrf
    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-5">
            @include('admin.mentees._onboarding-fields', [
                'mode' => 'create',
                'mentee' => null,
                'streams' => $streams,
                'tracks' => old('tracks', []),
                'preferences' => [],
            ])
        </div>

        <div class="space-y-5">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Assignment & Plan</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign Mentor</label>
                        <select name="assigned_mentor_id" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                            <option value="">No mentor assigned</option>
                            @foreach($mentors as $mentor)
                            <option value="{{ $mentor->id }}" {{ old('assigned_mentor_id') == $mentor->id ? 'selected' : '' }}>
                                {{ $mentor->name }} — {{ $mentor->designation ?? $mentor->field ?? 'Mentor' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="hidden" name="auto_assign_mentor" value="0">
                        <input type="checkbox" name="auto_assign_mentor" value="1" {{ old('auto_assign_mentor', '1') ? 'checked' : '' }} class="rounded text-emerald-600">
                        Auto-assign mentor if none selected
                    </label>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subscription Plan</label>
                        <select name="subscription_plan" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                            @foreach(['free'=>'Free','basic'=>'Basic','pro'=>'Pro','enterprise'=>'Enterprise'] as $v=>$l)
                            <option value="{{ $v }}" {{ old('subscription_plan','free') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Preview --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Preview Card</h3>
                </div>
                <div class="p-5">
                    <div class="bg-gradient-to-br from-emerald-50 to-white border border-emerald-100 rounded-xl p-4 space-y-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm flex items-center justify-center flex-shrink-0" id="prev-avatar-m">M</div>
                            <div>
                                <p class="text-xs font-bold text-gray-900" id="prev-name-m">Mentee Name</p>
                                <p class="text-[10px] text-gray-400" id="prev-edu-m">College · Year</p>
                            </div>
                        </div>
                        <div class="text-[10px] text-gray-500" id="prev-field-m">Field of study</div>
                        <div class="flex flex-wrap gap-1 mt-1" id="prev-goals-badges"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 flex gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Create Mentee
                </button>
                <a href="{{ route('admin.mentees.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

<script>
function updatePreviewMentee() {
    const name    = document.querySelector('[name=name]')?.value || 'Mentee Name';
    const college = document.querySelector('[name=college]')?.value || 'College';
    const year    = document.querySelector('[name=year]')?.value || 'Year';
    const field   = document.querySelector('[name=field]')?.value || 'Field of study';
    const tracks  = [...document.querySelectorAll('#tracks-chips span')].map(s => s.childNodes[0].textContent.trim()).slice(0, 3);

    document.getElementById('prev-avatar-m').textContent = (name[0] || 'M').toUpperCase();
    document.getElementById('prev-name-m').textContent = name;
    document.getElementById('prev-edu-m').textContent = [college, year].filter(Boolean).join(' · ');
    document.getElementById('prev-field-m').textContent = field;
    document.getElementById('prev-goals-badges').innerHTML = tracks
        .map(g => `<span class="text-[10px] font-medium bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">${g}</span>`)
        .join('');
}
document.querySelectorAll('[name=name],[name=college],[name=year],[name=field]')
    .forEach(el => el.addEventListener('input', updatePreviewMentee));
updatePreviewMentee();
</script>
@endsection
