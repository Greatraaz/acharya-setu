<div id="cancel-session-modal" class="modal-overlay">
    <div class="modal" style="max-width:440px;">
        <div class="modal-header">
            <span class="modal-title">Cancel session</span>
            <button type="button" class="modal-close" aria-label="Close">✕</button>
        </div>
        <div class="modal-body">
            <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">
                The mentee receives a full refund in their wallet, including any amount paid online. A reason is optional.
            </p>
            <form id="cancel-session-form">
                <input type="hidden" name="session_id" id="cancel-session-id">
                <div class="form-group">
                    <label class="form-label" for="cancel-session-reason">Reason (optional)</label>
                    <textarea id="cancel-session-reason" name="reason" class="form-textarea" rows="3" maxlength="500" placeholder="Why are you cancelling this session?"></textarea>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('cancel-session-modal')">Keep session</button>
                    <button type="submit" class="btn btn-danger" style="flex:1;">Cancel and refund</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openCancelSession(id) {
    const idInput = document.getElementById('cancel-session-id');
    const reason = document.getElementById('cancel-session-reason');
    if (idInput) idInput.value = id;
    if (reason) reason.value = '';
    openModal('cancel-session-modal');
}
document.getElementById('cancel-session-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('cancel-session-id')?.value;
    const reason = (document.getElementById('cancel-session-reason')?.value || '').trim();
    if (!id) {
        showToast('error', 'Could not cancel this session.');
        return;
    }
    const btn = this.querySelector('[type="submit"]');
    AjaxPost(`/mentor/sessions/${id}/cancel`, { reason }, {
        loader: true,
        btn,
        onSuccess: (data) => {
            closeModal('cancel-session-modal');
            showToast('success', data.message || 'Session cancelled. The mentee has been refunded in full.');
            setTimeout(() => location.reload(), 700);
        },
        onError: (err) => showToast('error', err.message || 'Could not cancel session.'),
    });
});
</script>
