<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Mail\OtpMail;
use App\Services\SmsService;
use App\Support\IndianPhone;
use App\Support\RegistrationData;
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

        $emailOtp = $this->generateOtp();
        OtpCode::storeOtp($email, 'email', $emailOtp);

        // Phone OTP disabled for web registration
        // $phoneOtp = $this->generateOtp();
        // if ($request->filled('phone')) {
        //     OtpCode::storeOtp($request->phone, 'phone', $phoneOtp);
        //     SmsService::sendOtp($request->phone, $phoneOtp);
        // }

        // ── Send Email OTP ────────────────────────────────────
        try {
            Mail::to($email)->send(new OtpMail($emailOtp, 'registration'));
        } catch (\Throwable $e) {
            Log::error('OTP email failed: ' . $e->getMessage());
        }

        return response()->json([
            'message'    => 'OTP sent to your email.',
            'expires_in' => 600,
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

        $otp = $this->generateOtp();
        OtpCode::storeOtp($request->phone, 'phone', $otp);

        SmsService::sendOtp($request->phone, $otp);

        return response()->json(['message' => 'OTP sent to your phone.', 'expires_in' => 600]);
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
            'otp'        => 'required|string|size:6',
            'channel'    => 'required|in:email,phone',
        ]);

        $ok = OtpCode::verify($request->identifier, $request->channel, $request->otp);

        if (! $ok) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
        }

        return response()->json(['message' => 'OTP verified.', 'verified' => true]);
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
            'otp'   => 'required|string|size:6',
        ]);

        $ok = OtpCode::verify($request->phone, 'phone', $request->otp);
        if (! $ok) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 422);
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

    private function generateOtp(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

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