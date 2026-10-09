<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use App\Support\IndianPhone;
use App\Support\RegistrationData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

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
        RegistrationData::normalize($request);

        $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => ['required', 'email:filter', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'phone'          => IndianPhone::rules(required: true),
            'password'       => ['required', Password::min(8)],
            'role'           => 'required|in:mentor,mentee',
            'email_otp'      => 'required|string|size:6',
            'accepted_terms' => 'required|accepted',
        ]);

        if (RegistrationData::emailTaken($request->email)) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered.',
            ]);
        }

        if (RegistrationData::phoneTaken($request->phone)) {
            throw ValidationException::withMessages([
                'phone' => 'This mobile number is already registered.',
            ]);
        }

        $emailOk = OtpCode::verify($request->email, 'email', $request->email_otp);
        if (! $emailOk) {
            return $this->otpError($request, 'email_otp', 'Invalid or expired email OTP.');
        }

        // Phone OTP verification disabled
        /* $phoneOk = OtpCode::verify($request->phone, 'phone', $request->phone_otp);
        if (! $phoneOk) {
            return $this->otpError($request, 'phone_otp', 'Invalid or expired mobile OTP.');
        } */

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'phone'                 => IndianPhone::normalize($request->phone),
                'password'              => Hash::make($request->password),
                'role'                  => $request->role,
                'mentor_status'         => $request->role === 'mentor' ? 'pending' : 'approved',
                'onboarding_completed'  => false,
                'onboarding_step'       => 0,
                'is_active'             => true,
                'terms_accepted_at'     => now(),
                'terms_version'         => RegistrationData::TERMS_VERSION,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) !== 1062) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'email' => 'This email is already registered.',
            ]);
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

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