<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Models\OtpCode;
use App\Models\WalletTransaction;
use App\Mail\OtpMail;
use App\Services\SmsService;
use App\Support\IndianPhone;
use App\Support\SensitiveLog;
use App\Support\LoginThrottle;
use App\Support\PasswordReset as PasswordResetGuard;
use App\Support\ProfileUpdate;
use App\Support\RegistrationData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    private const USER_FIELDS = [
        'id', 'name', 'email', 'role', 'bio', 'expertise',
        'field', 'college', 'year', 'company', 'designation',
        'experience_years', 'rating', 'total_sessions', 'avatar_url',
        'gender', 'phone', 'linkedin', 'onboarding_completed',
        'onboarding_step', 'mentor_status', 'subscription_plan',
        'education_stream', 'career_goals', 'strengths',
        'preferences', 'is_active', 'isVerifiedEmail', 'created_at',
        'has_pending_changes',
    ];

    public function register(Request $request): JsonResponse
    {
        RegistrationData::normalize($request);

        $validator = Validator::make($request->all(), [
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email:filter', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'phone'            => IndianPhone::rules(required: true),
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role'             => ['nullable', 'in:mentor,mentee'],
            'college'          => ['nullable', 'string', 'max:255'],
            'year'             => ['nullable', 'string', 'max:20'],
            'field'            => ['nullable', 'string', 'max:255'],
            'company'          => ['nullable', 'string', 'max:255'],
            'designation'      => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer'],
            'gender'           => ['nullable', 'in:male,female,other'],
            'referral_code'    => ['nullable', 'string', 'max:50'],
            'accepted_terms'   => ['required', 'accepted'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (RegistrationData::emailTaken($validated['email'])) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => ['email' => ['This email is already registered.']],
            ], 422);
        }

        if (RegistrationData::phoneTaken($validated['phone'])) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => ['phone' => ['This mobile number is already registered.']],
            ], 422);
        }

        $email = strtolower($validated['email']);

        // Legacy soft-deleted accounts may still hold the email — release it so re-registration works.
        User::onlyTrashed()
            ->where('email', $email)
            ->each(fn (User $user) => $user->releaseCredentialsForDeletion());

        try {
            $user = User::create([
                'name'              => $validated['name'],
                'email'             => $email,
                'phone'             => IndianPhone::normalize($validated['phone']),
                'password'          => Hash::make($validated['password']),
                'role'              => $validated['role'] ?? 'mentee',
                'college'           => $validated['college'] ?? null,
                'year'              => $validated['year'] ?? null,
                'field'             => $validated['field'] ?? null,
                'company'           => $validated['company'] ?? null,
                'designation'       => $validated['designation'] ?? null,
                'experience_years'  => $validated['experience_years'] ?? 0,
                'gender'            => $validated['gender'] ?? null,
                'referral_code'     => $validated['referral_code'] ?? null,
                'terms_accepted_at' => now(),
                'terms_version'     => RegistrationData::TERMS_VERSION,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) !== 1062) {
                throw $e;
            }

            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => ['email' => ['This email is already registered.']],
            ], 422);
        }

        if ($user->role === 'mentee') {
            WalletTransaction::create([
                'user_id'        => $user->id,
                'amount'         => 0,
                'balance_before' => 0,
                'balance_after'  => 0,
                'type'           => 'credit',
                'description'    => 'Welcome bonus',
                'status'         => 'completed',
            ]);
        }

        if (!empty($validated['referral_code'])) {
            $referral = Referral::where('code', $validated['referral_code'])->whereNull('referred_id')->first();
            if ($referral) {
                Referral::create([
                    'referrer_id'  => $referral->referrer_id,
                    'referred_id'  => $user->id,
                    'code'         => $validated['referral_code'],
                    'bonus_amount' => 250,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'status'  => true,
            'token'   => $user->createToken('api', ['*'], now()->addWeek())->plainTextToken,
            'user'    => $user->only(self::USER_FIELDS),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->merge([
            'email' => strtolower(trim((string) $request->input('email', ''))),
        ]);

        $validator = Validator::make($request->all(), [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $email = $validated['email'];
        $ip = $request->ip() ?: 'local';

        if ($wait = LoginThrottle::availableIn($email, $ip)) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => LoginThrottle::lockedMessage($wait),
            ], 429);
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            LoginThrottle::hit($email, $ip);

            return response()->json([
                'success' => false,
                'status'  => false,
                'error'   => 'Invalid email or password.',
            ], 401);
        }

        if (LoginThrottle::isDisabled($user)) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => 'This account has been disabled.',
            ], 403);
        }

        LoginThrottle::clear($email, $ip);

        return response()->json([
            'success' => true,
            'status'  => true,
            'token'   => $user->createToken('api', ['*'], now()->addWeek())->plainTextToken,
            'user'    => $user->only(self::USER_FIELDS),
        ], 200);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => true,
            'user'    => $request->user()->only(self::USER_FIELDS),
        ], 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Logged out successfully.'
        ], 200);
    }

    public function sessions(Request $request): JsonResponse
    {
        $currentId = $request->user()->currentAccessToken()?->id;

        $sessions = $request->user()->tokens()->latest('id')->get()->map(fn ($token) => [
            'id'           => $token->id,
            'name'         => $token->name,
            'current'      => $token->id === $currentId,
            'last_used_at' => $token->last_used_at,
            'created_at'   => $token->created_at,
            'expires_at'   => $token->expires_at,
        ]);

        return response()->json([
            'success'  => true,
            'status'   => true,
            'sessions' => $sessions,
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Signed out of all devices.',
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $rules = [
            'name' => ['sometimes', 'string', 'max:100'],
            'phone' => IndianPhone::rules(),
            'gender' => ['nullable', 'in:male,female,other'],
            'preferences' => ['nullable', 'array'],
        ];

        if ($user->role === 'mentee') {
            $rules = array_merge($rules, [
                'education_stream' => ['nullable', 'string', 'max:100'],
                'field' => ['nullable', 'string', 'max:100'],
                'college' => ['nullable', 'string', 'max:200'],
                'year' => ['nullable', 'string', 'max:50'],
                'career_goals' => ['nullable', 'array', 'min:1'],
                'career_goals.*' => ['string', 'max:100'],
                'preferences.preferred_language' => ['nullable', 'string', 'max:100'],
                'preferences.mentoring_format' => ['nullable', 'string', 'max:50'],
                'preferences.session_modes' => ['nullable', 'array'],
                'preferences.session_modes.*' => ['in:video,audio,chat,in_person'],
                'preferences.weekly_time_commitment' => ['nullable', 'string', 'max:100'],
                'preferences.monthly_budget' => ['nullable', 'string', 'max:100'],
            ]);
        }

        if ($user->role === 'mentor') {
            $rules = array_merge($rules, [
                'bio' => ['sometimes', 'string', 'min:80', 'max:2000'],
                'field' => ['nullable', 'string', 'max:100'],
                'company' => ['nullable', 'string', 'max:100'],
                'designation' => ['sometimes', 'string', 'max:100'],
                'experience_years' => ['sometimes', 'integer', 'min:0', 'max:50'],
                'rate_per_minute' => ['sometimes', 'numeric', 'min:5', 'max:500'],
                'linkedin' => ['nullable', 'url', 'max:255'],
                'expertise' => ['sometimes', 'array', 'min:1'],
                'expertise.*' => ['string', 'max:60'],
                'strengths' => ['nullable', 'array'],
                'strengths.*' => ['string', 'max:60'],
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = ProfileUpdate::save($user, $validator->validated());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => $result['message'],
            'pending' => $result['pending'],
            'user' => $user->fresh()->only(self::USER_FIELDS),
        ], 200);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'base64'   => ['required', 'string'],
            'mimeType' => ['nullable', 'string', 'in:image/jpeg,image/png,image/webp'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $rawBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('base64'));
        $rawBase64 = str_replace(' ', '+', $rawBase64);
        $imageData = base64_decode($rawBase64, true);

        if ($imageData === false || strlen($imageData) > 2 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'error'   => 'Upload a JPG, PNG, or WebP image up to 2MB.',
            ], 422);
        }

        $info = @getimagesizefromstring($imageData);
        $detected = is_array($info) ? ($info['mime'] ?? '') : '';
        if (! in_array($detected, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'error'   => 'Upload a JPG, PNG, or WebP image up to 2MB.',
            ], 422);
        }

        $extension = match ($detected) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $user = $request->user();
        $url = ProfileUpdate::storeAvatarContents($user, $imageData, $extension);

        return response()->json([
            'success'    => true,
            'status'     => true,
            'avatar_url' => $url,
            'user'       => $user->fresh()->only(self::USER_FIELDS)
        ], 200);
    }

    public function removePhoto(Request $request): JsonResponse
    {
        ProfileUpdate::clearAvatar($request->user());

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Photo removed.',
            'avatar_url' => null,
            'user' => $request->user()->fresh()->only(self::USER_FIELDS),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (!Hash::check($validated['current_password'], $request->user()->password)) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'error'   => 'Current password is incorrect.'
            ], 422);
        }

        $request->user()->update(['password' => Hash::make($validated['new_password'])]);
        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Password changed successfully.'
        ], 200);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->merge([
            'email' => PasswordResetGuard::normalizeEmail((string) $request->input('email', '')),
        ]);

        $validator = Validator::make($request->all(), [
            'email'        => ['required', 'email'],
            'token'        => ['required', 'string'],
            'new_password' => PasswordResetGuard::rules(),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $outcome = PasswordResetGuard::inspect($validated['email'], $validated['token']);
        if ($outcome !== 'ok') {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => PasswordResetGuard::message($outcome),
            ], 422);
        }

        $status = \Illuminate\Support\Facades\Password::reset(
            [
                'email'                 => $validated['email'],
                'token'                 => $validated['token'],
                'password'              => $validated['new_password'],
                'password_confirmation' => $request->input('new_password_confirmation'),
            ],
            function ($user, $password) {
                PasswordResetGuard::apply($user, $password);
                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status !== \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => PasswordResetGuard::message('invalid'),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Password reset successfully.',
        ], 200);
    }

    public function updateOnboarding(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'onboarding_step'      => ['sometimes', 'integer', 'min:0'],
            'onboarding_completed' => ['sometimes', 'boolean'],
            'education_stream'     => ['nullable', 'string'],
            'career_goals'         => ['nullable', 'array'],
            'strengths'            => ['nullable', 'array'],
            'preferences'          => ['nullable', 'array'],
            'field'                => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $request->user()->update($validator->validated());
        return response()->json([
            'success' => true,
            'status'  => true,
            'user'    => $request->user()->fresh()->only(self::USER_FIELDS)
        ], 200);
    }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'],
            'channel'    => ['required', 'in:email,phone'],
            'type'       => ['required', 'in:registration,login,reset'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'statusCode'  => 422,
                'errors'  => $validator->errors()
            ], 422);
        }

        $identifier = trim((string) $request->input('identifier'));
        $channel = $request->input('channel');
        $type = $request->input('type');

        if ($channel === 'email') {
            $identifier = strtolower($identifier);
            if (! filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 422,
                    'errors' => ['identifier' => ['Enter a valid email address.']],
                ], 422);
            }
            if ($type === 'registration' && RegistrationData::emailTaken($identifier)) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 422,
                    'errors' => ['identifier' => ['This email is already registered.']],
                ], 422);
            }
        }

        if ($channel === 'phone') {
            if (! IndianPhone::isValid($identifier)) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 422,
                    'errors' => ['identifier' => ['Enter a valid 10-digit Indian mobile number.']],
                ], 422);
            }
            $identifier = IndianPhone::normalize($identifier);
            if ($type === 'registration' && RegistrationData::phoneTaken($identifier)) {
                return response()->json([
                    'status' => false,
                    'statusCode' => 422,
                    'errors' => ['identifier' => ['This mobile number is already registered.']],
                ], 422);
            }
        }

        if ($type === 'reset' && ! $this->resetUser($identifier, $channel)) {
            return response()->json([
                'status'     => true,
                'statusCode' => 200,
                'message'    => PasswordResetGuard::GENERIC_SENT,
            ], 200);
        }

        $issued = OtpCode::issue($identifier, $channel, $type);
        if (! $issued['ok']) {
            return response()->json([
                'status'      => false,
                'statusCode'  => $issued['status'],
                'message'     => $issued['message'],
                'retry_after' => $issued['retry_after'] ?? null,
            ], $issued['status']);
        }

        $this->deliverOtp($issued['identifier'], $channel, $issued['otp'], $type);

        return response()->json([
            'status'      => true,
            'statusCode'  => 200,
            'message'     => $type === 'reset' ? PasswordResetGuard::GENERIC_SENT : 'OTP sent successfully',
            'expires_in'  => $issued['expires_in'],
            'retry_after' => OtpCode::COOLDOWN_SECONDS,
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'],
            'channel'    => ['required', 'in:email,phone'],
            'otp'        => ['required', 'digits:6'],
            'type'       => ['required', 'in:registration,login,reset'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'statusCode'  => 422,'errors'  => $validator->errors(),], 422);
        }

        $identifier = OtpCode::normalizeIdentifier($request->input('identifier'), $request->input('channel'));
        $channel = $request->input('channel');
        $result = OtpCode::verify($identifier, $channel, $request->input('otp'), $request->input('type'));

        if ($result !== 'ok') {
            return response()->json([
                'status'     => false,
                'statusCode' => 422,
                'message'    => OtpCode::message($result),
            ], 422);
        }

        if ($request->input('type') === 'reset') {
            $user = $this->resetUser($identifier, $channel);
            if (! $user) {
                return response()->json([
                    'status'     => false,
                    'statusCode' => 422,
                    'message'    => 'This reset code is invalid.',
                ], 422);
            }

            return response()->json([
                'status'      => true,
                'statusCode'  => 200,
                'message'     => 'OTP verified successfully',
                'reset_token' => \Illuminate\Support\Facades\Password::broker()->createToken($user),
                'email'       => $user->email,
                'expires_in'  => ((int) config('auth.passwords.users.expire', 60)) * 60,
            ], 200);
        }

        if ($channel === 'email') {
            $user = User::where('email', $identifier)->first();
            if ($user) {
                $user->update(['isVerifiedEmail' => 1]);
            }
        }

        return response()->json(['status' => true,'statusCode'  => 200,'message' => 'OTP verified successfully'], 200);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'],
            'channel'    => ['required', 'in:email,phone'],
            'type'       => ['nullable', 'in:registration,login,reset'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $channel = $request->input('channel');
        $identifier = OtpCode::normalizeIdentifier($request->input('identifier'), $channel);
        $type = $request->input('type')
            ?: OtpCode::where('identifier', $identifier)->where('channel', $channel)->value('purpose')
            ?: 'registration';

        $issued = OtpCode::issue($identifier, $channel, $type);
        if (! $issued['ok']) {
            return response()->json([
                'success'     => false,
                'status'      => false,
                'statusCode'  => $issued['status'],
                'message'     => $issued['message'],
                'retry_after' => $issued['retry_after'] ?? null,
            ], $issued['status']);
        }

        $this->deliverOtp($issued['identifier'], $channel, $issued['otp'], $type);

        return response()->json([
            'success'     => true,
            'status'      => true,
            'message'     => 'OTP resent successfully',
            'expires_in'  => $issued['expires_in'],
            'retry_after' => OtpCode::COOLDOWN_SECONDS,
        ], 200);
    }

    private function resetUser(string $identifier, string $channel): ?User
    {
        $user = $channel === 'phone'
            ? User::findByPhone($identifier)
            : User::where('email', PasswordResetGuard::normalizeEmail($identifier))->first();

        if (! $user || LoginThrottle::isDisabled($user)) {
            return null;
        }

        return $user;
    }

    private function deliverOtp(string $identifier, string $channel, string $otp, string $type): void
    {
        try {
            if ($channel === 'email') {
                Mail::to($identifier)->send(new OtpMail($otp, $type));
            } elseif ($channel === 'phone') {
                SmsService::send($identifier, "Your OTP is {$otp}. Valid for 10 minutes.");
            }
        } catch (\Throwable $e) {
            Log::error('OTP delivery failed.', [
                'channel' => $channel,
                'error'   => SensitiveLog::redact($e->getMessage()),
            ]);
        }
    }
}