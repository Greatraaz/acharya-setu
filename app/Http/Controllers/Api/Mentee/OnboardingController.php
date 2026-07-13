<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\EducationStream;
use App\Services\MentorMatcherService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'phone'    => 'nullable|string',
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
            'tracks'   => 'required|array|min:1',
            'tracks.*' => 'string|max:100',
        ]);

        $tracks = collect($data['tracks'])
            ->map(fn ($track) => trim((string) $track))
            ->filter()
            ->unique(fn ($track) => Str::lower($track))
            ->values();

        if ($tracks->isEmpty()) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'Please provide at least one valid track.',
            ], 422);
        }

        $this->syncMenteeTracks($user->id, $tracks->all());

        $user->update(['onboarding_step' => 3]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Tracks saved!',
            'next_step'  => 4,
            'tracks'     => $tracks->all(),
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
        $hasTracks = EducationStream::where('mentee_id', $user->id)
            ->where('is_active', true)
            ->exists();
        if (! $hasTracks) $missing[] = 'tracks';

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

        $user->refresh();
        $assignment = app(MentorMatcherService::class)->assignBestMentor($user);
        $assignedMentor = $assignment['mentor'];

        return response()->json([
            'status'               => true,
            'statuscode'           => 200,
            'message'              => $assignment['assigned']
                ? 'Onboarding complete! A mentor has been assigned to you.'
                : 'Onboarding complete!',
            'onboarding_completed' => true,
            'assigned_mentor'      => $assignedMentor ? $assignedMentor->only([
                'id', 'name', 'field', 'expertise', 'bio', 'avatar_url',
                'rating', 'experience_years', 'company', 'designation',
            ]) : null,
            'mentor_match_score'   => $assignment['match_score'],
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
                        'tracks' => EducationStream::where('mentee_id', $user->id)
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->pluck('name')
                            ->values(),
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

    // ─────────────────────────────────────────────
    //  DELETE /mentee/account
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

    private function syncMenteeTracks(int $menteeId, array $trackNames): void
    {
        $selectedSlugs = collect($trackNames)
            ->map(fn ($name) => Str::slug($name))
            ->filter()
            ->values();

        if ($selectedSlugs->isEmpty()) {
            return;
        }

        // Do not alter mentor-owned tracks.
        EducationStream::where('mentee_id', $menteeId)
            ->whereNull('mentor_id')
            ->whereNotIn('slug', $selectedSlugs)
            ->update(['is_active' => false]);

        foreach ($trackNames as $index => $name) {
            $slug = Str::slug($name);
            if ($slug === '') {
                continue;
            }

            EducationStream::updateOrCreate(
                ['mentee_id' => $menteeId, 'slug' => $slug],
                [
                    'name'       => $name,
                    'mentor_id'  => null,
                    'is_active'  => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
