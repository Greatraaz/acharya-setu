<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleAccountService;
use App\Support\LoginThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
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

    public function showComplete(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('google_pending_signup');
        if (! is_array($pending) || blank($pending['email'] ?? null)) {
            return redirect()
                ->route('register')
                ->with('error', 'Start again with Google and choose your role.');
        }

        return view('frontend.auth.google-complete', [
            'googleName' => (string) ($pending['name'] ?? ''),
            'googleEmail' => (string) $pending['email'],
            'defaultRole' => old('role', 'mentee'),
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('google_pending_signup');
        if (! is_array($pending) || blank($pending['id'] ?? null) || blank($pending['email'] ?? null)) {
            return redirect()
                ->route('register')
                ->with('error', 'Google signup expired. Please continue with Google again.');
        }

        $request->validate([
            'role' => 'required|in:mentor,mentee,expert',
            'accepted_terms' => 'accepted',
        ], [
            'accepted_terms.accepted' => 'Please accept the Terms & Conditions.',
        ]);

        try {
            $user = $this->google->findOrCreate(
                [
                    'id' => (string) $pending['id'],
                    'email' => (string) $pending['email'],
                    'name' => (string) ($pending['name'] ?? ''),
                    'avatar' => $pending['avatar'] ?? null,
                    'email_verified' => (bool) ($pending['email_verified'] ?? false),
                ],
                $request->input('role'),
                true
            );
        } catch (RuntimeException $e) {
            return redirect()
                ->route('auth.google.complete')
                ->withErrors(['google' => $e->getMessage()])
                ->with('error', $e->getMessage())
                ->withInput();
        }

        $request->session()->forget('google_pending_signup');

        if (LoginThrottle::isDisabled($user)) {
            return $this->fail('This account has been disabled.', 'register');
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        return redirect()
            ->to($user->postAuthRedirectUrl())
            ->with('success', 'Signed in with Google. Complete your profile before taking sessions.');
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
            // New Google user came from Login (no role) — ask them to pick one.
            if (isset($profile) && $this->needsRoleSelection($e->getMessage())) {
                $request->session()->put('google_pending_signup', $profile);

                return redirect()
                    ->route('auth.google.complete')
                    ->with('info', 'Choose how you want to join Vedrix to finish Google signup.');
            }

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

    private function needsRoleSelection(string $message): bool
    {
        return str_contains($message, 'Choose Mentee, Mentor, or Expert')
            || str_contains($message, 'Terms & Conditions');
    }

    private function fail(string $message, string $intent = 'login'): RedirectResponse
    {
        return redirect()
            ->route($intent === 'register' ? 'register' : 'login')
            ->withErrors(['google' => $message])
            ->with('error', $message);
    }
}
