<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;
 
class MentorOnboardingController extends Controller
{
    /**
     * Steps:
     * 1 → Personal info  (name, phone, gender, avatar)
     * 2 → Professional   (company, designation, experience_years, linkedin)
     * 3 → Expertise      (field, expertise[], bio)
     * 4 → Rates & Avail  (rate_per_minute, preferences)
     * 5 → Done / Submit for approval
     */
 
    public function show(int $step = 1)
    {
        /** @var User $user */
        $user = auth()->user();
 
        if ($user->onboarding_completed && $user->isApproved()) {
            return redirect()->route('admin.mentors.dashboard');
        }
 
        // Cannot skip steps
        if ($step > $user->onboarding_step + 1) {
            return redirect()->route('admin.mentors.onboarding', ['step' => $user->onboarding_step + 1]);
        }
 
        return view("mentors.onboarding.step-{$step}", compact('user', 'step'));
    }
 
    // ── Step 1: Personal info ─────────────────────────────────
    public function saveStep1(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'phone'  => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'avatar' => 'nullable|image|max:2048',
        ]);
 
        /** @var User $user */
        $user = auth()->user();
 
        if ($request->hasFile('avatar')) {
            PublicFileStorage::deleteByUrl($user->avatar_url);
            $data['avatar_url'] = PublicFileStorage::store($request->file('avatar'), "avatars/{$user->id}");
        }
 
        $user->update(array_merge($data, [
            'onboarding_step' => max($user->onboarding_step, 1),
        ]));
 
        return redirect()->route('mentor.onboarding', ['step' => 2]);
    }
 
    // ── Step 2: Professional info ─────────────────────────────
    public function saveStep2(Request $request)
    {
        $data = $request->validate([
            'company'          => 'nullable|string|max:150',
            'designation'      => 'required|string|max:150',
            'experience_years' => 'required|integer|min:0|max:50',
            'linkedin'         => 'nullable|url',
        ]);
 
        /** @var User $user */
        $user = auth()->user();
        $user->update(array_merge($data, [
            'onboarding_step' => max($user->onboarding_step, 2),
        ]));
 
        return redirect()->route('mentor.onboarding', ['step' => 3]);
    }
 
    // ── Step 3: Expertise ─────────────────────────────────────
    public function saveStep3(Request $request)
    {
        $data = $request->validate([
            'field'     => 'required|string|max:100',
            'expertise' => 'required|array|min:1|max:10',
            'expertise.*' => 'string|max:50',
            'bio'       => 'required|string|min:50|max:1000',
        ]);
 
        /** @var User $user */
        $user = auth()->user();
        $user->update(array_merge($data, [
            'onboarding_step' => max($user->onboarding_step, 3),
        ]));
 
        return redirect()->route('mentor.onboarding', ['step' => 4]);
    }
 
    // ── Step 4: Rates & Availability ─────────────────────────
    public function saveStep4(Request $request)
    {
        $data = $request->validate([
            'rate_per_minute' => 'required|numeric|min:0',
            'preferences'     => 'nullable|array',
        ]);
 
        /** @var User $user */
        $user = auth()->user();
        $user->update(array_merge($data, [
            'onboarding_step' => max($user->onboarding_step, 4),
        ]));
 
        return redirect()->route('mentor.onboarding', ['step' => 5]);
    }
 
    // ── Step 5: Submit for approval ───────────────────────────
    public function submit(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
 
        if ($user->onboarding_step < 4) {
            return redirect()->route('mentor.onboarding', ['step' => $user->onboarding_step + 1])
                ->with('error', 'Please complete all steps before submitting.');
        }
 
        $user->update([
            'mentor_status'       => User::MENTOR_STATUS_PENDING,
            'onboarding_step'     => 5,
            'onboarding_completed'=> true,
            'is_active'           => false,   // inactive until approved
        ]);
 
        ActivityLogger::record('mentor_submitted', "Mentor {$user->name} submitted profile for approval", 'users', 'info');
 
        // Notify admins (dispatch a notification/email here)
        // Notification::send(User::admins()->get(), new MentorPendingApprovalNotification($user));
 
        return redirect()->route('mentor.pending-approval');
    }
}