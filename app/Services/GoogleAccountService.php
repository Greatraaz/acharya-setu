<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\User;
use App\Support\RegistrationData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleAccountService
{
    public function isConfigured(): bool
    {
        return $this->clientId() !== '' && $this->clientSecret() !== '';
    }

    public function clientId(): string
    {
        return trim((string) (
            config('services.google.client_id')
            ?: AppSetting::get('google_client_id')
            ?: ''
        ));
    }

    public function redirectUri(): string
    {
        return rtrim((string) config('services.google.redirect', url('/auth/google/callback')), '/');
    }

    public function authorizationUrl(string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);
    }

    /**
     * @return array{id:string,email:string,name:string,avatar:?string,email_verified:bool}
     */
    public function userFromCode(string $code): array
    {
        $tokenResponse = Http::asForm()->acceptJson()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (! $tokenResponse->successful()) {
            throw new RuntimeException('Google did not return an access token.');
        }

        $accessToken = (string) $tokenResponse->json('access_token');
        if ($accessToken === '') {
            throw new RuntimeException('Google did not return an access token.');
        }

        return $this->userFromAccessToken($accessToken);
    }

    /**
     * @return array{id:string,email:string,name:string,avatar:?string,email_verified:bool}
     */
    public function userFromAccessToken(string $accessToken): array
    {
        $profile = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (! $profile->successful()) {
            throw new RuntimeException('Could not load your Google profile.');
        }

        return $this->normalizeProfile($profile->json() ?? []);
    }

    /**
     * @return array{id:string,email:string,name:string,avatar:?string,email_verified:bool}
     */
    public function userFromIdToken(string $idToken): array
    {
        $response = Http::acceptJson()->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Google token is invalid or expired.');
        }

        $payload = $response->json() ?? [];
        $audience = (string) ($payload['aud'] ?? '');
        if ($audience !== '' && $audience !== $this->clientId()) {
            throw new RuntimeException('Google token was issued for a different app.');
        }

        return $this->normalizeProfile($payload);
    }

    /**
     * @param  array{id:string,email:string,name:string,avatar:?string,email_verified:bool}  $google
     */
    public function findOrCreate(array $google, ?string $role = null, bool $acceptedTerms = false): User
    {
        $existing = User::query()
            ->where(function ($query) use ($google) {
                $query->where('google_id', $google['id'])
                    ->orWhereRaw('LOWER(email) = ?', [$google['email']]);
            })
            ->first();

        if ($existing) {
            return $this->linkExisting($existing, $google);
        }

        $role = in_array($role, ['mentor', 'mentee', 'expert'], true) ? $role : null;
        if ($role === null) {
            throw new RuntimeException('Choose Mentee, Mentor, or Expert, then continue with Google.');
        }

        if (! $acceptedTerms) {
            throw new RuntimeException('Please accept the Terms & Conditions to create an account.');
        }

        $user = User::create([
            'name' => $google['name'] !== '' ? $google['name'] : Str::before($google['email'], '@'),
            'email' => $google['email'],
            'google_id' => $google['id'],
            'auth_provider' => 'google',
            'avatar_url' => $google['avatar'],
            'password' => Hash::make(Str::random(40)),
            'role' => $role,
            'mentor_status' => in_array($role, ['mentor', 'expert'], true) ? User::MENTOR_STATUS_PENDING : User::MENTOR_STATUS_APPROVED,
            'onboarding_completed' => false,
            'onboarding_step' => 0,
            'is_active' => true,
            'isVerifiedEmail' => $google['email_verified'] ? 1 : 0,
            'email_verified_at' => $google['email_verified'] ? now() : null,
            'terms_accepted_at' => now(),
            'terms_version' => RegistrationData::TERMS_VERSION,
        ]);

        if ($user->isSessionHost()) {
            try {
                app(NotificationService::class)->notifyAdmins(
                    'google_host_signup',
                    ($user->isExpert() ? 'Expert' : 'Mentor').' signed up with Google',
                    "{$user->name} created a {$user->role} account with Google and is waiting for approval.",
                    ['user_id' => $user->id],
                    $user->isExpert() ? route('admin.experts.index') : route('admin.mentors.approvals')
                );
            } catch (\Throwable) {
                // Account is created even if the admin alert cannot be stored.
            }
        }

        return $user;
    }

    /**
     * @param  array{id:string,email:string,name:string,avatar:?string,email_verified:bool}  $google
     */
    private function linkExisting(User $user, array $google): User
    {
        if ($user->role === 'admin') {
            throw new RuntimeException('Admin accounts must sign in from the admin login.');
        }

        if (filled($user->google_id) && $user->google_id !== $google['id']) {
            throw new RuntimeException('This email is already linked to a different Google account.');
        }

        $updates = [
            'google_id' => $user->google_id ?: $google['id'],
        ];

        if (blank($user->avatar_url) && filled($google['avatar'])) {
            $updates['avatar_url'] = $google['avatar'];
        }

        if ($google['email_verified'] && ! $user->isVerifiedEmail) {
            $updates['isVerifiedEmail'] = 1;
            $updates['email_verified_at'] = $user->email_verified_at ?? now();
        }

        $user->update($updates);

        return $user->fresh();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{id:string,email:string,name:string,avatar:?string,email_verified:bool}
     */
    private function normalizeProfile(array $payload): array
    {
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $id = trim((string) ($payload['sub'] ?? $payload['id'] ?? ''));

        if ($email === '' || $id === '') {
            throw new RuntimeException('Google did not share an email address. Allow email access and try again.');
        }

        $verified = filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'id' => $id,
            'email' => $email,
            'name' => trim((string) ($payload['name'] ?? $payload['given_name'] ?? '')),
            'avatar' => filled($payload['picture'] ?? null) ? (string) $payload['picture'] : null,
            'email_verified' => $verified,
        ];
    }

    private function clientSecret(): string
    {
        $fromConfig = trim((string) config('services.google.client_secret', ''));
        if ($fromConfig !== '') {
            return $fromConfig;
        }

        $stored = (string) (AppSetting::get('google_client_secret') ?? '');
        if ($stored === '' || strlen($stored) > 80 || str_starts_with($stored, 'eyJpdiI6')) {
            return '';
        }

        return $stored;
    }
}
