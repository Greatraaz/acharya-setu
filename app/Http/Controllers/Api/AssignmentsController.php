<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User};
use Illuminate\Http\{Request, JsonResponse};


class AssignmentsController extends Controller
{
   
    public function myMentees(Request $request): JsonResponse
    {
        return response()->json([
            'mentees' => User::where('assigned_mentor_id', $request->user()->id)
                ->where('role', 'mentee')
                ->get(['id', 'name', 'email', 'field', 'college', 'year', 'avatar_url', 'subscription_plan', 'onboarding_completed']),
        ]);
    }

    
    public function myMentor(Request $request): JsonResponse
    {
        return response()->json(['mentor' => User::find($request->user()->assigned_mentor_id)]);
    }

   
    public function assign(Request $request): JsonResponse
    {
        $d = $request->validate([
            'mentee_id' => 'required|uuid|exists:users,id',
            'mentor_id' => 'required|uuid|exists:users,id',
        ]);
        User::where('id', $d['mentee_id'])->update(['assigned_mentor_id' => $d['mentor_id']]);
        $mentee = User::query()->find($d['mentee_id']);
        if ($mentee) {
            $notifications = app(\App\Services\NotificationService::class);
            $notifications->send(
                $mentee,
                'mentor_assigned',
                'Mentor Assigned!',
                'A mentor has been assigned to you!',
                ['mentor_id' => $d['mentor_id']],
                $notifications->roleUrl($mentee, 'mentor.change')
            );
        }
        return response()->json(['message' => 'Mentor assigned']);
    }
}
