@if(in_array(auth()->user()->role, ['mentor', 'mentee'], true))
<div class="card" id="delete-account" style="margin-top:16px;border-color:rgba(239,68,68,.35);">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:6px;color:var(--error);">Delete account</h3>
    <p style="font-size:13px;color:var(--text-2);line-height:1.6;margin-bottom:16px;">
        This permanently deactivates your {{ auth()->user()->role }} account. You will be signed out immediately.
        An admin can restore the account later, but you will not be able to sign in until then.
    </p>

    @if($errors->hasBag('deleteAccount'))
    <div class="alert alert-error" style="margin-bottom:14px;">
        <span class="alert-icon">❌</span>
        <div>{{ $errors->getBag('deleteAccount')->first() }}</div>
    </div>
    @endif

    <form action="{{ route('account.destroy') }}" method="POST"
          onsubmit="return confirm('Delete your account? This cannot be undone from here.');">
        @csrf
        @method('DELETE')
        <div class="form-group">
            <label class="form-label">Confirm with your password</label>
            <input type="password" name="password" class="form-input" required autocomplete="current-password"
                   placeholder="Enter your password">
        </div>
        <button type="submit" class="btn btn-danger">Delete my account</button>
    </form>
</div>
@endif
