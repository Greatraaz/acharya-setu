<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    // Show registration form
    public function showForm(Request $request)
    {
        return view('frontend.auth.register', [
            'defaultRole' => $request->query('role', 'mentee'),
        ]);
    }

    // Handle registration submission (after OTP verified)
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'phone'     => 'required|string',
            'password'  => ['required', Password::min(8)],
            'role'      => 'required|in:mentor,mentee',
            'email_otp' => 'required|string|size:6',
            'phone_otp' => 'required|string|size:6',
        ]);

        // Verify email OTP
        /* $emailOk = OtpCode::verify($request->email, 'email', $request->email_otp);
        if (! $emailOk) {
            return $this->otpError($request, 'email_otp', 'Invalid or expired email OTP.');
        } */

        // Verify phone OTP
        /* $phoneOk = OtpCode::verify($request->phone, 'phone', $request->phone_otp);
        if (! $phoneOk) {
            return $this->otpError($request, 'phone_otp', 'Invalid or expired mobile OTP.');
        } */

        // Create user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'mentor_status'         => $request->role === 'mentor' ? 'pending' : 'approved',
            'onboarding_completed'  => false,
            'onboarding_step'       => 0,
            'is_active'             => true,
        ]);

        Auth::guard('admin')->login($user);
   

        $redirect = match ($user->role) {
            'mentor' => route('mentor.onboarding', ['step' => 1]),
            'mentee' => route('mentee.onboarding', ['step' => 1]),
            default  => route('dashboard'),
        };

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message'  => 'Account created successfully!',
                'redirect' => $redirect,
                'user'     => $user->only('id','name','email','role'),
            ]);
        }

        return redirect($redirect);
    }

    private function otpError(Request $request, string $field, string $msg)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => $msg, 'errors' => [$field => [$msg]]], 422);
        }
        return back()->withErrors([$field => $msg])->withInput();
    }
}