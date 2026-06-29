<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentee/onboarding/meta
    // ─────────────────────────────────────────────
  
    public function meta(Request $request): JsonResponse
    {
        $streams = collect();
        try {
            $streams = DB::table('education_streams')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'name', 'sort_order']);
        } catch (\Throwable) {}

        return response()->json([
            'streams'               => $streams,
            'current_step'          => $request->user()->onboarding_step ?? 0,
            'onboarding_completed'  => (bool) $request->user()->onboarding_completed,
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/step/1
    // ─────────────────────────────────────────────
    
    public function saveStep1(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'gender'    => 'nullable|in:male,female,other',
            'phone'     => 'nullable|string|max:20',
            'avatar'    => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'education_stream'    => 'nullable|string',
            'field' => 'nullable|string',
            'college'   => 'nullable|string|max:200',
            'address'   => 'nullable|string|max:200',
            'user_type' => 'nullable|string',
        ]);

        $request->user()->update(array_merge($data, ['onboarding_step' => 1]));

        return response()->json([
            'message'   => 'Saved!',
            'next_step' => 2,
            'user'      => $request->user()->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/step/2
    // ─────────────────────────────────────────────
   
    public function saveStep2(Request $request): JsonResponse
    {
        $data = $request->validate([
            'education_stream' => 'nullable|string',
            'college'          => 'nullable|string|max:200',
            'year'             => 'nullable|string|max:50',
        ]);

        $request->user()->update(array_merge($data, ['onboarding_step' => 2]));

        return response()->json([
            'message'   => 'Saved!',
            'next_step' => 3,
            'user'      => $request->user()->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/step/3
    // ─────────────────────────────────────────────
    
    public function saveStep3(Request $request): JsonResponse
    {
        $request->validate(['career_goals' => 'nullable|array', 'career_goals.*' => 'string']);

        $request->user()->update([
            'career_goals'   => $request->career_goals ?? [],
            'onboarding_step' => 3,
        ]);

        return response()->json([
            'message'   => 'Saved!',
            'next_step' => 4,
            'user'      => $request->user()->fresh(),
        ]);
    }

    public function saveStep4(Request $request): JsonResponse
    {
        $request->validate(['career_goals' => 'nullable|array', 'career_goals.*' => 'string']);

        $request->user()->update([
            'career_goals'   => $request->career_goals ?? [],
            'onboarding_step' => 3,
        ]);

        return response()->json([
            'message'   => 'Saved!',
            'next_step' => 4,
            'user'      => $request->user()->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  POST /mentee/onboarding/complete
    // ─────────────────────────────────────────────
  
    public function complete(Request $request): JsonResponse
    {
        $request->user()->update([
            'onboarding_completed' => true,
            'onboarding_step'      => 4,
        ]);

        return response()->json([
            'message'              => 'Onboarding complete!',
            'onboarding_completed' => true,
            'user'                 => $request->user()->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentee/onboarding/status
    // ─────────────────────────────────────────────
    
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'onboarding_step'      => $user->onboarding_step ?? 0,
            'onboarding_completed' => (bool) $user->onboarding_completed,
            'steps' => [
                'step1' => [
                    'completed' => ($user->onboarding_step ?? 0) >= 1,
                    'data'      => [
                        'name'      => $user->name,
                        'gender'    => $user->gender,
                        'phone'     => $user->phone,
                        'user_type' => $user->user_type ?? null,
                    ],
                ],
                'step2' => [
                    'completed' => ($user->onboarding_step ?? 0) >= 2,
                    'data'      => [
                        'education_stream' => $user->education_stream,
                        'college'          => $user->college,
                        'year'             => $user->year,
                    ],
                ],
                'step3' => [
                    'completed' => ($user->onboarding_step ?? 0) >= 3,
                    'data'      => [
                        'career_goals' => $user->career_goals ?? [],
                    ],
                ],
            ],
        ]);
    }
}
