<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MentorPendingChange;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
 
class MentorApprovalController extends Controller
{
    // ── List all pending mentor registrations ─────────────────
    public function index(Request $request)
    {
        $query = User::mentors()
            ->with('approvedBy')
            ->withCount('assignedMentees');
 
        if ($status = $request->status) {
            $query->where('mentor_status', $status);
        } else {
            $query->where('mentor_status', '!=', 'approved'); // default: show non-approved
        }
 
        $mentors = $query->latest()->paginate(20);
 
        $stats = [
            'pending'   => User::mentors()->where('mentor_status', 'pending')->count(),
            'approved'  => User::mentors()->where('mentor_status', 'approved')->count(),
            'rejected'  => User::mentors()->where('mentor_status', 'rejected')->count(),
            'suspended' => User::mentors()->where('mentor_status', 'suspended')->count(),
            'pending_changes' => MentorPendingChange::pending()->count(),
        ];
 
        return view('admin.mentors.approvals', compact('mentors', 'stats'));
    }
 
    // ── Profile change requests queue ─────────────────────────
    public function pendingChanges(Request $request)
    {
        $changes = MentorPendingChange::with('mentor', 'reviewer')
            ->pending()
            ->latest()
            ->paginate(20);
 
        return view('admin.mentors.pending-changes', compact('changes'));
    }
 
    // ── View single mentor for review ─────────────────────────
    public function show(User $mentor)
    {
        $mentor->load(['assignedMentees', 'pendingChanges' => fn($q) => $q->latest()->take(5), 'approvedBy']);
        $pendingChange = $mentor->latestPendingChange;
        return view('admin.mentors.review', compact('mentor', 'pendingChange'));
    }
 
    // ── Approve mentor registration ───────────────────────────
    public function approve(Request $request, User $mentor)
    {
        $request->validate(['note' => 'nullable|string|max:500']);
 
        $mentor->approve(auth()->id());
 
        ActivityLogger::record(
            'mentor_approved',
            auth()->user()->name . " approved mentor: {$mentor->name}",
            'users', 'success'
        );
 
        // Send welcome email: Notification::send($mentor, new MentorApprovedNotification());
 
        return redirect()->back()->with('success', "{$mentor->name} has been approved as a mentor.");
    }
 
    // ── Reject mentor registration ────────────────────────────
    public function reject(Request $request, User $mentor)
    {
        $request->validate(['reason' => 'required|string|min:10|max:500']);
 
        $mentor->reject(auth()->id(), $request->reason);
 
        ActivityLogger::record(
            'mentor_rejected',
            auth()->user()->name . " rejected mentor: {$mentor->name}",
            'users', 'warning'
        );
 
        return redirect()->back()->with('success', "{$mentor->name}'s application has been rejected.");
    }
 
    // ── Suspend approved mentor ───────────────────────────────
    public function suspend(Request $request, User $mentor)
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);
 
        $mentor->suspend($request->reason);
 
        ActivityLogger::record(
            'mentor_suspended',
            auth()->user()->name . " suspended mentor: {$mentor->name}",
            'users', 'danger'
        );
 
        return redirect()->back()->with('success', "{$mentor->name} has been suspended.");
    }
 
    // ── Reinstate suspended mentor ────────────────────────────
    public function reinstate(User $mentor)
    {
        $mentor->approve(auth()->id());
 
        ActivityLogger::record(
            'mentor_reinstated',
            auth()->user()->name . " reinstated mentor: {$mentor->name}",
            'users', 'success'
        );
 
        return redirect()->back()->with('success', "{$mentor->name} has been reinstated.");
    }
 
    // ── Approve a profile change request ─────────────────────
    public function approveChange(Request $request, MentorPendingChange $change)
    {
        $change->approve(auth()->id());
 
        ActivityLogger::record(
            'profile_change_approved',
            auth()->user()->name . " approved profile changes for mentor: {$change->mentor->name}",
            'users', 'success'
        );
 
        return redirect()->back()->with('success', 'Profile changes approved and applied.');
    }
 
    // ── Reject a profile change request ──────────────────────
    public function rejectChange(Request $request, MentorPendingChange $change)
    {
        $request->validate(['reason' => 'required|string|min:10|max:500']);
 
        $change->reject(auth()->id(), $request->reason);
 
        ActivityLogger::record(
            'profile_change_rejected',
            auth()->user()->name . " rejected profile changes for mentor: {$change->mentor->name}",
            'users', 'warning'
        );
 
        return redirect()->back()->with('success', 'Profile changes rejected with reason sent to mentor.');
    }
 
    // ── Soft-delete (deactivate) a mentor or mentee ───────────
    public function destroy(Request $request, User $user)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
 
        ActivityLogger::record(
            'user_deleted',
            auth()->user()->name . " soft-deleted user: {$user->name} ({$user->role})",
            'users', 'danger'
        );
 
        $user->delete(); // SoftDeletes trait
 
        return redirect()->route('admin.mentors.approvals')->with('success', "{$user->name} has been deactivated.");
    }
 
    // ── Restore soft-deleted user ─────────────────────────────
    public function restore(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
 
        ActivityLogger::record(
            'user_restored',
            auth()->user()->name . " restored user: {$user->name}",
            'users', 'success'
        );
 
        return redirect()->back()->with('success', "{$user->name} has been restored.");
    }
}
