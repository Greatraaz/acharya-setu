<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\EducationStream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    // Show step view
    public function show(int $step)
    {
        $streams = \App\Models\User::getEducationStreams(); // or load from DB
        try {
            $streams = \DB::table('education_streams')->where('is_active', true)->orderBy('sort_order')->get();
        } catch (\Throwable) {
            $streams = collect();
        }
        return view('onboarding.mentor.steps', compact('step', 'streams'));
    }

    // Step 1: Basic info + avatar
    public function saveStep1(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'gender'  => 'nullable|string',
            'phone'   => 'nullable|string',
            'linkedin'=> 'nullable|url',
            'bio'     => 'required|string|min:50|max:2000',
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = '/storage/' . $path;
        }

        auth()->user()->update(array_merge($data, ['onboarding_step' => 1]));

        return $this->stepResponse($request, 2);
    }

    // Step 2: Professional details
    public function saveStep2(Request $request)
    {
        $data = $request->validate([
            'designation'    => 'required|string|max:100',
            'company'        => 'required|string|max:100',
            'experience_years' => 'required|integer|min:0|max:50',
            'rate_per_minute'  => 'required|numeric|min:1|max:1000',
            'field'          => 'required|string',
            'education_stream' => 'nullable|string',
        ]);

        auth()->user()->update(array_merge($data, ['onboarding_step' => 2]));

        return $this->stepResponse($request, 3);
    }

    // Step 3: Expertise chips
    public function saveStep3(Request $request)
    {
        $request->validate(['expertise' => 'required|array|min:1', 'expertise.*' => 'string|max:60']);
        auth()->user()->update(['expertise' => $request->expertise, 'onboarding_step' => 3]);
        return $this->stepResponse($request, 4);
    }

    // Step 4: Preferences
    public function saveStep4(Request $request)
    {
        $data = $request->validate([
            'preferences' => 'nullable|array',
            'strengths'   => 'nullable|array',
        ]);
        auth()->user()->update(array_merge($data, ['onboarding_step' => 4]));
        return $this->stepResponse($request, 5);
    }

    // Step 5: Submit for approval
    public function submit(Request $request)
    {
        $user = auth()->user();

        // Basic completeness check
        if (empty($user->bio) || empty($user->designation) || empty($user->expertise)) {
            $msg = 'Please complete all required sections before submitting.';
            if ($request->ajax()) return response()->json(['message' => $msg], 422);
            return back()->with('error', $msg);
        }

        $user->update([
            'mentor_status'        => 'pending',
            'onboarding_completed' => true,
            'onboarding_step'      => 5,
        ]);

        // TODO: notify admin of new mentor application

        if ($request->ajax()) {
            return response()->json(['message' => 'Profile submitted for review!', 'redirect' => route('mentor.onboarding.pending')]);
        }
        return redirect()->route('mentor.onboarding.pending');
    }

    private function stepResponse(Request $request, int $nextStep)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message'  => 'Saved!',
                'redirect' => route('mentor.onboarding', ['step' => $nextStep]),
            ]);
        }
        return redirect()->route('mentor.onboarding', ['step' => $nextStep]);
    }
}