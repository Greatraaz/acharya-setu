<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Models\OtpCode;
use App\Models\WalletTransaction;
use App\Mail\OtpMail;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
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
    ];

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'password'         => ['required', 'confirmed', Password::min(6)],
            'role'             => ['nullable', 'in:mentor,mentee'],
            'college'          => ['nullable', 'string'],
            'year'             => ['nullable', 'string'],
            'field'            => ['nullable', 'string'],
            'company'          => ['nullable', 'string'],
            'designation'      => ['nullable', 'string'],
            'experience_years' => ['nullable', 'integer'],
            'gender'           => ['nullable', 'in:male,female,other'],
            'referral_code'    => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name'             => $validated['name'],
            'email'            => strtolower($validated['email']),
            'password'         => Hash::make($validated['password']),
            'role'             => $validated['role'] ?? 'mentee',
            'college'          => $validated['college'] ?? null,
            'year'             => $validated['year'] ?? null,
            'field'            => $validated['field'] ?? null,
            'company'          => $validated['company'] ?? null,
            'designation'      => $validated['designation'] ?? null,
            'experience_years' => $validated['experience_years'] ?? 0,
            'gender'           => $validated['gender'] ?? null,
            'referral_code'    => $validated['referral_code'] ?? null,
        ]);

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
            'token'   => $user->createToken('api')->plainTextToken,
            'user'    => $user->only(self::USER_FIELDS),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
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
        $user = User::where('email', strtolower($validated['email']))->where('is_active', true)->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'error'   => 'Invalid email or password.'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'status'  => true,
            'token'   => $user->createToken('api')->plainTextToken,
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

    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'             => ['sometimes', 'string', 'max:100'],
            'bio'              => ['nullable', 'string'],
            'field'            => ['nullable', 'string'],
            'college'          => ['nullable', 'string'],
            'year'             => ['nullable', 'string'],
            'company'          => ['nullable', 'string'],
            'designation'      => ['nullable', 'string'],
            'experience_years' => ['nullable', 'integer'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'linkedin'         => ['nullable', 'url'],
            'gender'           => ['nullable', 'in:male,female,other'],
            'education_stream' => ['nullable', 'string'],
            'career_goals'     => ['nullable', 'array'],
            'strengths'        => ['nullable', 'array'],
            'preferences'      => ['nullable', 'array'],
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
            'user' => $request->user()->fresh()->only(self::USER_FIELDS)
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

        $mime = $request->input('mimeType', 'image/jpeg');
        $rawBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('base64'));
        $rawBase64 = str_replace(' ', '+', $rawBase64);

        $imageData = base64_decode($rawBase64);

        if ($imageData === false) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'error'   => 'Invalid base64 image data'
            ], 422);
        }

        $extension = match ($mime) {
            'image/png'  => 'png',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $user = $request->user();

        $fileName = 'avatar_' . $user->id . '_' . time() . '.' . $extension;

        // public/upload/avatar folder
        $uploadPath = public_path('upload/avatar');

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        file_put_contents($uploadPath . '/' . $fileName, $imageData);

        $url = url('upload/avatar/' . $fileName);

        $user->update([
            'avatar_url' => $url
        ]);

        return response()->json([
            'success'    => true,
            'status'     => true,
            'avatar_url' => $url,
            'user'       => $user->fresh()->only(self::USER_FIELDS)
        ], 200);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password'     => ['required', 'confirmed', Password::min(6)],
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
        $validator = Validator::make($request->all(), [
            'email'        => ['required', 'email'],
            'new_password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::where('email', strtolower($validated['email']))->where('is_active', true)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'message' => 'User not found.',
            ], 404);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'Password reset successfully.'
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

        $identifier = $request->input('identifier');
        $channel = $request->input('channel');

        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        OtpCode::updateOrCreate(
            ['identifier' => $identifier, 'channel' => $channel],
            [
                'code' => bcrypt($otp),
                'expires_at' => $expiresAt,
                'verified_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if ($channel === 'email') {
            Mail::to($identifier)->send(new OtpMail($otp, $request->input('type')));
        } else if ($channel === 'phone') {
            SmsService::send($identifier, "Your OTP is {$otp}. Valid for 10 minutes.");
        }

        return response()->json([
            'status' => true,
            'statusCode'  => 200,
            'message' => 'OTP sent successfully'
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'],
            'channel'    => ['required', 'in:email,phone'],
            'otp'        => ['required', 'digits:6'],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false,'statusCode'  => 422,'errors'  => $validator->errors(),], 422);
        }

        $identifier = $request->input('identifier');
        $channel = $request->input('channel');
        $otp      = $request->input('otp');

        $otpData = OtpCode::where('identifier', $identifier)->where('channel', $channel)->whereNull('verified_at')->first();

        if (!$otpData) {
            return response()->json(['status' => false,'statusCode'  => 404,'message' => 'No OTP found or OTP already used'], 404);
        }

        if (Carbon::parse($otpData->expires_at)->isPast()) {
            return response()->json(['status' => false,'statusCode'  => 422,'message' => 'OTP expired'], 422);
        }

        if (!Hash::check($otp, $otpData->code)) {
            return response()->json(['status' => false,'statusCode'  => 422,'message' => 'Invalid OTP'], 422);
        }

        OtpCode::where('identifier', $identifier)->where('channel', $channel)->update(['verified_at' => now()]);

        if ($channel === 'email') {
            $user = User::where('email', strtolower($identifier))->where('is_active', true)->first();
            $user->update(['isVerifiedEmail' => 1]);
        }

        return response()->json(['status' => true,'statusCode'  => 200,'message' => 'OTP verified successfully'], 200);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'],
            'channel'    => ['required', 'in:email,phone'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $identifier = $request->input('identifier');
        $channel = $request->input('channel');

        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        OtpCode::updateOrCreate(
            ['identifier' => $identifier, 'channel' => $channel],
            [
                'code' => bcrypt($otp),
                'expires_at' => $expiresAt,
                'verified_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        if ($channel === 'email') {
            Mail::to($identifier)->send(new OtpMail($otp));
        } else if ($channel === 'phone') {
            SmsService::send($identifier, "Your OTP is {$otp}. Valid for 10 minutes.");
        }

        return response()->json([
            'success' => true,
            'status'  => true,
            'message' => 'OTP resent successfully'
        ], 200);
    }
}