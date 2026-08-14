@php
    $myNote = ($session->notes ?? collect())
        ->where('author_id', auth()->id())
        ->where('is_shared', false)
        ->where('type', 'note')
        ->first();
@endphp
<div class="card" style="margin-bottom:20px;" id="my-session-notes" data-save-url="{{ route('sessions.my-note.save', $session->id) }}">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
        <h3 style="font-size:14px;font-weight:700;margin:0;">📝 My Personal Notes</h3>
        <span style="font-size:11px;color:var(--text-3);">Private — only you can see these</span>
    </div>
    <textarea id="my-note-content" class="form-textarea" rows="5"
              placeholder="Notes you took during the session…">{{ $myNote->content ?? '' }}</textarea>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;gap:12px;flex-wrap:wrap;">
        <span id="my-note-status" style="font-size:12px;color:var(--text-3);"></span>
        <button type="button" class="btn btn-primary btn-sm" id="my-note-save">Save Notes</button>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    const wrap = document.getElementById('my-session-notes');
    if (!wrap) return;

    const saveUrl = wrap.dataset.saveUrl;
    const textarea = document.getElementById('my-note-content');
    const statusEl = document.getElementById('my-note-status');
    const saveBtn = document.getElementById('my-note-save');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function saveMyNote() {
        saveBtn.disabled = true;
        statusEl.textContent = 'Saving…';
        statusEl.style.color = 'var(--text-2)';
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
            statusEl.style.color = 'var(--success)';
            if (window.showToast) showToast('success', 'Notes saved.');
        } catch (e) {
            statusEl.textContent = e.message || 'Save failed';
            statusEl.style.color = 'var(--error)';
            if (window.showToast) showToast('error', e.message || 'Could not save notes.');
        } finally {
            saveBtn.disabled = false;
        }
    }

    saveBtn?.addEventListener('click', saveMyNote);
})();
</script>
@endpush
@endonce
