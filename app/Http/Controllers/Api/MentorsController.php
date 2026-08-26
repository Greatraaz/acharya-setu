<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, SessionReview, MentorAvailability};
use App\Services\MentorAvailabilityService;
use Illuminate\Http\{Request, JsonResponse};

class MentorsController extends Controller
{
    public function __construct(
        private readonly MentorAvailabilityService $availabilityService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = User::where('role', 'mentor')->where('is_active', true);
        if ($s = $request->search) {
            $q->where(fn ($x) => $x->where('name', 'like', "%$s%")->orWhere('field', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }
        if ($f = $request->field) {
            $q->where('field', 'like', "%$f%");
        }
        $mentors = $q->get(['id', 'name', 'field', 'expertise', 'bio', 'rating', 'total_sessions', 'avatar_url', 'gender', 'company', 'designation', 'experience_years'])
            ->map(fn ($m) => array_merge($m->toArray(), [
                'available' => true,
                'nextSlot'  => 'Tomorrow 10 AM',
                'initials'  => strtoupper(implode('', array_map(fn ($p) => $p[0], array_slice(explode(' ', $m->name), 0, 2)))),
            ]));

        return response()->json(['mentors' => $mentors]);
    }

    public function show(int $id): JsonResponse
    {
        $m = User::where('id', $id)->where('role', 'mentor')->firstOrFail();
        $reviews = SessionReview::where('reviewee_id', $id)
            ->with('reviewer:id,name,avatar_url')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json(['mentor' => $m, 'reviews' => $reviews]);
    }

    /**
     * Discrete availability windows for a mentor.
     * Optional ?date=YYYY-MM-DD returns bookable start times for that day.
     */
    public function availability(int $id, Request $request): JsonResponse
    {
        $mentor = User::where('id', $id)->where('role', 'mentor')->where('mentor_status', 'approved')->first();
        if (! $mentor) {
            return response()->json(['message' => 'Mentor not found.'], 404);
        }

        if ($request->filled('date')) {
            $request->validate([
                'date'     => 'date',
                'duration' => 'nullable|integer|in:15,30,45,60,90,120',
            ]);

            return response()->json([
                'mentor_id' => $mentor->id,
                ...$this->availabilityService->slotsForDate(
                    $mentor,
                    $request->input('date'),
                    $request->filled('duration') ? (int) $request->input('duration') : null
                ),
            ]);
        }

        $slots = MentorAvailability::where('mentor_id', $id)
            ->where('is_available', true)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->orderBy('start_time')
            ->get(['id', 'day_of_week', 'start_time', 'end_time']);

        $byDay = [
            'Monday' => [], 'Tuesday' => [], 'Wednesday' => [], 'Thursday' => [],
            'Friday' => [], 'Saturday' => [], 'Sunday' => [],
        ];
        foreach ($slots as $slot) {
            $byDay[$slot->day_of_week][] = [
                'id'         => $slot->id,
                'start_time' => substr((string) $slot->start_time, 0, 5),
                'end_time'   => substr((string) $slot->end_time, 0, 5),
            ];
        }

        $overview = $this->availabilityService->weekOverview($mentor, null, 7);

        return response()->json([
            'mentor_id'      => $mentor->id,
            'availability'   => $slots,
            'by_day'         => $byDay,
            'weekly_summary' => $overview['weekly_summary'],
            'total'          => $slots->count(),
        ]);
    }
}
