<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string|min:10',
        ]);

        // ── Generate 6-digit codes ────────────────────────────
        $emailOtp = $this->generateOtp();
        $phoneOtp = $this->generateOtp();

        // ── Persist to DB ─────────────────────────────────────
        OtpCode::storeOtp($request->email, 'email', $emailOtp);
        OtpCode::storeOtp($request->phone, 'phone', $phoneOtp);

        // ── Send Email OTP ────────────────────────────────────
        try {
            Mail::to($request->email)->send(new OtpMail($emailOtp, 'registration'));
        } catch (\Throwable $e) {
            Log::error('OTP email failed: ' . $e->getMessage());
        }

        // ── Send SMS OTP ──────────────────────────────────────
        $this->sendSms($request->phone, "Your AcharyaSetu OTP is {$phoneOtp}. Valid for 10 minutes. Do not share.");

        return response()->json([
            'message'    => 'OTPs sent to your email and phone.',
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

        // Ensure user exists
        $user = User::where('phone', $request->phone)->first();
        if (! $user) {
            return response()->json(['message' => 'No account found with this phone number.'], 404);
        }

        $otp = $this->generateOtp();
        OtpCode::storeOtp($request->phone, 'phone', $otp);

        $this->sendSms($request->phone, "Your AcharyaSetu login OTP is {$otp}. Valid for 10 minutes.");

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

        $user = User::where('phone', $request->phone)->first();
        if (! $user) {
            return response()->json(['message' => 'Account not found.'], 404);
        }

        Auth::login($user, true);
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

    private function sendSms(string $phone, string $message): void
    {
        $provider = config('services.sms.provider', 'msg91'); // msg91 | fast2sms | sns

        try {
            match ($provider) {
                'msg91'     => $this->sendViaMSG91($phone, $message),
                'fast2sms'  => $this->sendViaFast2SMS($phone, $message),
                default     => Log::info("SMS (no provider): To {$phone}: {$message}"),
            };
        } catch (\Throwable $e) {
            Log::error("SMS send failed [{$provider}]: " . $e->getMessage());
        }
    }

    private function sendViaMSG91(string $phone, string $message): void
    {
        $authKey  = config('services.msg91.auth_key');
        $senderId = config('services.msg91.sender_id', 'ACHSETU');
        $route    = config('services.msg91.route', '4');

        // Ensure +91 format
        $phone = preg_replace('/^\+/', '', $phone);

        Http::post('https://api.msg91.com/api/v5/flow/', [
            'authkey'       => $authKey,
            'template_id'   => config('services.msg91.otp_template_id'),
            'mobile'        => $phone,
            'OTP'           => substr($message, strpos($message, ' is ') + 4, 6),
        ]);
    }

    private function sendViaFast2SMS(string $phone, string $message): void
    {
        $key = config('services.fast2sms.api_key');
        Http::withHeaders(['authorization' => $key])
            ->post('https://www.fast2sms.com/dev/bulkV2', [
                'route'    => 'q',
                'message'  => $message,
                'numbers'  => preg_replace('/^\+91/', '', $phone),
            ]);
    }

    private function redirectForUser(User $user): string
    {
        if ($user->role === 'admin') return route('admin.dashboard');
        if ($user->role === 'mentor') {
            if (! $user->onboarding_completed) return route('mentor.onboarding', ['step' => 1]);
            if ($user->mentor_status === 'pending') return route('mentor.onboarding.pending');
            return route('mentor.dashboard');
        }
        if (! $user->onboarding_completed) return route('mentee.onboarding', ['step' => 1]);
        return route('mentee.dashboard');
    }
}