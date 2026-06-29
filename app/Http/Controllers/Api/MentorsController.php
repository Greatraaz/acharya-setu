<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, SessionReview, MentorAvailability};
use Illuminate\Http\{Request, JsonResponse};


class MentorsController extends Controller
{
   
    public function index(Request $request): JsonResponse
    {
        $q = User::where('role', 'mentor')->where('is_active', true);
        if ($s = $request->search) {
            $q->where(fn($x) => $x->where('name', 'like', "%$s%")->orWhere('field', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }
        if ($f = $request->field) $q->where('field', 'like', "%$f%");
        $mentors = $q->get(['id', 'name', 'field', 'expertise', 'bio', 'rating', 'total_sessions', 'avatar_url', 'gender', 'company', 'designation', 'experience_years'])
            ->map(fn($m) => array_merge($m->toArray(), [
                'available' => true,
                'nextSlot'  => 'Tomorrow 10 AM',
                'initials'  => strtoupper(implode('', array_map(fn($p) => $p[0], array_slice(explode(' ', $m->name), 0, 2)))),
            ]));
        return response()->json(['mentors' => $mentors]);
    }

   
    public function show(int $id): JsonResponse
    {
        $m       = User::where('id', $id)->where('role', 'mentor')->firstOrFail();
        $reviews = SessionReview::where('reviewee_id', $id)
            ->with('reviewer:id,name,avatar_url')
            ->latest()
            ->limit(5)
            ->get();
        return response()->json(['mentor' => $m, 'reviews' => $reviews]);
    }

   
    public function availability(int $id): JsonResponse
    {
        return response()->json([
            'availability' => MentorAvailability::where('mentor_id', $id)->where('is_available', true)->get(),
        ]);
    }
}
