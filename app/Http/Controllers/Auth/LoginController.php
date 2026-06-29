<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::guard('web')->attempt($request->only('email','password'), $request->boolean('remember'))) {
            $msg = 'These credentials do not match our records.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            throw ValidationException::withMessages(['email' => $msg]);
        }

        $request->session()->regenerate();

        $redirect = $this->redirectAfterLogin(auth()->user());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message'  => 'Welcome back!',
                'redirect' => $redirect,
                'user'     => auth()->user()->only('id','name','email','role'),
            ]);
        }

        return redirect()->intended($redirect);
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    // Account settings update (shared for mentor+mentee)
    public function updateAccount(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'phone'  => 'nullable|string',
            'gender' => 'nullable|string',
        ]);

        $user->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Account updated.']);
        }
        return back()->with('success','Account updated.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $path = $request->file('avatar')->store('avatars','public');
        auth()->user()->update(['avatar_url' => '/storage/'.$path]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Photo updated.', 'url' => '/storage/'.$path]);
        }
        return back()->with('success','Photo updated.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:8',
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