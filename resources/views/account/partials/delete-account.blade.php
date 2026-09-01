@if(in_array(auth()->user()->role, ['mentor', 'mentee'], true))
<div class="card account-settings-delete" id="delete-account">
    <h3 class="account-settings-delete__title">Delete account</h3>
    <p class="account-settings-delete__text">
        This permanently deactivates your {{ auth()->user()->role }} account. You will be signed out immediately.
        An admin can restore the account later, but you will not be able to sign in until then.
    </p>

    @if($errors->hasBag('deleteAccount'))
    <div class="alert alert-error account-settings-page__alert">
        <span class="alert-icon">❌</span>
        <div>{{ $errors->getBag('deleteAccount')->first() }}</div>
    </div>
    @endif

    <form action="{{ route('account.destroy') }}" method="POST" class="account-settings-form"
          onsubmit="return confirm('Delete your account? This cannot be undone from here.');">
        @csrf
        @method('DELETE')
        <div class="form-group">
            <label class="form-label">Confirm with your password</label>
            <input type="password" name="password" class="form-input" required autocomplete="current-password"
                   placeholder="Enter your password">
        </div>
        <button type="submit" class="btn btn-danger account-settings-form__submit">Delete my account</button>
    </form>
</div>
@endif
