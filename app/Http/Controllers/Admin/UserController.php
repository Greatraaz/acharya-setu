<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\MenteeOnboardingService;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function menteeIndex(Request $request)
    {
        $mentees = User::where('role', 'mentee')
            ->withTrashed()
            ->with('assignedMentor')
            ->when($request->search, fn($q) =>
                $q->where(fn($q) => $q
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('college', 'like', "%{$request->search}%")
                )
            )
            ->when($request->status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($request->onboarded !== null && $request->onboarded !== '',
                fn($q) => $q->where('onboarding_completed', (bool) $request->onboarded)
            )
            ->when($request->assigned === 'yes', fn($q) => $q->whereNotNull('assigned_mentor_id'))
            ->when($request->assigned === 'no',  fn($q) => $q->whereNull('assigned_mentor_id'))
            ->whereNull('deleted_at')      // exclude soft-deleted from main list
            ->latest()
            ->paginate(20)
            ->withQueryString();
 
        return view('admin.mentees.index', compact('mentees'));
    }
 
    public function menteeShow(User $mentee, MenteeOnboardingService $onboarding)
    {
        $mentee->load(['assignedMentor', 'menteeSessions.mentor', 'enrollments.stream']);
        $tracks = $onboarding->menteeTracks($mentee->id);
        $preferences = $mentee->preferences ?? [];

        return view('admin.mentees.show', compact('mentee', 'tracks', 'preferences'));
    }

    public function menteeJourney(User $mentee)
    {
        abort_unless($mentee->role === 'mentee', 404);

        return redirect()->route('admin.curriculum.streams', [
            'mentee_id' => $mentee->id,
        ]);
    }
 
    public function menteeToggleStatus(User $mentee)
    {
        $mentee->update(['is_active' => !$mentee->is_active]);
        ActivityLogger::record(
            'user_status_changed',
            auth()->user()->name . " set mentee {$mentee->name} to " . ($mentee->is_active ? 'active' : 'inactive'),
            'users', 'info'
        );
        return redirect()->back()->with('success', "Mentee status updated.");
    }
 
    public function menteeAssignMentor(Request $request, User $mentee)
    {
        $request->validate(['mentor_id' => 'nullable|exists:users,id']);
 
        $old = $mentee->assigned_mentor_id;
        $mentee->update(['assigned_mentor_id' => $request->mentor_id ?: null]);
 
        $mentor = $request->mentor_id ? User::find($request->mentor_id) : null;
        ActivityLogger::record(
            'mentor_assigned',
            auth()->user()->name . " assigned mentor " . ($mentor?->name ?? 'none') . " to mentee {$mentee->name}",
            'users', 'info'
        );
 
        return redirect()->back()->with('success', $mentor ? "Mentor assigned to {$mentee->name}." : "Mentor unassigned.");
    }
 
    public function menteeDestroy(User $mentee)
    {
        ActivityLogger::record('user_deleted', auth()->user()->name . " deleted mentee: {$mentee->name}", 'users', 'danger');
        $mentee->delete();
        return redirect()->route('admin.mentees.index')->with('success', "{$mentee->name} has been deleted.");
    }
 
    public function menteeTrashed(Request $request)
    {
        $users = User::where('role', 'mentee')
            ->onlyTrashed()
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();
 
        return view('admin.users.trashed', ['users' => $users, 'type' => 'mentee']);
    }
 
    public function restore(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        ActivityLogger::record('user_restored', auth()->user()->name . " restored user: {$user->name}", 'users', 'success');
        return redirect()->back()->with('success', "{$user->name} has been restored.");
    }
 
    public function forceDelete(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        ActivityLogger::record('user_force_deleted', auth()->user()->name . " permanently deleted: {$user->name}", 'users', 'danger');
        $user->forceDelete();
        return redirect()->back()->with('success', "User permanently deleted.");
    }
 
    // ── MENTOR ROUTES ─────────────────────────────────────────
 
    public function mentorIndex(Request $request)
    {
        $mentors = User::where('role', 'mentor')
            ->with('assignedMentees')
            ->when($request->search, fn($q) =>
                $q->where(fn($q) => $q
                    ->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('company', 'like', "%{$request->search}%")
                    ->orWhere('designation', 'like', "%{$request->search}%")
                )
            )
            ->when($request->mentor_status, fn($q) => $q->where('mentor_status', $request->mentor_status))
            ->when($request->field, fn($q) => $q->where('field', 'like', "%{$request->field}%"))
            ->when($request->pending_changes === '1', fn($q) => $q->where('has_pending_changes', true))
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(20)
            ->withQueryString();
 
        return view('admin.mentors.index', compact('mentors'));
    }
 
    public function mentorToggleStatus(User $mentor)
    {
        $mentor->update(['is_active' => !$mentor->is_active]);
        ActivityLogger::record(
            'mentor_status_changed',
            auth()->user()->name . " set mentor {$mentor->name} to " . ($mentor->is_active ? 'active' : 'inactive'),
            'users', 'info'
        );
        return redirect()->back()->with('success', "Mentor status updated.");
    }
 
    public function mentorTrashed(Request $request)
    {
        $users = User::where('role', 'mentor')
            ->onlyTrashed()
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->latest('deleted_at')
            ->paginate(20)
            ->withQueryString();
 
        return view('admin.users.trashed', ['users' => $users, 'type' => 'mentor']);
    }
}
