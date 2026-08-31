<div class="session-register-card">
    <div class="session-register-card__head">
        <span>📝</span>
        <h3>Reserve Your Spot</h3>
    </div>
    <p class="session-register-card__sub">Free access for students, early professionals & career builders.</p>

    @if(session('registration_success'))
        <div class="session-register-success">
            Thank you! Your registration has been received. We will share session details on your email.
        </div>
    @else
        <form method="POST" action="{{ route($registerRoute, $session->slug) }}" class="session-register-form">
            @csrf
            <div>
                <label for="reg-full-name">Full Name *</label>
                <input id="reg-full-name" type="text" name="full_name" value="{{ old('full_name', auth()->user()?->name ?? '') }}" required placeholder="Your name">
                @error('full_name')<p class="session-register-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="reg-email">Business Email *</label>
                <input id="reg-email" type="email" name="email" value="{{ old('email', auth()->user()?->email ?? '') }}" required placeholder="name@company.com">
                @error('email')<p class="session-register-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="reg-company">Company *</label>
                <input id="reg-company" type="text" name="company" value="{{ old('company') }}" required placeholder="Organisation Name">
                @error('company')<p class="session-register-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="reg-phone">Phone Number</label>
                <input id="reg-phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="+91">
                @error('phone')<p class="session-register-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="session-register-btn">Register Now →</button>
        </form>
    @endif
</div>
