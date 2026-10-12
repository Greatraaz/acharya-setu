<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Support\IndianPhone;
use App\Support\LoginThrottle;
use App\Support\ProfileUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // Show login page
    public function showForm()
    {
        return view('frontend.auth.login');
    }

    // Email + password login
    public function login(Request $request)
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
        ]);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($wait = LoginThrottle::availableIn($request->email, $request->ip() ?: 'local')) {
            $msg = LoginThrottle::lockedMessage($wait);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 429);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        $remember = $request->boolean('remember');
        if (! $remember) {
            config(['session.expire_on_close' => true]);
        }

        if (! Auth::guard('web')->attempt($request->only('email','password'), $remember)) {
            LoginThrottle::hit($request->email, $request->ip() ?: 'local');
            $msg = 'These credentials do not match our records.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        $user = Auth::guard('web')->user();
        if (! $user || LoginThrottle::isDisabled($user)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $msg = 'This account has been disabled.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 403);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        LoginThrottle::clear($request->email, $request->ip() ?: 'local');
        $request->session()->regenerate();

        $redirect = $this->redirectAfterLogin($user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message'  => 'Welcome back!',
                'redirect' => $redirect,
                'user'     => $user->only('id','name','email','role'),
            ]);
        }

        return redirect()->to($redirect);
    }

    // Logout
    public function logout(Request $request)
    {
        $role = Auth::guard('web')->user()?->role;

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($role === 'admin') {
            return redirect()->route('admin.login');
        }

        return redirect()->route('login')->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, private',
            'Pragma'        => 'no-cache',
        ]);
    }

    // Account settings update (shared for mentor+mentee)
    public function updateAccount(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'name' => 'required|string|max:100',
            'phone' => IndianPhone::rules(),
            'gender' => 'nullable|in:male,female,other',
            'notifications' => 'nullable|array',
            'notifications.session_reminders' => 'nullable|boolean',
            'notifications.product_updates' => 'nullable|boolean',
        ];
        if ($user->role === 'mentee') {
            $rules = array_merge($rules, [
                'education_stream' => 'nullable|string|max:100',
                'college' => 'nullable|string|max:200',
                'year' => 'nullable|string|max:50',
                'goals' => 'nullable|string|max:500',
                'preferred_language' => 'nullable|string|max:100',
                'mentoring_format' => 'nullable|in:video,audio,chat,hybrid',
                'weekly_time_commitment' => 'nullable|string|max:100',
                'monthly_budget' => 'nullable|string|max:100',
            ]);
        }

        $data = $request->validate($rules);
        $data = $this->profilePayload($user, $data);
        $result = ProfileUpdate::save($user, $data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => $result['message']]);
        }
        return back()->with('success', $result['message']);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);
        $url = ProfileUpdate::storeAvatar(auth()->user(), $request->file('avatar'));

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Photo updated.', 'url' => $url]);
        }
        return back()->with('success', 'Photo updated.');
    }

    public function removeAvatar(Request $request)
    {
        ProfileUpdate::clearAvatar(auth()->user());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Photo removed.']);
        }
        return back()->with('success', 'Photo removed.');
    }

    private function profilePayload($user, array $data): array
    {
        $preferences = [];
        foreach (['preferred_language', 'mentoring_format', 'weekly_time_commitment', 'monthly_budget'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                $preferences[$key] = $data[$key];
            }
            unset($data[$key]);
        }
        if (! empty($data['notifications']) && is_array($data['notifications'])) {
            $preferences['notifications'] = [
                'session_reminders' => filter_var($data['notifications']['session_reminders'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'product_updates' => filter_var($data['notifications']['product_updates'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];
        }
        unset($data['notifications']);

        if (array_key_exists('goals', $data)) {
            $data['career_goals'] = collect(preg_split('/\s*,\s*/', (string) $data['goals']))
                ->map(fn ($goal) => trim((string) $goal))
                ->filter()
                ->unique()
                ->values()
                ->all();
            unset($data['goals']);
        }
        if ($preferences !== []) {
            $data['preferences'] = $preferences;
        }

        unset($data['email'], $data['role'], $data['mentor_status'], $data['password']);

        return $data;
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'password.confirmed' => 'The new password and confirmation do not match.',
        ]);

        $user = auth()->user();
        if (! Hash::check($request->current_password, $user->password)) {
            $msg = 'Current password is incorrect.';
            if ($request->ajax()) return response()->json(['message' => $msg], 422);
            return back()->withErrors(['current_password' => $msg]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        if ($request->ajax()) return response()->json(['message' => 'Password changed.']);
        return back()->with('success','Password changed successfully.');
    }

    public function destroyAccount(Request $request)
    {
        $user = $request->user();

        abort_unless(in_array($user->role, ['mentor', 'mentee'], true), 403);

        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'Your password is incorrect.',
        ]);

        $user->deleteAccount();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Your account has been deleted.');
    }

    // Determine where to redirect after login
    private function redirectAfterLogin(User $user): string
    {
        if ($user->role === 'admin') {
            return route('admin.dashboard');
        }

        if ($user->role === 'mentor') {
            if (! $user->onboarding_completed) {
                $step = max(1, $user->onboarding_step + 1);
                return route('mentor.onboarding', ['step' => min($step, 5)]);
            }
            if ($user->mentor_status === 'pending') {
                return route('mentor.onboarding.pending');
            }
            return route('mentor.dashboard');
        }

        // Mentee
        if (! $user->onboarding_completed) {
            $step = max(1, $user->onboarding_step + 1);
            return route('mentee.onboarding', ['step' => min($step, 4)]);
        }
        return route('mentee.dashboard');
    }
}