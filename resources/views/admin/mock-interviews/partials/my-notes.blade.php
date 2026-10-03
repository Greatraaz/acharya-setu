@php
    $myNote = ($item->notes ?? collect())
        ->where('author_id', auth()->id())
        ->where('is_shared', false)
        ->where('type', 'note')
        ->first();
@endphp
<div class="bg-white border border-gray-200 rounded-2xl p-5" id="admin-my-mock-notes"
     data-save-url="{{ route('mock-interviews.notes.save', $item->id) }}">
    <div class="flex items-start justify-between gap-3 mb-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">My Personal Notes</h3>
            <p class="text-xs text-gray-500 mt-0.5">Private — only you can see these</p>
        </div>
    </div>
    <textarea id="admin-my-mock-note-content" rows="5"
              class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
              placeholder="Notes you took during the mock interview…">{{ $myNote->content ?? '' }}</textarea>
    <div class="flex items-center justify-between gap-3 mt-3 flex-wrap">
        <span id="admin-my-mock-note-status" class="text-xs text-gray-400"></span>
        <button type="button" id="admin-my-mock-note-save"
                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
            Save Notes
        </button>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const wrap = document.getElementById('admin-my-mock-notes');
    if (!wrap) return;

    const saveUrl = wrap.dataset.saveUrl;
    const textarea = document.getElementById('admin-my-mock-note-content');
    const statusEl = document.getElementById('admin-my-mock-note-status');
    const saveBtn = document.getElementById('admin-my-mock-note-save');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function saveMyNote() {
        saveBtn.disabled = true;
        statusEl.textContent = 'Saving…';
        statusEl.className = 'text-xs text-gray-500';
        try {
            const res = await fetch(saveUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ content: textarea.value }),
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Could not save notes.');
            statusEl.textContent = 'Saved';
            statusEl.className = 'text-xs text-emerald-600';
        } catch (e) {
            statusEl.textContent = e.message || 'Save failed';
            statusEl.className = 'text-xs text-red-600';
        } finally {
            saveBtn.disabled = false;
        }
    }

    saveBtn?.addEventListener('click', saveMyNote);
})();
</script>
@endpush
@endonce
