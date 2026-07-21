@extends('admin.layouts.app')
@section('title','Curriculum Builder')
@section('heading','6-Month Journey Builder')
@section('content')

<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Build and manage the 6-month mentee learning journeys by stream.</p>
        <button onclick="document.getElementById('add-stream-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Stream
        </button>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
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

    {{-- Streams Grid --}}
    @if($streams->isEmpty())
    <div class="bg-white border-2 border-dashed border-gray-200 rounded-2xl p-16 text-center">
        <div class="text-5xl mb-4">🎓</div>
        <p class="text-gray-600 font-semibold mb-1">No education streams yet</p>
        <p class="text-gray-400 text-sm mb-5">Create your first stream to start building the 6-month journey.</p>
        <button onclick="document.getElementById('add-stream-modal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 bg-violet-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl hover:bg-violet-700 transition-colors">
            Create First Stream
        </button>
    </div>
    @else
    <div class="grid grid-cols-3 gap-5">
        @foreach($streams as $stream)
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-md transition-shadow group">
            {{-- Color bar --}}
            <div class="h-1.5 w-full" style="background: {{ $stream->color ?: '#7c3aed' }};"></div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-2xl flex-shrink-0"
                             style="background: {{ $stream->color ?: '#7c3aed' }}18;">
                            {{ $stream->icon ?: '📚' }}
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-sm">{{ $stream->name }}</h3>
                            <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $stream->slug }}</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full
                        {{ $stream->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $stream->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        {{ $stream->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                @if($stream->description)
                <p class="text-xs text-gray-500 leading-relaxed mb-2 line-clamp-2">{{ $stream->description }}</p>
                @endif

                @if($stream->mentee)
                <p class="text-xs text-violet-700 bg-violet-50 inline-flex items-center gap-1 px-2 py-1 rounded-lg mb-4">
                    👤 {{ $stream->mentee->name }}
                </p>
                @else
                <p class="text-xs text-amber-700 bg-amber-50 inline-flex items-center gap-1 px-2 py-1 rounded-lg mb-4">
                    ⚠ No mentee assigned
                </p>
                @endif

                <div class="grid grid-cols-3 gap-2 mb-4">
                    @foreach([
                        [$stream->months_count, 'Months'],
                        [$stream->enrollments_count, 'Enrolled'],
                        [0, 'Completed'],
                    ] as [$val, $label])
                    <div class="text-center bg-gray-50 rounded-lg py-2">
                        <div class="text-lg font-bold text-gray-800">{{ $val }}</div>
                        <div class="text-xs text-gray-400">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.curriculum.months', $stream) }}"
                       class="flex-1 text-center text-xs font-semibold text-violet-700 bg-violet-50 hover:bg-violet-100 px-3 py-2 rounded-lg transition-colors">
                        Manage Months
                    </a>
                    <button onclick='openEditStream(@json($stream))'
                            class="text-xs font-medium text-gray-600 border border-gray-200 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        Edit
                    </button>
                    <form method="POST" action="{{ route('admin.curriculum.streams.destroy', $stream) }}"
                          onsubmit="return confirm('Delete this stream and all its content?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-medium text-red-500 border border-red-100 px-3 py-2 rounded-lg hover:bg-red-50 transition-colors">
                            Del
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Add Stream Modal --}}
<div id="add-stream-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-900">New Education Stream</h3>
            <button onclick="document.getElementById('add-stream-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.curriculum.streams.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentee <span class="text-red-500">*</span></label>
                <select name="mentee_id" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
                    <option value="">— Select mentee —</option>
                    @foreach($mentees as $mentee)
                    <option value="{{ $mentee->id }}" @selected(old('mentee_id') == $mentee->id)>
                        {{ $mentee->name }} ({{ $mentee->email }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stream Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Full Stack Development, Product Design"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Icon (emoji)</label>
                    <input type="text" name="icon" placeholder="🎓" maxlength="4"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 text-center text-2xl">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Accent Color</label>
                    <div class="flex gap-2">
                        <input type="color" name="color" value="#7c3aed" class="h-10 w-14 rounded-lg border border-gray-200 cursor-pointer p-0.5">
                        <input type="text" value="#7c3aed" class="flex-1 border border-gray-200 rounded-xl px-3 py-2.5 text-xs font-mono outline-none focus:border-violet-400">
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="2" placeholder="What will mentees learn in this stream?"
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 resize-none"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Create Stream
                </button>
                <button type="button" onclick="document.getElementById('add-stream-modal').classList.add('hidden')"
                        class="px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Stream Modal --}}
<div id="edit-stream-modal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-bold text-gray-900">Edit Education Stream</h3>
            <button type="button" onclick="document.getElementById('edit-stream-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="edit-stream-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Mentee <span class="text-red-500">*</span></label>
                <select name="mentee_id" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 bg-white">
                    <option value="">— Select mentee —</option>
                    @foreach($mentees as $mentee)
                    <option value="{{ $mentee->id }}">{{ $mentee->name }} ({{ $mentee->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stream Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
            </div>
            <div class="grid grid-cols-2 gap-3">
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
                <span class="text-sm text-gray-700 font-medium">Active</span>
            </label>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                    Update Stream
                </button>
                <button type="button" onclick="document.getElementById('edit-stream-modal').classList.add('hidden')"
                        class="px-4 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditStream(stream) {
    const form = document.getElementById('edit-stream-form');
    form.action = '{{ url('/admin/curriculum/streams') }}/' + stream.id;
    form.querySelector('[name=mentee_id]').value = stream.mentee_id || '';
    form.querySelector('[name=name]').value = stream.name || '';
    form.querySelector('[name=icon]').value = stream.icon || '';
    form.querySelector('[name=color]').value = stream.color || '#7c3aed';
    form.querySelector('[name=description]').value = stream.description || '';
    form.querySelector('[name=is_active]').checked = !!stream.is_active;
    document.getElementById('edit-stream-modal').classList.remove('hidden');
}
</script>

@endsection