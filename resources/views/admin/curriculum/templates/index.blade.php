@extends('admin.layouts.app')
@section('title', 'Curriculum Templates')
@section('heading', 'Curriculum Templates')
@section('content')

@php
    $totalTemplates = $templates->count();
    $activeTemplates = $templates->where('is_active', true)->count();
    $coveredIndustries = collect($grouped)->filter(fn ($rows) => $rows->isNotEmpty())->count();
    $totalIndustries = count($grouped);
    $needsBuild = $templates->filter(fn ($t) => (int) $t->months_count < 6)->count();
@endphp

<style>
    .tpl-modal { backdrop-filter: blur(4px); }
    .tpl-modal-panel { animation: tplModalIn .18s ease-out; }
    @keyframes tplModalIn {
        from { opacity: 0; transform: translateY(8px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .tpl-card {
        transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .tpl-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 12px 28px rgba(79, 70, 229, .08);
        transform: translateY(-2px);
    }
</style>

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <p class="text-sm text-gray-500 max-w-2xl">
            One starter plan per mentor industry (same domain list as onboarding). Mentors only see the template for their industry, then clone and tailor it for each mentee.
        </p>
        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center w-full lg:w-auto">
            <a href="{{ route('admin.curriculum.templates.sample') }}"
               class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 px-3 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Sample Excel
            </a>
            <button type="button" data-open-modal="import-template-modal"
                    class="inline-flex items-center justify-center gap-1.5 text-xs font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 px-3 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L9 8m4-4v12"/></svg>
                Import Excel
            </button>
            <button type="button" data-open-modal="add-template-modal"
                    class="col-span-2 sm:col-span-1 inline-flex items-center justify-center gap-1.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2.5 rounded-xl transition-colors whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Template
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
        <ul class="space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('import_errors'))
    <div class="bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-3 rounded-xl">
        <p class="font-semibold mb-1">Import notes</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach(session('import_errors') as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach([
            ['Templates', $totalTemplates, 'bg-indigo-50', 'text-indigo-700', 'border-indigo-100', 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
            ['Industries covered', $coveredIndustries.' / '.$totalIndustries, 'bg-slate-50', 'text-slate-700', 'border-slate-200', 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
            ['Active', $activeTemplates, 'bg-green-50', 'text-green-700', 'border-green-200', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Need months', $needsBuild, 'bg-amber-50', 'text-amber-700', 'border-amber-200', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as [$label, $count, $bg, $tc, $bc, $icon])
        <div class="{{ $bg }} border {{ $bc }} rounded-2xl p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-2xl font-bold {{ $tc }}">{{ is_numeric($count) ? number_format($count) : $count }}</div>
                    <div class="text-xs font-medium text-gray-500 mt-0.5">{{ $label }}</div>
                </div>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white/70 border border-white/80 {{ $tc }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Toolbar --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div class="admin-filter-search min-w-0 flex-1 lg:max-w-sm">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" id="template-search" placeholder="Industry, template name, skills…"
                           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all">
                </div>
            </div>
            <div class="flex gap-1 bg-gray-100 p-1 rounded-xl w-fit">
                <button type="button" data-filter="all" class="tpl-filter px-3 py-2 rounded-lg text-xs font-semibold bg-white text-gray-900 shadow-sm">All</button>
                <button type="button" data-filter="active" class="tpl-filter px-3 py-2 rounded-lg text-xs font-semibold text-gray-500 hover:text-gray-700">Active</button>
                <button type="button" data-filter="incomplete" class="tpl-filter px-3 py-2 rounded-lg text-xs font-semibold text-gray-500 hover:text-gray-700">Needs build</button>
                <button type="button" data-filter="empty" class="tpl-filter px-3 py-2 rounded-lg text-xs font-semibold text-gray-500 hover:text-gray-700">Empty industries</button>
            </div>
        </div>
    </div>

    {{-- Template grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4" id="templates-grid">
        @foreach($grouped as $industry => $rows)
            @forelse($rows as $template)
            @php
                $monthsDone = (int) $template->months_count;
                $progress = min(100, (int) round(($monthsDone / 6) * 100));
                $tags = is_array($template->expertise_tags) ? $template->expertise_tags : [];
                $accent = $template->color ?: '#4f46e5';
                $cardSearch = strtolower($industry.' '.$template->name.' '.($template->description ?? '').' '.implode(' ', $tags));
            @endphp
            <article class="tpl-card bg-white border border-gray-200 rounded-2xl overflow-hidden flex flex-col"
                     data-search="{{ e($cardSearch) }}"
                     data-active="{{ $template->is_active ? '1' : '0' }}"
                     data-incomplete="{{ $monthsDone < 6 ? '1' : '0' }}"
                     data-empty="0">
                <div class="h-1.5 w-full" style="background: {{ $accent }};"></div>
                <div class="p-5 flex flex-col flex-1">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0 border border-gray-100"
                             style="background: {{ $accent }}18;">
                            {{ $template->icon ?: '🗺️' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-gray-900 leading-snug line-clamp-2">{{ $template->name }}</h3>
                                @if($template->is_active)
                                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                                @else
                                <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                </span>
                                @endif
                            </div>
                            <p class="text-[11px] font-medium text-indigo-600 mt-1 truncate">{{ $industry }}</p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mt-3 line-clamp-2 min-h-[2.5rem]">{{ $template->description ?: 'No description yet.' }}</p>

                    <div class="flex flex-wrap gap-1.5 mt-3 min-h-[1.75rem]">
                        @forelse(array_slice($tags, 0, 3) as $tag)
                        <span class="text-[11px] px-2 py-0.5 rounded-lg bg-gray-50 text-gray-600 border border-gray-100">{{ $tag }}</span>
                        @empty
                        <span class="text-[11px] text-gray-300">No skills tagged</span>
                        @endforelse
                        @if(count($tags) > 3)
                        <span class="text-[11px] px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100 font-semibold">+{{ count($tags) - 3 }}</span>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="flex items-center justify-between text-[11px] text-gray-400 mb-1.5">
                            <span>{{ $monthsDone }}/6 months</span>
                            <span class="font-semibold {{ $monthsDone >= 6 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $monthsDone >= 6 ? 'Ready' : 'Needs build' }}
                            </span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $progress }}%; background: {{ $accent }};"></div>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 flex items-center gap-1.5 border-t border-gray-100">
                        <a href="{{ route('admin.curriculum.months', $template) }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-semibold px-2.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                            Build
                        </a>
                        <button type="button"
                                class="text-xs font-medium px-2.5 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                                onclick='openEditTemplate(@json($template))'>
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.curriculum.templates.destroy', $template) }}" class="inline"
                              onsubmit="return confirm('Delete “{{ addslashes($template->name) }}”? Mentors will no longer see it. Existing mentee copies stay.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium px-2.5 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </article>
            @empty
            <article class="tpl-card bg-white border border-dashed border-gray-200 rounded-2xl p-5 flex flex-col items-center justify-center text-center min-h-[260px]"
                     data-search="{{ e(strtolower($industry)) }}"
                     data-active="0"
                     data-incomplete="1"
                     data-empty="1">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-3 border border-amber-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                </div>
                <p class="text-sm font-semibold text-gray-800">{{ $industry }}</p>
                <p class="text-xs text-gray-400 mt-1 max-w-[14rem]">No starter template yet for this industry.</p>
                <button type="button" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-2 rounded-lg transition-colors"
                        onclick="openAddForIndustry(@json($industry))">
                    Create template
                </button>
            </article>
            @endforelse
        @endforeach

        @foreach($unassigned as $template)
        @php
            $monthsDone = (int) $template->months_count;
            $progress = min(100, (int) round(($monthsDone / 6) * 100));
            $accent = $template->color ?: '#f59e0b';
            $cardSearch = strtolower('unassigned '.$template->name.' '.($template->description ?? ''));
        @endphp
        <article class="tpl-card bg-white border border-amber-200 rounded-2xl overflow-hidden flex flex-col"
                 data-search="{{ e($cardSearch) }}"
                 data-active="{{ $template->is_active ? '1' : '0' }}"
                 data-incomplete="{{ $monthsDone < 6 ? '1' : '0' }}"
                 data-empty="0">
            <div class="h-1.5 w-full bg-amber-400"></div>
            <div class="p-5 flex flex-col flex-1">
                <div class="flex items-start gap-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0 border border-amber-100 bg-amber-50">
                        {{ $template->icon ?: '⚠️' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-gray-900 leading-snug">{{ $template->name }}</h3>
                        <p class="text-[11px] font-medium text-amber-700 mt-1">Needs industry assignment</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">{{ $template->expertise_field ?: $template->expertise_stream ?: 'No industry set' }}</p>
                <div class="mt-auto pt-4 flex items-center gap-1.5 border-t border-gray-100">
                    <a href="{{ route('admin.curriculum.months', $template) }}"
                       class="flex-1 inline-flex items-center justify-center text-xs font-semibold px-2.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">Build</a>
                    <button type="button" class="text-xs font-medium px-2.5 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition-colors"
                            onclick='openEditTemplate(@json($template))'>Edit</button>
                </div>
            </div>
        </article>
        @endforeach
    </div>

    <div id="templates-empty-filter" class="hidden bg-white border border-gray-200 rounded-2xl py-14 text-center">
        <p class="font-medium text-gray-700">No templates match your filters</p>
        <p class="text-sm text-gray-400 mt-1">Try another search or switch the status chips.</p>
    </div>
</div>

{{-- Import modal --}}
<div id="import-template-modal" class="tpl-modal hidden fixed inset-0 z-[80] bg-slate-900/45 items-center justify-center p-4">
    <div class="tpl-modal-panel bg-white rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col">
        <form method="POST" action="{{ route('admin.curriculum.templates.import') }}" enctype="multipart/form-data" class="flex flex-col min-h-0">
            @csrf
            <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between gap-3 shrink-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Import from Excel</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Create or replace templates from a spreadsheet.</p>
                </div>
                <button type="button" data-close-modal class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                <div class="rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 text-sm text-indigo-900">
                    One file can hold multiple templates. Repeat the template name and industry on every row. Re-importing the same name + industry replaces that template’s months/weeks.
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Excel file <span class="text-red-400">*</span></label>
                    <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-200 hover:border-indigo-300 rounded-2xl px-4 py-8 cursor-pointer bg-gray-50/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <span class="text-sm text-gray-600" id="import-file-label">Choose .xlsx, .xls, or .csv</span>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="hidden"
                               onchange="document.getElementById('import-file-label').textContent = this.files[0]?.name || 'Choose .xlsx, .xls, or .csv'">
                    </label>
                </div>
                <p class="text-xs text-gray-400">
                    Need the column layout?
                    <a href="{{ route('admin.curriculum.templates.sample') }}" class="text-indigo-700 font-semibold hover:text-indigo-900">Download the sample</a>
                    (Guide sheet lists every column and valid industries).
                </p>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/70 flex justify-end gap-2 shrink-0">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">Import</button>
            </div>
        </form>
    </div>
</div>

{{-- Add modal --}}
<div id="add-template-modal" class="tpl-modal hidden fixed inset-0 z-[80] bg-slate-900/45 items-center justify-center p-4">
    <div class="tpl-modal-panel bg-white rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col">
        <form method="POST" action="{{ route('admin.curriculum.templates.store') }}" class="flex flex-col min-h-0">
            @csrf
            <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between gap-3 shrink-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">New starter template</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Create the shell, then Build months and weeks.</p>
                </div>
                <button type="button" data-close-modal class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                @include('admin.curriculum.templates._fields')
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/70 flex justify-end gap-2 shrink-0">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">Create template</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit modal --}}
<div id="edit-template-modal" class="tpl-modal hidden fixed inset-0 z-[80] bg-slate-900/45 items-center justify-center p-4">
    <div class="tpl-modal-panel bg-white rounded-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden shadow-2xl border border-gray-100 flex flex-col">
        <form id="edit-template-form" method="POST" class="flex flex-col min-h-0">
            @csrf
            @method('PUT')
            <div class="px-6 py-4 border-b border-gray-100 flex items-start justify-between gap-3 shrink-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Edit template</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Update name, industry, and visibility.</p>
                </div>
                <button type="button" data-close-modal class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4 overflow-y-auto flex-1">
                @include('admin.curriculum.templates._fields')
            </div>
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/70 flex justify-end gap-2 shrink-0">
                <button type="button" data-close-modal class="px-4 py-2.5 text-sm font-medium text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 rounded-xl transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors">Update template</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeModal(el) {
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
        if (!document.querySelector('.tpl-modal.flex')) {
            document.body.style.overflow = '';
        }
    }

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => closeModal(btn.closest('.tpl-modal')));
    });
    document.querySelectorAll('.tpl-modal').forEach((modal) => {
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.tpl-modal.flex').forEach((modal) => closeModal(modal));
    });
    document.querySelectorAll('[data-open-modal]').forEach((btn) => {
        btn.addEventListener('click', () => openModal(btn.getAttribute('data-open-modal')));
    });

    window.openAddForIndustry = function (industry) {
        const modal = document.getElementById('add-template-modal');
        const select = modal.querySelector('[name=expertise_field]');
        if (select) select.value = industry || '';
        openModal('add-template-modal');
    };

    window.openEditTemplate = function (template) {
        const form = document.getElementById('edit-template-form');
        form.action = '{{ url('/admin/curriculum/templates') }}/' + template.id;
        form.querySelector('[name=name]').value = template.name || '';
        form.querySelector('[name=icon]').value = template.icon || '';
        form.querySelector('[name=color]').value = template.color || '#4f46e5';
        form.querySelector('[name=description]').value = template.description || '';
        form.querySelector('[name=expertise_field]').value = template.expertise_field || '';
        form.querySelector('[name=expertise_tags]').value = Array.isArray(template.expertise_tags) ? template.expertise_tags.join(', ') : '';
        form.querySelectorAll('[name=is_active]').forEach((el) => {
            if (el.type === 'checkbox') el.checked = !!template.is_active;
        });
        openModal('edit-template-modal');
    };

    let activeFilter = 'all';
    const searchInput = document.getElementById('template-search');
    const emptyState = document.getElementById('templates-empty-filter');

    function applyFilters() {
        const q = (searchInput?.value || '').trim().toLowerCase();
        let visible = 0;

        document.querySelectorAll('#templates-grid .tpl-card').forEach((card) => {
            const isEmpty = card.dataset.empty === '1';
            let match = true;

            if (activeFilter === 'empty' && !isEmpty) match = false;
            if (activeFilter === 'active' && card.dataset.active !== '1') match = false;
            if (activeFilter === 'incomplete' && card.dataset.incomplete !== '1') match = false;
            if (activeFilter !== 'empty' && isEmpty && activeFilter !== 'all') match = false;
            if (activeFilter === 'all' && isEmpty) match = false;
            if (q && !(card.dataset.search || '').includes(q)) match = false;

            card.classList.toggle('hidden', !match);
            if (match) visible++;
        });

        if (emptyState) emptyState.classList.toggle('hidden', visible > 0);
    }

    document.querySelectorAll('.tpl-filter').forEach((btn) => {
        btn.addEventListener('click', function () {
            activeFilter = this.dataset.filter || 'all';
            document.querySelectorAll('.tpl-filter').forEach((b) => {
                const on = b === this;
                b.classList.toggle('bg-white', on);
                b.classList.toggle('text-gray-900', on);
                b.classList.toggle('shadow-sm', on);
                b.classList.toggle('text-gray-500', !on);
            });
            applyFilters();
        });
    });

    searchInput?.addEventListener('input', applyFilters);
})();
</script>
@endpush
