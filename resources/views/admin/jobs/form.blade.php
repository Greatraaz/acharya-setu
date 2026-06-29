@extends('admin.layouts.app')
@section('title', $job->exists ? 'Edit Job' : 'Post New Job')
@section('heading', $job->exists ? 'Edit: ' . $job->title : 'Post New Job')
@section('content')

<style>
    .toggle-switch { position: relative; display: inline-flex; align-items: center; cursor: pointer; }
    .toggle-switch input { display: none; }
    .toggle-track { width: 44px; height: 24px; background: #d1d5db; border-radius: 9999px; transition: background .2s; position: relative; flex-shrink: 0; }
    .toggle-switch input:checked + .toggle-track { background: #2563eb; }
    .toggle-thumb { position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; background: white; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.18); }
    .toggle-switch input:checked ~ .toggle-track .toggle-thumb { transform: translateX(20px); }
</style>

{{-- Page top bar --}}
<div class="flex items-center gap-2 mb-6 text-sm">
    <a href="{{ route('admin.jobs.index') }}"
       class="inline-flex items-center gap-1.5 text-gray-400 hover:text-gray-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Jobs
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-600 font-medium">{{ $job->exists ? 'Edit listing' : 'New listing' }}</span>
</div>

@if($errors->any())
<div class="flex gap-3 items-start bg-red-50 border border-red-200 rounded-2xl px-5 py-4 mb-6">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
        <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
    </svg>
    <ul class="text-sm text-red-700 space-y-0.5 list-none">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST"
      action="{{ $job->exists ? route('admin.jobs.update', $job) : route('admin.jobs.store') }}"
      class="max-w-5xl">
    @csrf
    @if($job->exists) @method('PUT') @endif

    <div class="grid grid-cols-3 gap-6">

        {{-- ═══════════════ LEFT COLUMN ═══════════════ --}}
        <div class="col-span-2 space-y-5">

            {{-- Job Details card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Job Details</h3>
                </div>
                <div class="p-6 space-y-5">

                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Job Title <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="title"
                               value="{{ old('title', $job->title) }}"
                               placeholder="e.g. Senior Laravel Developer"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400">
                        @error('title')
                        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    {{-- Dept + Openings --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                            <input type="text" name="department"
                                   value="{{ old('department', $job->department) }}"
                                   list="dept-list"
                                   placeholder="Engineering, Marketing…"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400">
                            <datalist id="dept-list">
                                @foreach(['Engineering','Product','Design','Marketing','Sales','Operations','Finance','HR','Legal','Customer Support'] as $d)
                                <option value="{{ $d }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Number of Openings</label>
                            <input type="number" name="openings" min="1"
                                   value="{{ old('openings', $job->openings ?? 1) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>

                    {{-- Job type + Experience + Location type --}}
                    <div class="grid grid-cols-3 gap-4">
                        @foreach([
                            ['job_type',         'Job Type',         \App\Models\JobListing::JOB_TYPES,         null],
                            ['experience_level', 'Experience Level', \App\Models\JobListing::EXPERIENCE_LEVELS, 'mid'],
                            ['location_type',    'Location Type',    \App\Models\JobListing::LOCATION_TYPES,    'onsite'],
                        ] as [$name, $label, $options, $default])
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                {{ $label }} <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <select name="{{ $name }}"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-9 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer">
                                    @foreach($options as $val => $lbl)
                                    <option value="{{ $val }}" {{ old($name, $job->$name ?? $default) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Location --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Location <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="location"
                               value="{{ old('location', $job->location) }}"
                               placeholder="e.g. Mumbai, India · Remote · Bengaluru / Hybrid"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400">
                        @error('location')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Compensation card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Compensation</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Minimum Salary</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none select-none">{{ config_val('currency_symbol','₹') }}</span>
                                <input type="number" name="salary_min" step="1000" min="0"
                                       value="{{ old('salary_min', $job->salary_min) }}"
                                       placeholder="500000"
                                       class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Maximum Salary</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium pointer-events-none select-none">{{ config_val('currency_symbol','₹') }}</span>
                                <input type="number" name="salary_max" step="1000" min="0"
                                       value="{{ old('salary_max', $job->salary_max) }}"
                                       placeholder="1200000"
                                       class="w-full border border-gray-200 rounded-xl pl-8 pr-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Salary Period</label>
                            <div class="relative">
                                <select name="salary_period"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-9 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer">
                                    <option value="yearly"  {{ old('salary_period', $job->salary_period ?? 'yearly') === 'yearly'  ? 'selected' : '' }}>Per Year</option>
                                    <option value="monthly" {{ old('salary_period', $job->salary_period) === 'monthly' ? 'selected' : '' }}>Per Month</option>
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 pt-5">
                            <label class="toggle-switch flex-shrink-0">
                                <input type="hidden" name="salary_hidden" value="0">
                                <input type="checkbox" name="salary_hidden" value="1"
                                       {{ old('salary_hidden', $job->salary_hidden) ? 'checked' : '' }}>
                                <div class="toggle-track"><div class="toggle-thumb"></div></div>
                            </label>
                            <div>
                                <p class="text-sm font-medium text-gray-800 leading-tight">Hide Salary</p>
                                <p class="text-xs text-gray-400 mt-0.5">Show "Competitive" instead</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Job Description card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Job Description</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Overview <span class="text-red-400">*</span>
                        </label>
                        <textarea name="description" rows="6"
                                  placeholder="Describe the role, your company culture, and what makes this an exciting opportunity…"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400 resize-y leading-relaxed">{{ old('description', $job->description) }}</textarea>
                        @error('description')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    @foreach([
                        ['responsibilities', 'Responsibilities', "• Lead the design and implementation of backend services\n• Review code and mentor junior developers\n• Collaborate with product and design teams", '6', 'Use bullet points (•) for readability.'],
                        ['requirements',     'Requirements',     "• 4+ years of PHP / Laravel experience\n• Strong understanding of REST APIs\n• Experience with MySQL and Redis", '6', null],
                        ['benefits',         'Benefits & Perks', "• Flexible working hours\n• Health insurance\n• Annual performance bonus", '4', null],
                    ] as [$name, $label, $ph, $rows, $hint])
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
                        <textarea name="{{ $name }}" rows="{{ $rows }}"
                                  placeholder="{{ $ph }}"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400 resize-y leading-relaxed font-mono">{{ old($name, $job->$name) }}</textarea>
                        @if($hint)
                        <p class="text-xs text-gray-400 mt-1.5">{{ $hint }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Skills card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Skills & Technologies</h3>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Skills / Tags</label>

                    <div id="skills-chips" class="flex flex-wrap gap-2 mb-3 min-h-[32px]">
                        @foreach(old('skills_array', $job->skills ?? []) as $skill)
                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-blue-100">
                            {{ $skill }}
                            <button type="button" onclick="removeChip(this)"
                                    class="text-blue-300 hover:text-blue-700 text-sm font-bold leading-none transition-colors">×</button>
                        </span>
                        @endforeach
                    </div>

                    <div class="flex gap-2">
                        <input type="text" id="skill-input"
                               placeholder="PHP, Laravel, MySQL… press Enter or comma to add"
                               class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400">
                        <button type="button" onclick="addChip()"
                                class="px-4 py-2.5 text-sm font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-xl transition-colors flex-shrink-0">
                            Add
                        </button>
                    </div>
                    <input type="hidden" name="skills_raw" id="skills-raw"
                           value="{{ old('skills_raw', implode(', ', $job->skills ?? [])) }}">
                    <p class="text-xs text-gray-400 mt-2">These help candidates discover the listing via search filters.</p>
                </div>
            </div>

            {{-- Application Settings card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Application Settings</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Apply URL
                                <span class="text-xs text-gray-400 font-normal">(external ATS link)</span>
                            </label>
                            <input type="url" name="apply_url"
                                   value="{{ old('apply_url', $job->apply_url) }}"
                                   placeholder="https://your-ats.com/apply/job-123"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Apply Email</label>
                            <input type="email" name="apply_email"
                                   value="{{ old('apply_email', $job->apply_email) }}"
                                   placeholder="careers@yourcompany.com"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 placeholder-gray-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Application Deadline</label>
                            <input type="date" name="deadline"
                                   value="{{ old('deadline', $job->deadline?->format('Y-m-d')) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /left column --}}

        {{-- ═══════════════ SIDEBAR ═══════════════ --}}
        <div class="space-y-5">

            {{-- Publish card (sticky) --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden sticky top-4">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60">
                    <h3 class="text-sm font-semibold text-gray-800">Publish</h3>
                </div>
                <div class="p-5 space-y-4">
                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Status <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <select name="status"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 pr-9 text-sm text-gray-900 bg-white outline-none transition-all focus:border-blue-400 focus:ring-2 focus:ring-blue-100 appearance-none cursor-pointer">
                                <option value="draft"   {{ old('status', $job->status ?? 'draft') === 'draft'   ? 'selected' : '' }}>📝 Draft</option>
                                <option value="active"  {{ old('status', $job->status) === 'active'  ? 'selected' : '' }}>✅ Active / Published</option>
                                <option value="paused"  {{ old('status', $job->status) === 'paused'  ? 'selected' : '' }}>⏸ Paused</option>
                                <option value="closed"  {{ old('status', $job->status) === 'closed'  ? 'selected' : '' }}>🔒 Closed</option>
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </div>
                    </div>

                    {{-- Featured --}}
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-xl px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800 leading-tight">Featured</p>
                            <p class="text-xs text-gray-400 mt-0.5">Pin to top of listings</p>
                        </div>
                        <label class="toggle-switch flex-shrink-0">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1"
                                   {{ old('is_featured', $job->is_featured) ? 'checked' : '' }}>
                            <div class="toggle-track"><div class="toggle-thumb"></div></div>
                        </label>
                    </div>
                </div>

                {{-- CTA --}}
                <div class="px-5 pb-5 flex gap-2">
                    <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                        {{ $job->exists ? 'Update Job' : 'Post Job' }}
                    </button>
                    <a href="{{ route('admin.jobs.index') }}"
                       class="px-4 py-2.5 text-sm font-medium text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors text-center">
                        Cancel
                    </a>
                </div>
            </div>

            {{-- Live Preview card --}}
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/60 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800">Preview</h3>
                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2 py-0.5 rounded-full uppercase tracking-wide">Live</span>
                </div>
                <div class="p-5">
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 space-y-3">
                        {{-- Header --}}
                        <div class="flex items-start gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 border border-blue-200 flex items-center justify-center flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M14.763.075A.5.5 0 0 1 15 .5v15a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5V14h-1v1.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V10a.5.5 0 0 1 .342-.474L6 7.64V4.5a.5.5 0 0 1 .276-.447l8-4a.5.5 0 0 1 .487.022z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-900 truncate leading-tight" id="prev-title">Job Title</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Your Company</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5" id="prev-badges"></div>

                        <div class="flex items-center gap-1.5 text-[11px] text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/></svg>
                            <span id="prev-location">Location</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/><path d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V4zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2H3z"/></svg>
                            <span class="text-[11px] font-bold text-emerald-700" id="prev-salary">—</span>
                        </div>

                        <div class="flex flex-wrap gap-1" id="prev-skills"></div>
                    </div>
                </div>
            </div>

            {{-- Tips card --}}
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                            <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-blue-800 uppercase tracking-wide">Posting tips</p>
                </div>
                <ul class="text-xs text-blue-700 space-y-2 leading-relaxed">
                    @foreach([
                        'Clear, specific titles get 3× more views',
                        'A salary range increases applications by 40%',
                        'Add 5–10 skills tags for better discoverability',
                        'Bullet-point responsibilities are easier to scan',
                    ] as $tip)
                    <li class="flex items-start gap-2">
                        <span class="text-blue-300 flex-shrink-0 mt-0.5 font-bold">•</span>
                        {{ $tip }}
                    </li>
                    @endforeach
                </ul>
            </div>

        </div>{{-- /sidebar --}}
    </div>
</form>

<script>
// ── Chip system ────────────────────────────────────────────
function getChips() {
    return [...document.querySelectorAll('#skills-chips span')].map(s =>
        s.childNodes[0].textContent.trim()
    );
}
function syncRaw() {
    document.getElementById('skills-raw').value = getChips().join(', ');
}
function addChip() {
    const input = document.getElementById('skill-input');
    const raw   = input.value.trim().replace(/,$/, '');
    if (!raw) return;

    raw.split(',').map(v => v.trim()).filter(Boolean).forEach(v => {
        const span = document.createElement('span');
        span.className = 'inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-full border border-blue-100';
        span.innerHTML = `${v}<button type="button" onclick="removeChip(this)" class="text-blue-300 hover:text-blue-700 text-sm font-bold leading-none transition-colors">×</button>`;
        document.getElementById('skills-chips').appendChild(span);
    });

    input.value = '';
    syncRaw();
    updatePreview();
}
function removeChip(btn) {
    btn.closest('span').remove();
    syncRaw();
    updatePreview();
}
document.getElementById('skill-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ',') { e.preventDefault(); addChip(); }
});

// ── Live preview ────────────────────────────────────────────
function updatePreview() {
    const title    = document.querySelector('[name=title]').value.trim()    || 'Job Title';
    const location = document.querySelector('[name=location]').value.trim() || 'Location';
    const jtype    = document.querySelector('[name=job_type]');
    const ltype    = document.querySelector('[name=location_type]');
    const exp      = document.querySelector('[name=experience_level]');
    const smin     = parseFloat(document.querySelector('[name=salary_min]').value) || 0;
    const smax     = parseFloat(document.querySelector('[name=salary_max]').value) || 0;
    const shidden  = document.querySelector('[name=salary_hidden]:checked');
    const sym      = '{{ config_val("currency_symbol", "₹") }}';

    document.getElementById('prev-title').textContent    = title;
    document.getElementById('prev-location').textContent = location;

    // Salary
    let salaryText = '—';
    if (shidden) {
        salaryText = 'Competitive';
    } else if (smin || smax) {
        const lo = smin ? sym + (smin / 100000).toFixed(1) + 'L' : '';
        const hi = smax ? sym + (smax / 100000).toFixed(1) + 'L' : '';
        salaryText = lo && hi ? lo + ' – ' + hi + '/yr' : (lo || hi) + '/yr';
    }
    document.getElementById('prev-salary').textContent = salaryText;

    // Badges
    const badgeDefs = [
        { el: jtype, cls: 'bg-blue-100 text-blue-700'   },
        { el: ltype, cls: 'bg-teal-100 text-teal-700'   },
        { el: exp,   cls: 'bg-violet-100 text-violet-700'},
    ];
    document.getElementById('prev-badges').innerHTML = badgeDefs
        .filter(b => b.el?.selectedIndex >= 0)
        .map(b => `<span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full ${b.cls}">${b.el.options[b.el.selectedIndex].text}</span>`)
        .join('');

    // Skills
    document.getElementById('prev-skills').innerHTML = getChips().slice(0, 5)
        .map(s => `<span class="inline-block text-[10px] font-medium bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">${s}</span>`)
        .join('');
}

document.querySelectorAll('[name=title],[name=location],[name=salary_min],[name=salary_max],[name=job_type],[name=location_type],[name=experience_level]')
    .forEach(el => el.addEventListener('input', updatePreview));
document.querySelector('[name=salary_hidden]')?.addEventListener('change', updatePreview);

updatePreview();
</script>

@endsection