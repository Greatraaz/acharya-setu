<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{SessionReview, Session, User};
use Illuminate\Http\{Request, JsonResponse};

class ReviewsController extends Controller
{
    
    public function store(Request $request): JsonResponse
    {
        $d   = $request->validate([
            'session_id' => 'required|integer|exists:sessions,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string',
        ]);
        $s   = Session::findOrFail($d['session_id']);
        $rid = $request->user()->id === $s->mentor_id ? $s->mentee_id : $s->mentor_id;
        $r   = SessionReview::updateOrCreate(
            ['session_id' => $d['session_id'], 'reviewer_id' => $request->user()->id],
            ['reviewee_id' => $rid, 'rating' => $d['rating'], 'comment' => $d['comment'] ?? null]
        );
        User::where('id', $rid)->update([
            'rating' => round(SessionReview::where('reviewee_id', $rid)->avg('rating'), 2),
        ]);
        return response()->json(['review' => $r], 201);
    }
}
