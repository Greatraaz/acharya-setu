@extends('admin.layouts.app')
@section('title', 'Curriculum Streams')
@section('heading', 'Curriculum Streams')
@section('content')

<div class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">Global catalog shown during mentee onboarding. The 6-month plan is created later on each mentee’s copy.</p>
        <button type="button" onclick="document.getElementById('add-catalog-modal').classList.remove('hidden')"
                class="inline-flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-colors whitespace-nowrap flex-shrink-0 w-full sm:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Global Stream
        </button>
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

    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
        @if($streams->isEmpty())
        <div class="py-16 text-center text-gray-400 px-4">
            <div class="text-5xl mb-4">📚</div>
            <p class="text-gray-600 font-semibold mb-1">No global streams yet</p>
            <p class="text-gray-400 text-sm mb-5">Create catalog streams so new mentees can pick one during onboarding.</p>
            <button type="button" onclick="document.getElementById('add-catalog-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-violet-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-violet-700 transition-colors whitespace-nowrap">
                Create First Stream
            </button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[760px]">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-16">Sr. No.</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-20">Icon</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Stream</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Slug</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600">Description</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-28">Status</th>
                        <th class="text-left px-5 py-3 font-semibold text-gray-600 w-28">Enrollments</th>
                        <th class="text-right px-5 py-3 font-semibold text-gray-600 w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($streams as $stream)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4 text-gray-500">{{ $streams->firstItem() + $loop->index }}</td>
                        <td class="px-5 py-4">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                                 style="background: {{ $stream->color ?: '#7c3aed' }}18;">
                                {{ $stream->icon ?: '📚' }}
                            </div>
                        </td>
                        <td class="px-5 py-4 font-semibold text-gray-900 max-w-[180px]">
                            <div class="line-clamp-2">{{ $stream->name }}</div>
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md mt-1.5 whitespace-nowrap">Global catalog</span>
                        </td>
                        <td class="px-5 py-4 text-gray-500 font-mono text-xs whitespace-nowrap">{{ $stream->slug }}</td>
                        <td class="px-5 py-4 text-gray-600 max-w-xs">
                            <div class="line-clamp-2">{{ $stream->description ?: '—' }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($stream->is_active)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">Active</span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 whitespace-nowrap">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-600 whitespace-nowrap">{{ number_format($stream->enrollments_count ?? 0) }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" onclick='openEditCatalog(@json($stream))'
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-600 text-white hover:bg-green-700" title="Edit">✎</button>
                                <form method="POST" action="{{ route('admin.curriculum.catalog.destroy', $stream) }}"
                                      onsubmit="return confirm('Delete this global stream? Mentee copies already created will stay.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-red-600 text-white hover:bg-red-700" title="Delete">🗑</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $streams])
        @endif
    </div>
</div>

<div id="add-catalog-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
        <div class="flex items-center justify-between mb-5 gap-3">
            <h3 class="text-base font-bold text-gray-900">New Global Stream</h3>
            <button type="button" onclick="document.getElementById('add-catalog-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.curriculum.catalog.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stream Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. DevOps, Product Management"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (emoji)</label>
                    <input type="text" name="icon" placeholder="🎓" maxlength="4"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 text-center text-2xl">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Accent Color</label>
                    <input type="color" name="color" value="#7c3aed" class="h-10 w-full rounded-lg border border-gray-200 cursor-pointer p-0.5">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="Short description shown on onboarding"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="rounded">
                <span class="text-sm text-gray-700 font-medium">Active (shown in onboarding)</span>
            </label>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors whitespace-nowrap">
                    Create Stream
                </button>
                <button type="button" onclick="document.getElementById('add-catalog-modal').classList.add('hidden')"
                        class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<div id="edit-catalog-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto p-4 sm:p-6">
        <div class="flex items-center justify-between mb-5 gap-3">
            <h3 class="text-base font-bold text-gray-900">Edit Global Stream</h3>
            <button type="button" onclick="document.getElementById('edit-catalog-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-catalog-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stream Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (emoji)</label>
                    <input type="text" name="icon" maxlength="4"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 text-center text-2xl">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Accent Color</label>
                    <input type="color" name="color" value="#7c3aed" class="h-10 w-full rounded-lg border border-gray-200 cursor-pointer p-0.5">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="2"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="rounded">
                <span class="text-sm text-gray-700 font-medium">Active (shown in onboarding)</span>
            </label>
            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="w-full sm:flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors whitespace-nowrap">
                    Update Stream
                </button>
                <button type="button" onclick="document.getElementById('edit-catalog-modal').classList.add('hidden')"
                        class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCatalog(stream) {
    const form = document.getElementById('edit-catalog-form');
    form.action = '{{ url('/admin/curriculum/catalog') }}/' + stream.id;
    form.querySelector('[name=name]').value = stream.name || '';
    form.querySelector('[name=icon]').value = stream.icon || '';
    form.querySelector('[name=color]').value = stream.color || '#7c3aed';
    form.querySelector('[name=description]').value = stream.description || '';
    form.querySelectorAll('[name=is_active]').forEach(el => {
        if (el.type === 'checkbox') el.checked = !!stream.is_active;
    });
    document.getElementById('edit-catalog-modal').classList.remove('hidden');
}
</script>
@endsection
