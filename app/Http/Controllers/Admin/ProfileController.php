<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
 
class ProfileController extends Controller
{
    public function edit()
    {
        $user    = auth()->user();
        $pending = $user->latestPendingChange;
        return view('mentor.profile.edit', compact('user', 'pending'));
    }
 
    public function update(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
 
        $data = $request->validate([
            'bio'              => 'nullable|string|min:50|max:1000',
            'expertise'        => 'nullable|array',
            'expertise.*'      => 'string|max:50',
            'field'            => 'nullable|string|max:100',
            'company'          => 'nullable|string|max:150',
            'designation'      => 'nullable|string|max:150',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'linkedin'         => 'nullable|url',
            'rate_per_minute'  => 'nullable|numeric|min:0',
            'phone'            => 'nullable|string|max:20',
        ]);
 
        // Filter to only changed fields
        $changed = array_filter($data, fn($v, $k) => $v !== $user->$k, ARRAY_FILTER_USE_BOTH);
 
        if (empty($changed)) {
            return redirect()->back()->with('info', 'No changes detected.');
        }
 
        // Queue for admin approval
        $pending = $user->requestProfileChange($changed);
 
        ActivityLogger::record(
            'profile_change_requested',
            "Mentor {$user->name} submitted profile changes for approval (#{$pending->id})",
            'users', 'info'
        );
 
        return redirect()->back()->with('success', 'Your changes have been submitted for admin review. They will appear on your profile once approved.');
    }
 
    // Mentor can cancel their pending request
    public function cancelPending()
    {
        /** @var User $user */
        $user = auth()->user();
        $user->pendingChanges()->pending()->delete();
        $user->update(['has_pending_changes' => false]);
 
        return redirect()->back()->with('success', 'Pending change request cancelled.');
    }
}
