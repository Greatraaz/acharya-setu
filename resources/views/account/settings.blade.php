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

                <form action="{{ route('account.update') }}" method="POST" class="account-settings-form">
                    @csrf
                    @method('PUT')
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
                            <option value="">Prefer not to say</option>
                            @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('gender', $user->gender) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($user->role === 'mentee')
                    <div class="form-group">
                        <label class="form-label">Education stream</label>
                        <input type="text" name="education_stream" class="form-input" maxlength="100" value="{{ old('education_stream', $user->education_stream) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">College</label>
                        <input type="text" name="college" class="form-input" maxlength="200" value="{{ old('college', $user->college) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Year</label>
                        <input type="text" name="year" class="form-input" maxlength="50" value="{{ old('year', $user->year) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Goals</label>
                        <input type="text" name="goals" class="form-input" maxlength="500" value="{{ old('goals', implode(', ', $user->career_goals ?? [])) }}" placeholder="Separate goals with commas">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred language</label>
                        <input type="text" name="preferred_language" class="form-input" maxlength="100" value="{{ old('preferred_language', $user->preferences['preferred_language'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mentoring format</label>
                        <select name="mentoring_format" class="form-input form-select">
                            <option value="">Keep current</option>
                            @foreach(['video' => 'Video', 'audio' => 'Audio', 'chat' => 'Chat', 'hybrid' => 'Hybrid'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('mentoring_format', $user->preferences['mentoring_format'] ?? '') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @php
                        $notes = $user->preferences['notifications'] ?? [];
                    @endphp
                    <div class="form-group">
                        <label class="form-label">Notifications</label>
                        <input type="hidden" name="notifications[session_reminders]" value="0">
                        <label class="form-hint">
                            <input type="checkbox" name="notifications[session_reminders]" value="1" @checked(old('notifications.session_reminders', $notes['session_reminders'] ?? false))>
                            Session reminders
                        </label>
                        <input type="hidden" name="notifications[product_updates]" value="0">
                        <label class="form-hint">
                            <input type="checkbox" name="notifications[product_updates]" value="1" @checked(old('notifications.product_updates', $notes['product_updates'] ?? false))>
                            Product updates
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary account-settings-form__submit">Save Changes</button>
                </form>
            </div>

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
        </div>

        @include('account.partials.delete-account')
    </div>
</div>
<script>
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
