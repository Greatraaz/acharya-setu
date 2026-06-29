<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class MenteeOnboardingController extends Controller
{
    /**
     * Steps:
     * 1 → Personal      (name, phone, gender, avatar, college, year)
     * 2 → Learning Goals (education_stream, career_goals, field)
     * 3 → Preferences   (strengths, preferences)
     * 4 → Done (immediate access)
     */
 
    public function show(int $step = 1)
    {
        /** @var User $user */
        $user = auth()->user();
 
        if ($user->onboarding_completed) {
            return redirect()->route('mentee.dashboard');
        }
 
        if ($step > $user->onboarding_step + 1) {
            return redirect()->route('mentee.onboarding', ['step' => $user->onboarding_step + 1]);
        }
 
        return view("onboarding.mentee.step-{$step}", compact('user', 'step'));
    }
 
    public function saveStep1(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'phone'  => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'college'=> 'nullable|string|max:200',
            'year'   => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);
 
        /** @var User $user */
        $user = auth()->user();
 
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');
            $data['avatar_url'] = Storage::url($path);
        }
 
        $user->update(array_merge($data, ['onboarding_step' => max($user->onboarding_step, 1)]));
        return redirect()->route('mentee.onboarding', ['step' => 2]);
    }
 
    public function saveStep2(Request $request)
    {
        $data = $request->validate([
            'education_stream' => 'required|string|max:100',
            'field'            => 'required|string|max:100',
            'career_goals'     => 'required|array|min:1',
            'career_goals.*'   => 'string|max:200',
        ]);
 
        /** @var User $user */
        $user = auth()->user();
        $user->update(array_merge($data, ['onboarding_step' => max($user->onboarding_step, 2)]));
        return redirect()->route('mentee.onboarding', ['step' => 3]);
    }
 
    public function saveStep3(Request $request)
    {
        $data = $request->validate([
            'strengths'   => 'nullable|array',
            'strengths.*' => 'string|max:100',
            'preferences' => 'nullable|array',
        ]);
 
        /** @var User $user */
        $user = auth()->user();
        $user->update(array_merge($data, [
            'onboarding_step'     => max($user->onboarding_step, 3),
        ]));
        return redirect()->route('mentee.onboarding', ['step' => 4]);
    }
 
    public function complete()
    {
        /** @var User $user */
        $user = auth()->user();
        $user->update([
            'onboarding_step'      => 4,
            'onboarding_completed' => true,
            'is_active'            => true,
        ]);
        return redirect()->route('mentee.dashboard')->with('success', 'Welcome! Your profile is ready.');
    }
}