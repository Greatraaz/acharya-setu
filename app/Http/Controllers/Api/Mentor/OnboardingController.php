<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentor/onboarding/meta
    // ─────────────────────────────────────────────
    public function meta(Request $request): JsonResponse
    {
        $statusCode = 200;
        $status = true;

        $streams = collect();
        try {
            $streams = DB::table('education_streams')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'sort_order']);
        } catch (\Throwable $e) {
            $status = false;
            $statusCode = 500;
        }

        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'     => false,
                'statuscode' => 401,
                'message'    => 'User record not found.',
            ], 401);
        }

        return response()->json([
            'status'                => $status,
            'statuscode'            => $statusCode,
            'streams'               => $streams,
            'current_step'          => $user->onboarding_step ?? 0,
            'onboarding_completed'  => (bool) $user->onboarding_completed,
            'mentor_status'         => $user->mentor_status,
            'total_steps'           => 5,
        ], $statusCode);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/onboarding/step/1
    // ─────────────────────────────────────────────
    public function saveStep1(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'     => false,
                'statuscode' => 401,
                'message'    => 'User record not found.',
            ], 401);
        }

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'gender'   => 'nullable|in:male,female,other',
            'phone'    => 'nullable|string|max:20',
            'linkedin' => 'nullable|url',
            'bio'      => 'required|string|min:50|max:2000',
            'avatar'   => 'nullable|file|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = '/storage/' . $path;
        }

        $user->update(array_merge($data, ['onboarding_step' => 1]));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Saved!',
            'next_step'  => 2,
            'avatar_url' => $user->fresh()->avatar_url,
            'user'       => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/onboarding/step/2
    // ─────────────────────────────────────────────
    public function saveStep2(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'     => false,
                'statuscode' => 401,
                'message'    => 'User record not found.',
            ], 401);
        }

        $data = $request->validate([
            'designation'      => 'required|string|max:100',
            'company'          => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0|max:50',
            'rate_per_minute'  => 'required|numeric|min:1|max:1000',
            'field'            => 'required|string',
            'education_stream' => 'nullable|string',
        ]);

        $user->update(array_merge($data, ['onboarding_step' => 2]));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Saved!',
            'next_step'  => 3,
            'user'       => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/onboarding/step/3
    // ─────────────────────────────────────────────
    public function saveStep3(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'     => false,
                'statuscode' => 401,
                'message'    => 'User record not found.',
            ], 401);
        }

        $request->validate([
            'expertise'   => 'required|array|min:1',
            'expertise.*' => 'string|max:60',
        ]);

        $user->update([
            'expertise'       => $request->expertise,
            'onboarding_step' => 3,
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Saved!',
            'next_step'  => 4,
            'user'       => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/onboarding/step/4
    // ─────────────────────────────────────────────
    public function saveStep4(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'     => false,
                'statuscode' => 401,
                'message'    => 'User record not found.',
            ], 401);
        }

        $data = $request->validate([
            'preferences'                  => 'nullable|array',
            'preferences.preferred_time' => 'nullable|string',
            'preferences.session_length' => 'nullable|integer',
            'strengths'                    => 'nullable|array',
            'strengths.*'                  => 'string',
        ]);

        $update = ['onboarding_step' => 4];

        if (array_key_exists('preferences', $data)) {
            $update['preferences'] = $data['preferences'];
        }

        if (array_key_exists('strengths', $data)) {
            $update['strengths'] = $data['strengths'];
        }

        $user->update($update);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Saved!',
            'next_step'  => 5,
            'user'       => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentor/onboarding/submit
    // ─────────────────────────────────────────────
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'     => false,
                'statuscode' => 401,
                'message'    => 'User record not found.',
            ], 401);
        }

        $missingFields = [];

        if (empty($user->bio))         $missingFields[] = 'bio';
        if (empty($user->designation)) $missingFields[] = 'designation';
        if (empty($user->expertise))   $missingFields[] = 'expertise';

        if (!empty($missingFields)) {
            return response()->json([
                'status'         => false,
                'statuscode'     => 422,
                'message'        => 'Please complete all required sections before submitting.',
                'missing_fields' => $missingFields,
            ], 422);
        }

        $user->update([
            'mentor_status'        => 'pending',
            'onboarding_completed' => true,
            'onboarding_step'      => 5,
        ]);

        // TODO: notify admin of new mentor application

        return response()->json([
            'status'              => true,
            'statuscode'          => 200,
            'message'             => 'Profile submitted for review!',
            'mentor_status'       => 'pending',
            'onboarding_completed'=> true,
            'user'                => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentor/onboarding/status
    // ─────────────────────────────────────────────
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status'     => false,
                'statuscode' => 401,
                'message'    => 'User record not found.',
            ], 401);
        }
        $currentStep = $user->onboarding_step ?? 0;

        return response()->json([
            'status'                => true,
            'statuscode'            => 200,
            'onboarding_step'       => $currentStep,
            'onboarding_completed'  => (bool) $user->onboarding_completed,
            'mentor_status'         => $user->mentor_status,
            'steps' => [
                'step1' => [
                    'completed' => $currentStep >= 1,
                    'data'      => [
                        'name'       => $user->name,
                        'gender'     => $user->gender,
                        'phone'      => $user->phone,
                        'linkedin'   => $user->linkedin,
                        'bio'        => $user->bio,
                        'avatar_url' => $user->avatar_url,
                    ],
                ],
                'step2' => [
                    'completed' => $currentStep >= 2,
                    'data'      => [
                        'designation'      => $user->designation,
                        'company'          => $user->company,
                        'experience_years' => $user->experience_years,
                        'rate_per_minute'  => $user->rate_per_minute ?? null,
                        'field'            => $user->field,
                        'education_stream' => $user->education_stream,
                    ],
                ],
                'step3' => [
                    'completed' => $currentStep >= 3,
                    'data'      => [
                        'expertise' => $user->expertise ?? [],
                    ],
                ],
                'step4' => [
                    'completed' => $currentStep >= 4,
                    'data'      => [
                        'preferences' => $user->preferencesForResponse(),
                        'strengths'   => $user->strengths ?? [],
                    ],
                ],
                'step5' => [
                    'completed' => $currentStep >= 5,
                    'data'      => [
                        'mentor_status'        => $user->mentor_status,
                        'onboarding_completed' => (bool) $user->onboarding_completed,
                    ],
                ],
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  DELETE /mentor/account
    // ─────────────────────────────────────────────
    public function destroyAccount(Request $request): JsonResponse
    {
        $request->user()->deleteAccount();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Account deleted successfully.',
        ]);
    }
}
