<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAccountService;
use App\Support\LoginThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleAuthController extends Controller
{
    public function __construct(private readonly GoogleAccountService $google)
    {
    }

    public function login(Request $request): RedirectResponse
    {
        return $this->start($request, 'login');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => 'required|in:mentor,mentee,expert',
            'accepted_terms' => 'accepted',
        ], [
            'accepted_terms.accepted' => 'Please accept the Terms & Conditions.',
        ]);

        return $this->start($request, 'register');
    }

    private function start(Request $request, string $intent): RedirectResponse
    {
        if (! $this->google->isConfigured()) {
            return $this->fail('Google sign-in is not configured yet. Use email, or ask an admin to add Google credentials.', $intent);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth', [
            'state' => $state,
            'intent' => $intent,
            'role' => $intent === 'register' ? $request->input('role') : null,
            'accepted_terms' => $intent === 'register',
        ]);

        return redirect()->away($this->google->authorizationUrl($state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $payload = $request->session()->pull('google_oauth', []);
        $intent = (string) ($payload['intent'] ?? 'login');

        if ($request->filled('error')) {
            return $this->fail('Google sign-in was cancelled.', $intent);
        }

        $state = (string) $request->query('state', '');
        if ($state === '' || $state !== ($payload['state'] ?? null)) {
            return $this->fail('Google sign-in expired. Please try again.', $intent);
        }

        $code = (string) $request->query('code', '');
        if ($code === '') {
            return $this->fail('Google did not return an authorization code.', $intent);
        }

        try {
            $profile = $this->google->userFromCode($code);
            $user = $this->google->findOrCreate(
                $profile,
                $payload['role'] ?? null,
                (bool) ($payload['accepted_terms'] ?? false)
            );
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), $intent);
        }

        if (LoginThrottle::isDisabled($user)) {
            return $this->fail('This account has been disabled.', $intent);
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        $isNew = $user->wasRecentlyCreated;
        $message = $isNew
            ? 'Signed in with Google. Complete your profile before taking sessions.'
            : 'Welcome back!';

        return redirect()
            ->to($user->postAuthRedirectUrl())
            ->with('success', $message);
    }

    private function fail(string $message, string $intent = 'login'): RedirectResponse
    {
        return redirect()
            ->route($intent === 'register' ? 'register' : 'login')
            ->withErrors(['google' => $message])
            ->with('error', $message);
    }
}
