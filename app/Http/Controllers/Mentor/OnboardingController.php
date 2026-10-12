<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationSession;
use App\Models\EducationStream;
use App\Support\IndianPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    // Show step view
    public function show(int $step)
    {
        if ($done = $this->finishedRedirect()) {
            return $done;
        }

        $streams = collect();
        try {
            $streams = \DB::table('education_streams')->where('is_active', true)->whereNull('mentee_id')->orderBy('sort_order')->get();
        } catch (\Throwable) {
            $streams = collect();
        }
        return view('frontend.mentors.steps', compact('step', 'streams'));
    }

    // Step 1: Basic info + avatar
    public function saveStep1(Request $request)
    {
        if ($done = $this->finishedRedirect($request)) {
            return $done;
        }

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'gender'   => 'nullable|in:male,female,other',
            'phone'    => IndianPhone::rules(),
            'linkedin' => 'nullable|url',
            'bio'      => 'required|string|min:50|max:2000',
            'avatar'   => 'nullable|file|image|mimes:jpeg,png,webp|max:2048',
        ]);

        if (array_key_exists('phone', $data)) {
            $data['phone'] = IndianPhone::normalize($data['phone']);
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar_url'] = '/storage/' . $path;
        }
        unset($data['avatar']);

        auth()->user()->update(array_merge($data, ['onboarding_step' => 1]));

        return $this->stepResponse($request, 2);
    }

    // Step 2: Professional details
    public function saveStep2(Request $request)
    {
        if ($done = $this->finishedRedirect($request)) {
            return $done;
        }

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
        if ($done = $this->finishedRedirect($request)) {
            return $done;
        }

        $request->validate(['expertise' => 'required|array|min:1', 'expertise.*' => 'string|max:60']);
        auth()->user()->update(['expertise' => $request->expertise, 'onboarding_step' => 3]);
        return $this->stepResponse($request, 4);
    }

    // Step 4: Preferences
    public function saveStep4(Request $request)
    {
        if ($done = $this->finishedRedirect($request)) {
            return $done;
        }

        $data = $request->validate([
            'preferences'                  => 'nullable|array',
            'preferences.preferred_time'   => 'nullable|string',
            'preferences.session_length'   => 'nullable|integer|in:'.implode(',', ConsultationSession::BOOKING_DURATIONS),
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

        auth()->user()->update($update);
        return $this->stepResponse($request, 5);
    }

    // Step 5: Submit for approval
    public function submit(Request $request)
    {
        $user = auth()->user();

        if ($user->onboarding_completed || $user->mentor_status === 'approved') {
            return $this->finishedRedirect($request) ?? redirect()->route('mentor.onboarding.pending');
        }

        if (empty($user->bio) || empty($user->designation) || empty($user->expertise) || (float) $user->rate_per_minute < 1) {
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

    private function finishedRedirect(?Request $request = null)
    {
        $user = auth()->user();
        if (! $user->onboarding_completed && $user->mentor_status !== 'approved') {
            return null;
        }

        $redirect = $user->mentor_status === 'approved'
            ? route('mentor.dashboard')
            : route('mentor.onboarding.pending');

        if ($request && ($request->ajax() || $request->wantsJson())) {
            return response()->json([
                'message'       => $user->mentor_status === 'approved'
                    ? 'Your mentor profile is already approved.'
                    : 'Your profile is already submitted for review.',
                'redirect'      => $redirect,
                'mentor_status' => $user->mentor_status,
            ], 422);
        }

        return redirect($redirect);
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