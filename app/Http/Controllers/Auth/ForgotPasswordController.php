<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LoginThrottle;
use App\Support\PasswordReset as PasswordResetGuard;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('frontend.auth.forget-password');
    }

    public function sendLink(Request $request)
    {
        $email = PasswordResetGuard::normalizeEmail((string) $request->input('email', ''));
        $request->merge(['email' => $email]);
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $email)->first();
        $msg = 'If an account exists for this email, a reset link has been sent.';

        if ($user && ! LoginThrottle::isDisabled($user)) {
            try {
                $status = Password::sendResetLink(['email' => $email]);
            } catch (\Throwable $e) {
                report($e);
                $status = Password::RESET_LINK_SENT;
            }

            if ($status === Password::RESET_THROTTLED) {
                $msg = 'Please wait a minute before requesting another reset link.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => $msg], 429);
                }

                return back()->withErrors(['email' => $msg]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function showReset(Request $request, ?string $token = null)
    {
        $token = $token ?: (string) $request->query('token', '');

        if ($token === '') {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'This password reset link is invalid. Please request a new one.']);
        }

        return view('frontend.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', $request->input('email')),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->merge([
            'email' => PasswordResetGuard::normalizeEmail((string) $request->input('email', '')),
        ]);

        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $outcome = PasswordResetGuard::inspect($request->email, $request->token);
        if ($outcome !== 'ok') {
            $msg = PasswordResetGuard::message($outcome);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors(['email' => $msg])->withInput($request->only('email'));
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                PasswordResetGuard::apply($user, $password);
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $msg = 'Password reset. Please sign in with your new password.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $msg, 'redirect' => route('login')]);
            }

            return redirect()->route('login')->with('success', $msg);
        }

        $msg = PasswordResetGuard::message('invalid');
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => $msg], 422);
        }

        return back()->withErrors(['email' => $msg])->withInput($request->only('email'));
    }
}