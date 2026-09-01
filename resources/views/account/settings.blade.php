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
                        <input type="file" name="avatar" id="avatar-input" accept="image/*" class="sr-only" onchange="this.form.submit()">
                        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('avatar-input').click()">Change Photo</button>
                        <div class="form-hint account-settings-avatar__hint">JPG/PNG up to 2MB</div>
                    </form>
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
                    <button type="submit" class="btn btn-primary account-settings-form__submit">Save Changes</button>
                </form>
            </div>

            <div class="card account-settings-card">
                <h3 class="account-settings-card__title">Change Password</h3>
                <form action="{{ route('account.password') }}" method="POST" class="account-settings-form">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-input" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-input" required minlength="8" autocomplete="new-password">
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
@endsection
