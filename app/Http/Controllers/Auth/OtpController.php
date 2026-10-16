<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Mail\OtpMail;
use App\Services\SmsService;
use App\Support\IndianPhone;
use App\Support\RegistrationData;
use App\Support\SensitiveLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    /*
    |-----------------------------------------------------------
    | SEND OTP — email + phone (registration)
    |-----------------------------------------------------------
    | POST /auth/send-otp
    | Body: { email, phone }
    */
    public function send(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email')));
        $phone = trim((string) $request->input('phone', ''));
        $request->merge(['email' => $email, 'phone' => $phone]);

        $request->validate([
            'email' => 'required|email:filter|max:255',
            'phone' => IndianPhone::rules(required: true),
        ]);

        if (RegistrationData::emailTaken($email)) {
            return response()->json([
                'message' => 'This email is already registered.',
                'errors'  => ['email' => ['This email is already registered.']],
            ], 422);
        }

        if (RegistrationData::phoneTaken($phone)) {
            return response()->json([
                'message' => 'This mobile number is already registered.',
                'errors'  => ['phone' => ['This mobile number is already registered.']],
            ], 422);
        }

        $issued = OtpCode::issue($email, 'email', 'registration');
        if (! $issued['ok']) {
            return response()->json([
                'message'     => $issued['message'],
                'retry_after' => $issued['retry_after'] ?? null,
            ], $issued['status']);
        }

        try {
            Mail::to($issued['identifier'])->send(new OtpMail($issued['otp'], 'registration'));
        } catch (\Throwable $e) {
            Log::error('OTP email failed: ' . SensitiveLog::redact($e->getMessage()));
        }

        return response()->json([
            'message'     => 'OTP sent to your email.',
            'expires_in'  => $issued['expires_in'],
            'retry_after' => OtpCode::COOLDOWN_SECONDS,
        ]);
    }

    /*
    |-----------------------------------------------------------
    | SEND LOGIN OTP — phone only
    |-----------------------------------------------------------
    | POST /auth/send-login-otp
    | Body: { phone }
    */
    public function sendLogin(Request $request)
    {
        $request->validate(['phone' => 'required|string|min:10']);

        // Match 8787878787, +918787878787, +91 8787878787, etc.
        $user = User::findByPhone($request->phone);
        if (! $user) {
            return response()->json(['message' => 'No account found with this phone number.'], 404);
        }

        $issued = OtpCode::issue($request->phone, 'phone', 'login');
        if (! $issued['ok']) {
            return response()->json([
                'message'     => $issued['message'],
                'retry_after' => $issued['retry_after'] ?? null,
            ], $issued['status']);
        }

        SmsService::sendOtp($issued['identifier'], $issued['otp']);

        return response()->json([
            'message'     => 'OTP sent to your phone.',
            'expires_in'  => $issued['expires_in'],
            'retry_after' => OtpCode::COOLDOWN_SECONDS,
        ]);
    }

    /*
    |-----------------------------------------------------------
    | VERIFY OTP  (standalone — used during registration)
    |-----------------------------------------------------------
    | POST /auth/verify-otp
    | Body: { identifier, otp, channel: 'email'|'phone' }
    */
    public function verify(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp'        => 'required|digits:6',
            'channel'    => 'required|in:email,phone',
            'type'       => 'nullable|in:registration,login,reset',
        ]);

        $result = OtpCode::verify(
            $request->identifier,
            $request->channel,
            $request->otp,
            $request->input('type')
        );

        if ($result !== 'ok') {
            return response()->json(['message' => OtpCode::message($result), 'verified' => false], 422);
        }

        return response()->json(['message' => OtpCode::message('ok'), 'verified' => true]);
    }

    /*
    |-----------------------------------------------------------
    | LOGIN WITH PHONE OTP
    |-----------------------------------------------------------
    | POST /auth/login-otp
    | Body: { phone, otp }
    */
    public function loginWithOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp'   => 'required|digits:6',
        ]);

        $result = OtpCode::verify($request->phone, 'phone', $request->otp, 'login');
        if ($result !== 'ok') {
            return response()->json(['message' => OtpCode::message($result)], 422);
        }

        $user = User::findByPhone($request->phone);
        if (! $user) {
            return response()->json(['message' => 'Account not found.'], 404);
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        $redirect = $this->redirectForUser($user);

        return response()->json([
            'message'  => 'Welcome back!',
            'redirect' => $redirect,
            'user'     => $user->only('id','name','email','role'),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function redirectForUser(User $user): string
    {
        if ($user->role === 'admin') return route('admin.dashboard');
        if ($user->role === 'mentor') {
            if (! $user->onboarding_completed) {
                $step = max(1, $user->onboarding_step + 1);
                return route('mentor.onboarding', ['step' => min($step, 5)]);
            }
            if ($user->mentor_status === 'pending') return route('mentor.onboarding.pending');
            return route('mentor.dashboard');
        }
        if (! $user->onboarding_completed) {
            $step = max(1, $user->onboarding_step + 1);
            return route('mentee.onboarding', ['step' => min($step, 4)]);
        }
        return route('mentee.dashboard');
    }
}