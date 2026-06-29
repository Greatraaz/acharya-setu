<form id="passwordResetForm" class="space-y-4">
    @csrf

    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-sm text-orange-700">
        Resetting password for <strong>{{ $user->name }}</strong>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">New Password *</label>
        <input type="password" name="password" required
            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password *</label>
        <input type="password" name="password_confirmation" required
            class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
    </div>

    <div class="flex justify-end gap-3 pt-2 border-t">
        <button type="button" onclick="closeGlobalModal()"
            class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-sm hover:bg-slate-50">
            Cancel
        </button>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600">
            Reset Password
        </button>
    </div>
</form>

<script>
$('#passwordResetForm').on('submit', function (e) {
    e.preventDefault();
    $.ajax({
        url:  '{{ route('admin.users.password.reset', $user->id) }}',
        type: 'POST',
        data: $(this).serialize(),
        success: function () {
            closeGlobalModal();
            alert('Password reset successfully.');
        },
        error: function (xhr) {
            let errors = xhr.responseJSON?.errors;
            if (errors) alert(Object.values(errors).flat().join('\n'));
        }
    });
});
</script>