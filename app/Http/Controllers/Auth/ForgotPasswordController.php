<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function showForm()
    {
        return view('frontend.auth.forget-password');
    }

    public function sendLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            $msg = 'Password reset link sent to your email!';
            if ($request->ajax()) return response()->json(['message' => $msg]);
            return back()->with('success', $msg);
        }

        $msg = 'No account found with that email address.';
        if ($request->ajax()) return response()->json(['message' => $msg], 422);
        return back()->withErrors(['email' => $msg]);
    }

    public function showReset(string $token, Request $request)
    {
        return view('frontend.auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email','password','password_confirmation','token'),
            function ($user, $password) {
                $data = ['password' => Hash::make($password)];

                if (Schema::hasColumn('users', 'remember_token')) {
                    $data['remember_token'] = Str::random(60);
                }

                $user->forceFill($data)->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success','Password reset! Please sign in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}