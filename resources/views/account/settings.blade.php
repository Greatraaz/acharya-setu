@extends('frontend.layouts.app')
@section('title', 'Profile Settings — Vedrix')

@section('content')
@php $user = $user ?? auth()->user(); @endphp
<div class="dash-layout account-settings-page">
    @if($user->role === 'mentor')
        @include('frontend.mentors.partials.sidebar')
    @else
        @include('frontend.mentee.partials.sidebar')
    @endif

    <div class="dash-content">
        <div class="dash-header">
            <div class="dash-title">Profile Settings</div>
            <div class="dash-subtitle">Update your account details and password.</div>
        </div>

        @if(session('success'))
        <div class="alert alert-success account-settings-page__alert">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-error account-settings-page__alert">
            <span class="alert-icon">❌</span>
            <div>
                <ul class="account-settings-page__error-list">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="account-settings-layout">
            <div class="card account-settings-card">
                <h3 class="account-settings-card__title">Basic Information</h3>

                <div class="account-settings-avatar">
                    <div class="account-settings-avatar__preview">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="" id="settings-avatar-img">
                        @else
                            <span id="settings-avatar-fallback">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="account-settings-avatar__actions">
                        <form action="{{ route('account.avatar') }}" method="POST" enctype="multipart/form-data" id="avatar-form" class="account-settings-avatar__form">
                            @csrf
                            <input type="file" name="avatar" id="avatar-input" accept="image/jpeg,image/png,image/webp" class="sr-only" onchange="this.form.submit()">
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('avatar-input').click()">Change Photo</button>
                            <div class="form-hint account-settings-avatar__hint">JPG, PNG, or WebP up to 2MB</div>
                        </form>
                        @if($user->avatar_url)
                        <form action="{{ route('account.avatar.remove') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm">Remove Photo</button>
                        </form>
                        @endif
                    </div>
                </div>

                <form action="{{ route('account.update') }}" method="POST" class="account-settings-form" id="account-profile-form">
                    @csrf
                    @method('PUT')
                    <div class="account-field-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" value="{{ old('name', $user->name) }}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" value="{{ $user->email }}" disabled>
                        <div class="form-hint">Email cannot be changed here.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-input" value="{{ old('phone', $user->phone) }}" placeholder="+91…">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-input form-select">
                            <option value="">Select</option>
                            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('gender', $user->gender) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($user->role === 'mentee')
                    @php
                        $prefs = $preferences ?? (is_array($user->preferences) ? $user->preferences : []);
                        $selectedModes = old('session_modes', $prefs['session_modes'] ?? []);
                        if (! is_array($selectedModes)) {
                            $selectedModes = [];
                        }
                        $languageOptions = ['English', 'Hindi', 'Tamil', 'Telugu', 'Kannada', 'Malayalam', 'Bengali', 'Marathi'];
                        $savedLanguage = old('preferred_language', $prefs['preferred_language'] ?? '');
                        if ($savedLanguage !== '' && ! in_array($savedLanguage, $languageOptions, true)) {
                            array_unshift($languageOptions, $savedLanguage);
                        }
                        $savedTracks = old('tracks', $tracks ?? ($user->career_goals ?? []));
                        if (! is_array($savedTracks)) {
                            $savedTracks = [];
                        }
                    @endphp
                    <div class="form-group form-group--wide">
                        <label class="form-label">Address / Location</label>
                        <input type="text" name="address" class="form-input" maxlength="200" value="{{ old('address', $user->location) }}" placeholder="City, state or full address">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Education stream</label>
                        <select name="education_stream" class="form-input form-select">
                            <option value="">Select stream…</option>
                            @foreach($streams ?? [] as $stream)
                            <option value="{{ $stream }}" @selected(old('education_stream', $user->education_stream) === $stream)>{{ $stream }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Field of study</label>
                        <input type="text" name="field" class="form-input" maxlength="100" value="{{ old('field', $user->field) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">College / University</label>
                        <input type="text" name="college" class="form-input" maxlength="200" value="{{ old('college', $user->college) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year / Batch</label>
                        <input type="text" name="year" class="form-input" maxlength="50" value="{{ old('year', $user->year) }}">
                    </div>
                    <div class="form-group form-group--wide">
                        <label class="form-label">Career tracks</label>
                        <div id="tracks-hidden-inputs">
                            @foreach($savedTracks as $track)
                            <input type="hidden" name="tracks[]" value="{{ $track }}" data-onboard-hidden="tracks">
                            @endforeach
                        </div>
                        <div id="tracks-chips" class="account-chip-list">
                            @foreach($savedTracks as $track)
                            <span class="account-chip" data-chip-field="tracks" data-chip-value="{{ $track }}">{{ $track }}<button type="button" class="account-chip__remove" onclick="removeAccountChip(this)">×</button></span>
                            @endforeach
                        </div>
                        <div class="account-chip-add">
                            <input type="text" id="tracks-input" class="form-input" placeholder="Add a track, then press Enter">
                            <button type="button" class="btn btn-outline btn-sm" onclick="addAccountChips()">Add</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weekly time commitment</label>
                        <select name="weekly_time_commitment" class="form-input form-select">
                            <option value="">Select…</option>
                            @foreach(['1-3 hours' => '1–3 hours/week', '3-5 hours' => '3–5 hours/week', '5-10 hours' => '5–10 hours/week', '10+ hours' => '10+ hours/week'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('weekly_time_commitment', $prefs['weekly_time_commitment'] ?? '') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly budget</label>
                        <select name="monthly_budget" class="form-input form-select">
                            <option value="">Select…</option>
                            @foreach(['under_500' => 'Under ₹500', '500-1000' => '₹500 – ₹1,000', '1000-2500' => '₹1,000 – ₹2,500', '2500+' => '₹2,500+'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('monthly_budget', $prefs['monthly_budget'] ?? '') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-group--wide">
                        <label class="form-label">Preferred language</label>
                        <select name="preferred_language" class="form-input form-select">
                            <option value="">Select…</option>
                            @foreach($languageOptions as $lang)
                            <option value="{{ $lang }}" @selected($savedLanguage === $lang)>{{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form-group--wide">
                        <label class="form-label">Mentoring format</label>
                        <div class="account-mode-grid">
                            @foreach(['video' => ['📹', 'Video calls'], 'audio' => ['🎙️', 'Audio only'], 'chat' => ['💬', 'Text / chat'], 'in_person' => ['🤝', 'In-person']] as $val => [$icon, $label])
                            <label class="account-mode-option">
                                <input type="checkbox" name="session_modes[]" value="{{ $val }}" @checked(in_array($val, $selectedModes, true))>
                                <span>{{ $icon }}</span>
                                <span>{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    </div>

                    <button type="submit" class="btn btn-primary account-settings-form__submit">Save Changes</button>
                </form>
            </div>

            <div class="account-settings-lower">
            <div class="card account-settings-card">
                <h3 class="account-settings-card__title">Change Password</h3>
                <form action="{{ route('account.password') }}" method="POST" class="account-settings-form" id="change-password-form">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <div class="password-field">
                            <input type="password" name="current_password" class="form-input" required autocomplete="current-password">
                            @include('frontend.partials.password-toggle')
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div class="password-field">
                            <input type="password" name="password" id="new-password" class="form-input" required minlength="8" maxlength="100" autocomplete="new-password">
                            @include('frontend.partials.password-toggle')
                        </div>
                        <div class="form-hint">At least 8 characters, with uppercase, lowercase, and a number.</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <div class="password-field">
                            <input type="password" name="password_confirmation" id="new-password-confirmation" class="form-input" required minlength="8" maxlength="100" autocomplete="new-password">
                            @include('frontend.partials.password-toggle')
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary account-settings-form__submit">Update Password</button>
                </form>

                @if($user->role === 'mentor')
                <div class="account-settings-mentor-link">
                    <p>Want to edit your public mentor profile?</p>
                    <a href="{{ route('mentor.profile.edit') }}" class="btn btn-outline btn-sm account-settings-mentor-link__btn">Open Mentor Profile Editor →</a>
                </div>
                @endif
            </div>

            @include('account.partials.delete-account')
            </div>
        </div>
    </div>
</div>
<script>
function accountChipValue(span) {
    return (span.dataset.chipValue || span.textContent.replace(/×/g, '')).trim();
}
function syncAccountTracks() {
    const form = document.getElementById('account-profile-form');
    const holder = document.getElementById('tracks-hidden-inputs');
    if (!form || !holder) return;
    holder.innerHTML = '';
    form.querySelectorAll('[data-chip-field="tracks"]').forEach(function (span) {
        const value = accountChipValue(span);
        if (!value) return;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'tracks[]';
        input.value = value;
        input.dataset.onboardHidden = 'tracks';
        holder.appendChild(input);
    });
}
function addAccountChips() {
    const input = document.getElementById('tracks-input');
    const container = document.getElementById('tracks-chips');
    if (!input || !container) return;
    const raw = input.value.trim().replace(/,+$/, '');
    if (!raw) return;
    raw.split(',').map(function (value) { return value.trim(); }).filter(Boolean).forEach(function (value) {
        const span = document.createElement('span');
        span.className = 'account-chip';
        span.dataset.chipField = 'tracks';
        span.dataset.chipValue = value;
        span.append(document.createTextNode(value));
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'account-chip__remove';
        button.textContent = '×';
        button.addEventListener('click', function () { removeAccountChip(button); });
        span.appendChild(button);
        container.appendChild(span);
    });
    input.value = '';
    syncAccountTracks();
}
function removeAccountChip(button) {
    button.closest('span')?.remove();
    syncAccountTracks();
}
document.getElementById('tracks-input')?.addEventListener('keydown', function (event) {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        addAccountChips();
    }
});
document.getElementById('account-profile-form')?.addEventListener('submit', function () {
    const input = document.getElementById('tracks-input');
    if (input?.value.trim()) addAccountChips();
    syncAccountTracks();
});

document.getElementById('change-password-form')?.addEventListener('submit', function (event) {
    const password = document.getElementById('new-password').value;
    const confirmation = document.getElementById('new-password-confirmation').value;
    const strong = password.length >= 8 && /[a-z]/.test(password) && /[A-Z]/.test(password) && /\d/.test(password);
    if (!strong) {
        event.preventDefault();
        showToast('error', 'Password must be at least 8 characters and include uppercase, lowercase, and a number.');
        return;
    }
    if (password !== confirmation) {
        event.preventDefault();
        showToast('error', 'The new password and confirmation do not match.');
    }
});
</script>
@endsection
