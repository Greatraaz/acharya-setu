<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallRecord;


class CallsController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $u = $request->user();
        $f = $u->role === 'mentor' ? 'mentor_id' : 'mentee_id';
        return response()->json([
            'calls' => CallRecord::where($f, $u->id)
                ->with(['mentor:id,name,avatar_url', 'mentee:id,name,avatar_url'])
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

   
    public function start(Request $request): JsonResponse
    {
        $d = $request->validate([
            'session_id' => 'required|uuid',
            'mentor_id'  => 'required|uuid',
        ]);
        return response()->json([
            'call' => CallRecord::create(array_merge($d, [
                'mentee_id'  => $request->user()->id,
                'started_at' => now(),
                'status'     => 'active',
            ])),
        ], 201);
    }

    
    public function end(Request $request, int $id): JsonResponse
    {
        $c   = CallRecord::findOrFail($id);
        $dur = now()->diffInSeconds($c->started_at);
        $amt = round($dur / 60 * $c->rate_per_minute, 2);
        $c->update([
            'ended_at'         => now(),
            'duration_seconds' => $dur,
            'total_amount'     => $amt,
            'status'           => 'completed',
        ]);
        return response()->json(['call' => $c, 'duration_seconds' => $dur, 'amount' => $amt]);
    }
}
