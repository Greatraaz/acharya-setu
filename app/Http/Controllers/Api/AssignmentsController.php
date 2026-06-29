<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, Notification};
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
        Notification::create([
            'user_id' => $d['mentee_id'],
            'type'    => 'mentor_assigned',
            'title'   => 'Mentor Assigned!',
            'body'    => 'A mentor has been assigned to you!',
        ]);
        return response()->json(['message' => 'Mentor assigned']);
    }
}
