<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CareerTrack, CareerTrackMilestone};
use Illuminate\Http\{Request, JsonResponse};


class CareerTracksController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        $f = $u->role === 'mentor' ? 'mentor_id' : 'mentee_id';
        return response()->json([
            'tracks' => CareerTrack::where($f, $u->id)->with('milestones')->get(),
        ]);
    }

    
    public function store(Request $request): JsonResponse
    {
        $d = $request->validate([
            'mentee_id'       => 'required|integer',
            'title'           => 'required|string',
            'description'     => 'nullable|string',
            'goal'            => 'nullable|string',
            'duration_months' => 'sometimes|integer',
        ]);
        return response()->json([
            'track' => CareerTrack::create(array_merge($d, ['mentor_id' => $request->user()->id])),
        ], 201);
    }

   
    public function updateMilestone(int $id): JsonResponse
    {
        $m = CareerTrackMilestone::findOrFail($id);
        $m->update(['is_completed' => true, 'completed_at' => now()]);
        return response()->json(['milestone' => $m]);
    }
}
