<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
 
class AdminOnboardingController extends Controller
{
    // ── MENTOR ────────────────────────────────────────────────
 
    public function createMentor()
    {
        return view('admin.mentors.create');
    }
 
    public function storeMentor(Request $request)
    {
        $data = $request->validate([
            // Account
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email',
            'password'         => ['required', Password::min(8)],
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|in:male,female,other,prefer_not_to_say',
            'avatar'           => 'nullable|image|max:2048',
 
            // Professional
            'designation'      => 'required|string|max:150',
            'company'          => 'nullable|string|max:150',
            'experience_years' => 'required|integer|min:0|max:50',
            'linkedin'         => 'nullable|url',
 
            // Expertise
            'field'            => 'required|string|max:100',
            'expertise'        => 'required|array|min:1',
            'expertise.*'      => 'string|max:50',
            'bio'              => 'required|string|min:30|max:1000',
 
            // Rates
            'rate_per_minute'  => 'required|numeric|min:0',
            'preferences'      => 'nullable|array',
            'preferences.*'    => 'string',
 
            // Approval
            'mentor_status'    => 'required|in:pending,approved,rejected',
        ]);
 
        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = Storage::url(
                $request->file('avatar')->store('avatars', 'public')
            );
        }
 
        $user = User::create(array_merge($data, [
            'password'             => Hash::make($data['password']),
            'role'                 => 'mentor',
            'onboarding_step'      => 5,
            'onboarding_completed' => true,
            'is_active'            => $data['mentor_status'] === 'approved',
            'approved_by'          => $data['mentor_status'] === 'approved' ? auth()->id() : null,
            'approved_at'          => $data['mentor_status'] === 'approved' ? now() : null,
        ]));
 
        ActivityLogger::record(
            'mentor_created_by_admin',
            auth()->user()->name . " created mentor account for: {$user->name}",
            'users', 'success'
        );
 
        return redirect()->route('admin.mentors.review', $user)
            ->with('success', "Mentor account created for {$user->name}.");
    }
 
    public function editMentor(User $mentor)
    {
        abort_unless($mentor->isMentor(), 403);
        return view('admin.mentors.edit', compact('mentor'));
    }
 
    public function updateMentor(Request $request, User $mentor)
    {
        abort_unless($mentor->isMentor(), 403);
 
        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email,' . $mentor->id,
            'phone'            => 'nullable|string|max:20',
            'gender'           => 'nullable|in:male,female,other,prefer_not_to_say',
            'avatar'           => 'nullable|image|max:2048',
            'designation'      => 'required|string|max:150',
            'company'          => 'nullable|string|max:150',
            'experience_years' => 'required|integer|min:0|max:50',
            'linkedin'         => 'nullable|url',
            'field'            => 'required|string|max:100',
            'expertise'        => 'nullable|array',
            'expertise.*'      => 'string|max:50',
            'bio'              => 'required|string|min:30|max:1000',
            'rate_per_minute'  => 'required|numeric|min:0',
            'preferences'      => 'nullable|array',
            'mentor_status'    => 'required|in:pending,approved,rejected,suspended',
            'is_active'        => 'nullable|boolean',
            'new_password'     => ['nullable', Password::min(8)],
        ]);
 
        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = Storage::url(
                $request->file('avatar')->store('avatars', 'public')
            );
        }
 
        if (!empty($data['new_password'])) {
            $data['password'] = Hash::make($data['new_password']);
        }
 
        unset($data['new_password']);
 
        // Handle approval state transitions
        $oldStatus = $mentor->mentor_status;
        if ($data['mentor_status'] === 'approved' && $oldStatus !== 'approved') {
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
            $data['is_active']   = true;
        } elseif ($data['mentor_status'] !== 'approved') {
            $data['is_active'] = $request->boolean('is_active');
        }
 
        $mentor->update($data);
 
        ActivityLogger::record(
            'mentor_updated_by_admin',
            auth()->user()->name . " updated mentor profile: {$mentor->name}",
            'users', 'info'
        );
 
        return redirect()->route('admin.mentors.review', $mentor)
            ->with('success', "Mentor profile updated.");
    }
 
    // ── MENTEE ────────────────────────────────────────────────
 
    public function createMentee()
    {
        $mentors = User::mentors()->active()->approved()
            ->select('id', 'name', 'designation', 'field', 'rating')
            ->orderBy('name')
            ->get();
 
        return view('admin.mentees.create', compact('mentors'));
    }
 
    public function storeMentee(Request $request)
    {
        $data = $request->validate([
            // Account
            'name'               => 'required|string|max:100',
            'email'              => 'required|email|unique:users,email',
            'password'           => ['required', Password::min(8)],
            'phone'              => 'nullable|string|max:20',
            'gender'             => 'nullable|in:male,female,other,prefer_not_to_say',
            'avatar'             => 'nullable|image|max:2048',
 
            // Education
            'college'            => 'nullable|string|max:200',
            'year'               => 'nullable|string|max:20',
            'field'              => 'nullable|string|max:100',
            'education_stream'   => 'nullable|string|max:100',
 
            // Goals & Preferences
            'career_goals'       => 'nullable|array',
            'career_goals.*'     => 'string|max:200',
            'strengths'          => 'nullable|array',
            'strengths.*'        => 'string|max:100',
            'preferences'        => 'nullable|array',
 
            // Assignment
            'assigned_mentor_id' => 'nullable|exists:users,id',
            'subscription_plan'  => 'nullable|in:free,basic,pro,enterprise',
        ]);
 
        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = Storage::url(
                $request->file('avatar')->store('avatars', 'public')
            );
        }
 
        $user = User::create(array_merge($data, [
            'password'             => Hash::make($data['password']),
            'role'                 => 'mentee',
            'onboarding_step'      => 4,
            'onboarding_completed' => true,
            'is_active'            => true,
        ]));
 
        ActivityLogger::record(
            'mentee_created_by_admin',
            auth()->user()->name . " created mentee account for: {$user->name}",
            'users', 'success'
        );
 
        return redirect()->route('admin.mentees.show', $user)
            ->with('success', "Mentee account created for {$user->name}.");
    }
 
    public function editMentee(User $mentee)
    {
        abort_unless($mentee->isMentee(), 403);
 
        $mentors = User::mentors()->active()->approved()
            ->select('id', 'name', 'designation', 'field')
            ->orderBy('name')
            ->get();
 
        return view('admin.mentees-edit', compact('mentee', 'mentors'));
    }
 
    public function updateMentee(Request $request, User $mentee)
    {
        abort_unless($mentee->isMentee(), 403);
 
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'email'              => 'required|email|unique:users,email,' . $mentee->id,
            'phone'              => 'nullable|string|max:20',
            'gender'             => 'nullable|in:male,female,other,prefer_not_to_say',
            'avatar'             => 'nullable|image|max:2048',
            'college'            => 'nullable|string|max:200',
            'year'               => 'nullable|string|max:20',
            'field'              => 'nullable|string|max:100',
            'education_stream'   => 'nullable|string|max:100',
            'career_goals'       => 'nullable|array',
            'career_goals.*'     => 'string|max:200',
            'strengths'          => 'nullable|array',
            'strengths.*'        => 'string|max:100',
            'preferences'        => 'nullable|array',
            'assigned_mentor_id' => 'nullable|exists:users,id',
            'subscription_plan'  => 'nullable|in:free,basic,pro,enterprise',
            'is_active'          => 'nullable|boolean',
            'new_password'       => ['nullable', Password::min(8)],
        ]);
 
        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = Storage::url(
                $request->file('avatar')->store('avatars', 'public')
            );
        }
 
        if (!empty($data['new_password'])) {
            $data['password'] = Hash::make($data['new_password']);
        }
        unset($data['new_password']);
 
        $data['is_active'] = $request->boolean('is_active', true);
 
        $mentee->update($data);
 
        ActivityLogger::record(
            'mentee_updated_by_admin',
            auth()->user()->name . " updated mentee profile: {$mentee->name}",
            'users', 'info'
        );
 
        return redirect()->route('admin.mentees.show', $mentee)
            ->with('success', "Mentee profile updated.");
    }
}
