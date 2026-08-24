@extends('frontend.layouts.app')
@section('title', 'Profile Settings — Vedrix')

@section('content')
@php $user = $user ?? auth()->user(); @endphp
<div class="dash-layout">
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
        <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:16px;">
            <span class="alert-icon">❌</span>
            <div>
                <ul style="margin:0;padding-left:16px;font-size:13px;">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
            {{-- Account info --}}
            <div class="card">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Basic Information</h3>

                <div style="display:flex;gap:16px;align-items:center;margin-bottom:18px;">
                    <div style="width:72px;height:72px;border-radius:16px;overflow:hidden;background:var(--brand-muted);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800;color:var(--brand);flex-shrink:0;">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="" style="width:100%;height:100%;object-fit:cover;" id="settings-avatar-img">
                        @else
                            <span id="settings-avatar-fallback">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <form action="{{ route('account.avatar') }}" method="POST" enctype="multipart/form-data" id="avatar-form">
                        @csrf
                        <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display:none;" onchange="this.form.submit()">
                        <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('avatar-input').click()">Change Photo</button>
                        <div class="form-hint" style="margin-top:6px;">JPG/PNG up to 2MB</div>
                    </form>
                </div>

                <form action="{{ route('account.update') }}" method="POST">
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
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            {{-- Password --}}
            <div class="card">
                <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Change Password</h3>
                <form action="{{ route('account.password') }}" method="POST">
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
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>

                @if($user->role === 'mentor')
                <div style="margin-top:24px;padding-top:18px;border-top:1px solid var(--border);">
                    <div style="font-size:13px;color:var(--text-2);margin-bottom:10px;">Want to edit your public mentor profile?</div>
                    <a href="{{ route('mentor.profile.edit') }}" class="btn btn-outline btn-sm">Open Mentor Profile Editor →</a>
                </div>
                @endif
            </div>
        </div>

        @include('account.partials.delete-account')
    </div>
</div>
@endsection
