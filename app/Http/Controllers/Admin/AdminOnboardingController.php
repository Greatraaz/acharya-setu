<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\MenteeOnboardingService;
use App\Services\PublicFileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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
            $data['avatar_url'] = PublicFileStorage::store($request->file('avatar'), 'avatars');
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
            PublicFileStorage::deleteByUrl($mentor->avatar_url);
            $data['avatar_url'] = PublicFileStorage::store($request->file('avatar'), 'avatars');
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

    public function createMentee(MenteeOnboardingService $onboarding)
    {
        $mentors = User::mentors()->active()->approved()
            ->select('id', 'name', 'designation', 'field', 'rating')
            ->orderBy('name')
            ->get();

        $streams = $this->educationStreamOptions($onboarding);

        return view('admin.mentees.create', compact('mentors', 'streams'));
    }

    public function storeMentee(Request $request, MenteeOnboardingService $onboarding)
    {
        $request->merge(['tracks' => $this->normalizeMenteeTracks($request)]);

        $data = $request->validate(array_merge(
            $onboarding->adminValidationRules(),
            ['email' => 'required|email|unique:users,email']
        ));

        $avatarUrl = $this->storeAvatar($request);
        $preferences = $onboarding->mergePreferences(new User(), $data);

        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => Hash::make($data['password']),
            'phone'              => $data['phone'] ?? null,
            'gender'             => $data['gender'] ?? null,
            'location'           => $data['address'],
            'avatar_url'         => $avatarUrl,
            'education_stream'   => $data['education_stream'],
            'field'              => $data['field'] ?? null,
            'college'            => $data['college'] ?? null,
            'year'               => $data['year'] ?? null,
            'career_goals'       => $data['tracks'],
            'preferences'        => $preferences,
            'assigned_mentor_id' => $data['assigned_mentor_id'] ?? null,
            'subscription_plan'  => $data['subscription_plan'] ?? 'free',
            'role'                 => 'mentee',
            'onboarding_step'      => MenteeOnboardingService::TOTAL_STEPS,
            'onboarding_completed' => false,
            'is_active'            => true,
        ]);

        $onboarding->syncMenteeTracks($user->id, $data['tracks']);

        $result = $onboarding->complete(
            $user->fresh(),
            autoAssignMentor: empty($data['assigned_mentor_id']) && $request->boolean('auto_assign_mentor', true)
        );

        if (! $result['completed']) {
            return back()
                ->withInput()
                ->withErrors(['onboarding' => 'Missing required onboarding fields: ' . implode(', ', $result['missing'])]);
        }

        ActivityLogger::record(
            'mentee_created_by_admin',
            auth()->user()->name . " created mentee account for: {$user->name}",
            'users', 'success'
        );

        $message = "Mentee account created for {$user->name}.";
        if ($result['assigned'] && $result['mentor']) {
            $message .= " Auto-assigned mentor: {$result['mentor']->name}.";
        }

        return redirect()->route('admin.mentees.show', $user)->with('success', $message);
    }

    public function editMentee(User $mentee, MenteeOnboardingService $onboarding)
    {
        abort_unless($mentee->isMentee(), 403);

        $mentors = User::mentors()->active()->approved()
            ->select('id', 'name', 'designation', 'field')
            ->orderBy('name')
            ->get();

        $streams = $this->educationStreamOptions($onboarding);
        $tracks = old('tracks', $onboarding->menteeTracks($mentee->id));
        $preferences = $mentee->preferences ?? [];

        return view('admin.mentees.edit', compact('mentee', 'mentors', 'streams', 'tracks', 'preferences'));
    }

    public function updateMentee(Request $request, User $mentee, MenteeOnboardingService $onboarding)
    {
        abort_unless($mentee->isMentee(), 403);

        $request->merge(['tracks' => $this->normalizeMenteeTracks($request)]);

        $data = $request->validate(array_merge(
            $onboarding->adminValidationRules(isUpdate: true),
            ['email' => ['required', 'email', Rule::unique('users', 'email')->ignore($mentee->id)]]
        ));

        if ($request->hasFile('avatar')) {
            $data['avatar_url'] = $this->storeAvatar($request, $mentee->avatar_url);
        }

        if (! empty($data['new_password'])) {
            $data['password'] = Hash::make($data['new_password']);
        }

        $preferences = $onboarding->mergePreferences($mentee, $data);

        $mentee->update([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'phone'              => $data['phone'] ?? null,
            'gender'             => $data['gender'] ?? null,
            'location'           => $data['address'],
            'avatar_url'         => $data['avatar_url'] ?? $mentee->avatar_url,
            'education_stream'   => $data['education_stream'],
            'field'              => $data['field'] ?? null,
            'college'            => $data['college'] ?? null,
            'year'               => $data['year'] ?? null,
            'career_goals'       => $data['tracks'],
            'preferences'        => $preferences,
            'assigned_mentor_id' => $data['assigned_mentor_id'] ?? null,
            'subscription_plan'  => $data['subscription_plan'] ?? $mentee->subscription_plan ?? 'free',
            'is_active'          => $request->boolean('is_active', true),
            'password'           => $data['password'] ?? $mentee->password,
        ]);

        $onboarding->syncMenteeTracks($mentee->id, $data['tracks']);

        $result = $onboarding->complete(
            $mentee->fresh(),
            autoAssignMentor: empty($data['assigned_mentor_id']) && $request->boolean('auto_assign_mentor', false)
        );

        if (! $result['completed']) {
            $mentee->update(['onboarding_completed' => false]);

            return back()
                ->withInput()
                ->withErrors(['onboarding' => 'Profile saved but onboarding incomplete. Missing: ' . implode(', ', $result['missing'])]);
        }

        ActivityLogger::record(
            'mentee_updated_by_admin',
            auth()->user()->name . " updated mentee profile: {$mentee->name}",
            'users', 'info'
        );

        $message = 'Mentee profile updated.';
        if ($result['assigned'] && $result['mentor']) {
            $message .= " Auto-assigned mentor: {$result['mentor']->name}.";
        }

        return redirect()->route('admin.mentees.show', $mentee)->with('success', $message);
    }

    private function storeAvatar(Request $request, ?string $existingUrl = null): ?string
    {
        if (! $request->hasFile('avatar')) {
            return null;
        }

        PublicFileStorage::deleteByUrl($existingUrl);

        return PublicFileStorage::store($request->file('avatar'), 'avatars');
    }

    /** @return list<string> */
    private function normalizeMenteeTracks(Request $request): array
    {
        return collect($request->input('tracks', []))
            ->flatten()
            ->filter(fn ($track) => is_string($track) && trim($track) !== '')
            ->map(fn ($track) => trim($track))
            ->unique()
            ->values()
            ->all();
    }

    private function educationStreamOptions(MenteeOnboardingService $onboarding): array
    {
        $catalog = $onboarding->catalogStreams();

        if ($catalog->isNotEmpty()) {
            return $catalog->all();
        }

        return [
            'Technology', 'Business & Management', 'Design & Arts', 'Science & Research',
            'Healthcare', 'Law', 'Finance', 'Marketing', 'Operations', 'Other',
        ];
    }
}
