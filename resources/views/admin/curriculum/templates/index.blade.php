@extends('admin.layouts.app')
@section('title', 'Curriculum Templates')
@section('heading', 'Curriculum Templates')
@section('content')

<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">One starter plan per mentor industry (the same domain list as onboarding). Mentors only see the template for their industry.</p>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <a href="{{ route('admin.curriculum.templates.sample') }}"
               class="inline-flex items-center justify-center gap-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors whitespace-nowrap">
                Sample Excel
            </a>
            <button type="button" onclick="document.getElementById('import-template-modal').classList.remove('hidden')"
                    class="inline-flex items-center justify-center gap-2 border border-violet-200 bg-violet-50 hover:bg-violet-100 text-violet-800 text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors whitespace-nowrap">
                Import Excel
            </button>
            <button type="button" onclick="document.getElementById('add-template-modal').classList.remove('hidden')"
                    class="inline-flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors whitespace-nowrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Template
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('import_errors'))
    <div class="bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-3 rounded-xl">
        <ul class="list-disc list-inside space-y-1">
            @foreach(session('import_errors') as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="space-y-4">
        @foreach($grouped as $industry => $rows)
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between gap-3 bg-gray-50/70">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ $industry }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $rows->count() }} template{{ $rows->count() === 1 ? '' : 's' }}</p>
                </div>
            </div>
            @if($rows->isEmpty())
            <div class="px-5 py-8 text-sm text-gray-400 text-center">
                No template for this industry yet.
                <button type="button" class="text-violet-700 font-medium hover:text-violet-900 ml-1" onclick="openAddForIndustry(@json($industry))">Create one</button>
            </div>
            @else
            <div class="divide-y divide-gray-100">
                @foreach($rows as $template)
                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex items-start gap-3 min-w-0 flex-1">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                             style="background: {{ $template->color ?: '#7c3aed' }}18;">
                            {{ $template->icon ?: '🗺️' }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900">{{ $template->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $template->description ?: '—' }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $template->months_count }}/6 months
                                @if($template->is_active)
                                · <span class="text-green-700">Active</span>
                                @else
                                · Inactive
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 sm:justify-end flex-shrink-0">
                        <a href="{{ route('admin.curriculum.months', $template) }}" class="text-violet-700 hover:text-violet-900 text-sm font-medium">Build</a>
                        <button type="button" class="text-gray-600 hover:text-gray-900 text-sm font-medium" onclick='openEditTemplate(@json($template))'>Edit</button>
                        <form method="POST" action="{{ route('admin.curriculum.templates.destroy', $template) }}" class="inline"
                              onsubmit="return confirm('Delete this starter template? Mentors will no longer see it. Existing mentee copies stay.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach

        @if($unassigned->isNotEmpty())
        <div class="bg-white border border-amber-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-amber-100 bg-amber-50">
                <h3 class="text-sm font-semibold text-amber-900">Unassigned industry</h3>
                <p class="text-xs text-amber-700 mt-0.5">Set an onboarding industry so mentors can see these.</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($unassigned as $template)
                <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-gray-900">{{ $template->name }}</div>
                        <div class="text-xs text-gray-500">{{ $template->expertise_field ?: $template->expertise_stream ?: 'No industry' }}</div>
                    </div>
                    <button type="button" class="text-gray-600 hover:text-gray-900 text-sm font-medium" onclick='openEditTemplate(@json($template))'>Edit</button>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<div id="import-template-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-4 sm:p-6">
        <div class="flex items-center justify-between mb-5 gap-3">
            <h3 class="text-base font-bold text-gray-900">Import templates from Excel</h3>
            <button type="button" onclick="document.getElementById('import-template-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.curriculum.templates.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <p class="text-sm text-gray-500">One spreadsheet can contain multiple templates. Each row is a task or MCQ; repeat the template name and industry on every row. Re-importing the same name + industry replaces the months/weeks on that template.</p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Excel file</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="block w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-violet-50 file:text-violet-800 file:font-semibold">
            </div>
            <p class="text-xs text-gray-400">Columns include template, month, week, task, and MCQ fields.
                <a href="{{ route('admin.curriculum.templates.sample') }}" class="text-violet-700 font-medium hover:text-violet-900">Download the sample</a>
                (Guide sheet lists every column and valid industries).
            </p>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg">Import</button>
                <button type="button" onclick="document.getElementById('import-template-modal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="add-template-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-4 sm:p-6">
        <div class="flex items-center justify-between mb-5 gap-3">
            <h3 class="text-base font-bold text-gray-900">New starter template</h3>
            <button type="button" onclick="document.getElementById('add-template-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.curriculum.templates.store') }}" class="space-y-4">
            @csrf
            @include('admin.curriculum.templates._fields')
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg">Create template</button>
                <button type="button" onclick="document.getElementById('add-template-modal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="edit-template-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-4 sm:p-6">
        <div class="flex items-center justify-between mb-5 gap-3">
            <h3 class="text-base font-bold text-gray-900">Edit template</h3>
            <button type="button" onclick="document.getElementById('edit-template-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-template-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.curriculum.templates._fields')
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg">Update template</button>
                <button type="button" onclick="document.getElementById('edit-template-modal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddForIndustry(industry) {
    const modal = document.getElementById('add-template-modal');
    const select = modal.querySelector('[name=expertise_field]');
    if (select) select.value = industry || '';
    modal.classList.remove('hidden');
}
function openEditTemplate(template) {
    const form = document.getElementById('edit-template-form');
    form.action = '{{ url('/admin/curriculum/templates') }}/' + template.id;
    form.querySelector('[name=name]').value = template.name || '';
    form.querySelector('[name=icon]').value = template.icon || '';
    form.querySelector('[name=color]').value = template.color || '#7c3aed';
    form.querySelector('[name=description]').value = template.description || '';
    form.querySelector('[name=expertise_field]').value = template.expertise_field || '';
    form.querySelector('[name=expertise_tags]').value = Array.isArray(template.expertise_tags) ? template.expertise_tags.join(', ') : '';
    form.querySelectorAll('[name=is_active]').forEach(el => { if (el.type === 'checkbox') el.checked = !!template.is_active; });
    document.getElementById('edit-template-modal').classList.remove('hidden');
}
</script>
@endsection
