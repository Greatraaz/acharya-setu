<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentee/onboarding/meta
    // ─────────────────────────────────────────────
    public function meta(Request $request): JsonResponse
    {
        $user = $request->user();

        $dbStreams = collect();
        try {
            $dbStreams = DB::table('education_streams')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'icon', 'sort_order']);
        } catch (\Throwable) {}

        return response()->json([
            'status'               => true,
            'statuscode'           => 200,
            'streams'              => $dbStreams,
            'current_step'         => $user->onboarding_step ?? 0,
            'total_steps'          => 4,
            'onboarding_completed' => (bool) $user->onboarding_completed,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/step/1
    // ─────────────────────────────────────────────
    public function saveStep1(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'gender'   => 'nullable|in:male,female,other',
            'phone'    => 'nullable|string|regex:/^[6-9]\d{9}$/',
            'address'  => 'nullable|string|max:200',
            'avatar'   => 'nullable|file|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = '/storage/' . $path;
        }

        $data['location'] = $data['address'];
        unset($data['address']);
        unset($data['avatar']);
        $user->update(array_merge($data, ['onboarding_step' => 1]));
        $freshUser = $user->fresh();
        $fullAvatarUrl = $freshUser->avatar_url;
        if ($fullAvatarUrl && !str_starts_with($fullAvatarUrl, 'http://') && !str_starts_with($fullAvatarUrl, 'https://')) {
            $fullAvatarUrl = url($fullAvatarUrl);
        }
        $freshUser->avatar_url = $fullAvatarUrl;

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Profile saved!',
            'next_step'  => 2,
            'avatar_url' => $fullAvatarUrl,
            'user'       => $freshUser,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/step/2
    // ─────────────────────────────────────────────
    public function saveStep2(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'education_stream' => $request->input('education_stream'),
            'field'            => $request->input('field'),
            'college'          => $request->input('college'),
            'year'             => $request->input('year'),
            'onboarding_step'  => 2,
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Education details saved!',
            'next_step'  => 3,
            'user'       => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/step/3
    // ─────────────────────────────────────────────
    public function saveStep3(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'career_goals'   => 'required|array|min:1',
            'career_goals.*' => 'string',
        ]);

        $user->update([
            'career_goals'    => $data['career_goals'],
            'onboarding_step' => 3,
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Career goals saved!',
            'next_step'  => 4,
            'user'       => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/step/4 — Set Your Preferences
    // ─────────────────────────────────────────────
    public function saveStep4(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'weekly_time_commitment' => 'required|string',
            'monthly_budget'         => 'nullable|string',
            'preferred_language'     => 'required|string',
            'mentoring_format'       => 'required|string',
        ]);

        $preferences = array_merge($user->preferences ?? [], [
            'weekly_time_commitment' => $data['weekly_time_commitment'],
            'monthly_budget'         => $data['monthly_budget'] ?? null,
            'preferred_language'     => $data['preferred_language'],
            'mentoring_format'       => $data['mentoring_format'],
        ]);

        $user->update([
            'preferences'     => $preferences,
            'onboarding_step' => 4,
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Preferences saved!',
            'next_step'  => 5,
            'user'       => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/complete
    // ─────────────────────────────────────────────
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        $missing = [];
        if (empty($user->name))              $missing[] = 'name';
        if (empty($user->location))          $missing[] = 'address';
        if (empty($user->education_stream))  $missing[] = 'education_stream';
        if (empty($user->preferences['weekly_time_commitment'] ?? null)) $missing[] = 'weekly_time_commitment';
        if (empty($user->preferences['preferred_language'] ?? null))     $missing[] = 'preferred_language';
        if (empty($user->preferences['mentoring_format'] ?? null))       $missing[] = 'mentoring_format';
        if (empty($user->career_goals))      $missing[] = 'career_goals';

        if (!empty($missing)) {
            return response()->json([
                'status'         => false,
                'statuscode'     => 422,
                'message'        => 'Please complete all onboarding steps before finishing.',
                'missing_fields' => $missing,
            ], 422);
        }

        $user->update([
            'onboarding_completed' => true,
            'onboarding_step'      => 4,
        ]);

        return response()->json([
            'status'               => true,
            'statuscode'           => 200,
            'message'              => 'Onboarding complete!',
            'onboarding_completed' => true,
            'user'                 => $user->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentee/onboarding/status
    // ─────────────────────────────────────────────
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentStep = $user->onboarding_step ?? 0;
        $preferences = $user->preferences ?? [];

        return response()->json([
            'status'               => true,
            'statuscode'           => 200,
            'onboarding_step'      => $currentStep,
            'onboarding_completed' => (bool) $user->onboarding_completed,
            'total_steps'          => 4,
            'steps' => [
                'step1' => [
                    'completed' => $currentStep >= 1,
                    'data'      => [
                        'name'       => $user->name,
                        'gender'     => $user->gender,
                        'phone'      => $user->phone,
                        'address'    => $user->location,
                        'avatar_url' => $user->avatar_url,
                    ],
                ],
                'step2' => [
                    'completed' => $currentStep >= 2,
                    'data'      => [
                        'education_stream' => $user->education_stream,
                        'field'            => $user->field,
                        'college'          => $user->college,
                        'year'             => $user->year,
                    ],
                ],
                'step3' => [
                    'completed' => $currentStep >= 3,
                    'data'      => [
                        'career_goals' => $user->career_goals ?? [],
                    ],
                ],
                'step4' => [
                    'completed' => $currentStep >= 4,
                    'data'      => [
                        'weekly_time_commitment' => $preferences['weekly_time_commitment'] ?? null,
                        'monthly_budget'         => $preferences['monthly_budget'] ?? null,
                        'preferred_language'     => $preferences['preferred_language'] ?? null,
                        'mentoring_format'       => $preferences['mentoring_format'] ?? null,
                    ],
                ],
            ],
        ]);
    }
}
