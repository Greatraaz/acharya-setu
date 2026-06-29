<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WellnessResponse;
use Illuminate\Http\{Request, JsonResponse};

class WellnessController extends Controller
{
   
    public function logMood(Request $request): JsonResponse
    {
        $d = $request->validate([
            'mood'    => 'required|string',
            'score'   => 'nullable|integer',
            'answers' => 'nullable|array',
        ]);
        return response()->json([
            'response' => WellnessResponse::create(array_merge($d, ['user_id' => $request->user()->id])),
        ], 201);
    }

    
    public function history(Request $request): JsonResponse
    {
        return response()->json([
            'history' => WellnessResponse::where('user_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->limit(30)
                ->get(),
        ]);
    }
}
