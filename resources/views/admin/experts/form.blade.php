@extends('admin.layouts.app')
@php
    $isEdit = (bool) $expert;
    $oldExpertise = old('expertise_text', $isEdit ? implode(', ', $expert->expertise ?? []) : '');
@endphp
@section('title', $isEdit ? 'Edit Expert' : 'Create Expert')
@section('heading', $isEdit ? 'Edit Expert' : 'Create Expert Account')
@section('content')

<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.experts.index') }}" class="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-700">Experts</a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600 font-medium">{{ $isEdit ? 'Edit' : 'Create' }}</span>
</div>

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6 text-sm text-red-700">
    <ul class="space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST"
      action="{{ $isEdit ? route('admin.experts.update', $expert) : route('admin.experts.store') }}"
      enctype="multipart/form-data"
      class="max-w-5xl">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Account</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $expert->name ?? '') }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="Ananya Rao">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $expert->email ?? '') }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="expert@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password {{ $isEdit ? '' : '*' }}</label>
                        <input type="password" name="password" {{ $isEdit ? '' : 'required' }}
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="{{ $isEdit ? 'Leave blank to keep' : 'Min 8 characters' }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $expert->phone ?? '') }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="+91 98765 43210">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Gender</label>
                        <select name="gender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-indigo-400">
                            <option value="">Select</option>
                            @foreach(['male'=>'Male','female'=>'Female','other'=>'Other','prefer_not_to_say'=>'Prefer not to say'] as $v=>$l)
                            <option value="{{ $v }}" @selected(old('gender', $expert->gender ?? '') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Photo</label>
                        <input type="file" name="avatar" accept="image/*"
                               class="w-full border border-dashed border-gray-200 rounded-xl px-4 py-2 text-sm bg-gray-50">
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Professional profile</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Title <span class="text-red-400">*</span></label>
                            <input type="text" name="designation" value="{{ old('designation', $expert->designation ?? '') }}" required
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="Career coach, Interview specialist…">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Organisation</label>
                            <input type="text" name="company" value="{{ old('company', $expert->company ?? '') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Years of experience</label>
                            <input type="number" name="experience_years" min="0" max="50" value="{{ old('experience_years', $expert->experience_years ?? 0) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                            <input type="text" name="location" value="{{ old('location', $expert->location ?? '') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="Bengaluru">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Domain <span class="text-red-400">*</span></label>
                            <select name="field" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm bg-white outline-none focus:border-indigo-400">
                                <option value="">Select domain…</option>
                                @foreach(\App\Support\MentorIndustry::all() as $f)
                                <option value="{{ $f }}" @selected(old('field', $expert->field ?? '') === $f)>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">LinkedIn</label>
                            <input type="url" name="linkedin" value="{{ old('linkedin', $expert->linkedin ?? '') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100" placeholder="https://linkedin.com/in/…">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Skills (comma separated)</label>
                        <input type="text" name="expertise_text" value="{{ $oldExpertise }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                               placeholder="Resume review, Mock interviews, Career switch">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Bio <span class="text-red-400">*</span></label>
                        <textarea name="bio" rows="6" required
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">{{ old('bio', $expert->bio ?? '') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Min 30 characters. Shown on the public profile.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Session charge</h3>
                </div>
                <div class="p-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rate per minute <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm">₹</span>
                        <input type="number" name="rate_per_minute" id="expert-rate" step="0.5" min="0" required
                               value="{{ old('rate_per_minute', $expert->rate_per_minute ?? 10) }}"
                               class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                    <p class="text-sm text-gray-500 mt-2">= <span id="expert-hourly" class="font-semibold text-gray-800">₹0</span>/hour</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Status</h3>
                </div>
                <div class="p-5 space-y-2">
                    @foreach([
                        ['approved', 'Approved', 'Listed and bookable'],
                        ['pending', 'Pending', 'Hidden until you approve'],
                        ['rejected', 'Rejected', 'Not listed'],
                        ['suspended', 'Suspended', 'Temporarily hidden'],
                    ] as [$val, $label, $desc])
                    <label class="flex items-start gap-3 px-4 py-3 border rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="mentor_status" value="{{ $val }}" class="mt-1"
                               @checked(old('mentor_status', $expert->mentor_status ?? 'approved') === $val)>
                        <span>
                            <span class="block text-sm font-semibold text-gray-800">{{ $label }}</span>
                            <span class="block text-xs text-gray-400">{{ $desc }}</span>
                        </span>
                    </label>
                    @endforeach
                    @if($isEdit)
                    <label class="flex items-center gap-2 pt-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $expert->is_active))>
                        Account active
                    </label>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 flex gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl">
                    {{ $isEdit ? 'Save expert' : 'Create expert' }}
                </button>
                <a href="{{ route('admin.experts.index') }}" class="px-4 py-2.5 text-sm font-medium text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50">Cancel</a>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    var input = document.getElementById('expert-rate');
    var hourly = document.getElementById('expert-hourly');
    function sync() {
        var rate = parseFloat(input.value || '0');
        hourly.textContent = '₹' + Math.round(rate * 60);
    }
    input.addEventListener('input', sync);
    sync();
})();
</script>
@endsection
