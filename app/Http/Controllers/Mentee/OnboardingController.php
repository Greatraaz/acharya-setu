<?php

namespace App\Http\Controllers\Mentee;

use App\Http\Controllers\Controller;
use App\Services\MentorMatcherService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function show(int $step)
    {
        $streams = collect();
        try {
            $streams = \DB::table('education_streams')->where('is_active', true)->orderBy('sort_order')->get();
        } catch (\Throwable) {}

        return view('frontend.mentee.steps', compact('step', 'streams'));
    }

    public function saveStep1(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'gender'    => 'nullable|string',
            'phone'     => 'nullable|string',
            'user_type' => 'nullable|string',
        ]);
        auth()->user()->update(array_merge($data, ['onboarding_step' => 1]));
        return $this->next($request, 2);
    }

    public function saveStep2(Request $request)
    {
        $data = $request->validate([
            'education_stream' => 'nullable|string',
            'college'          => 'nullable|string|max:200',
            'year'             => 'nullable|string|max:50',
        ]);
        auth()->user()->update(array_merge($data, ['onboarding_step' => 2]));
        return $this->next($request, 3);
    }

    public function saveStep3(Request $request)
    {
        $request->validate(['career_goals' => 'nullable|array']);
        auth()->user()->update(['career_goals' => $request->career_goals ?? [], 'onboarding_step' => 3]);
        return $this->next($request, 4);
    }

    public function complete(Request $request)
    {
        $user = auth()->user();
        $user->update(['onboarding_completed' => true, 'onboarding_step' => 4]);
        $user->refresh();
        app(MentorMatcherService::class)->assignBestMentor($user);

        $redirect = route('mentee.dashboard');
        if ($request->ajax()) return response()->json(['redirect' => $redirect]);
        return redirect($redirect);
    }

    private function next(Request $request, int $step)
    {
        $redirect = route('mentee.onboarding', ['step' => $step]);
        if ($request->ajax()) return response()->json(['message' => 'Saved!', 'redirect' => $redirect]);
        return redirect($redirect);
    }
}