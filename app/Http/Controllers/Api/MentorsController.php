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
        $data = $request->validate([
            'search'     => 'nullable|string|max:100',
            'field'      => 'nullable|string|max:100',
            'company'    => 'nullable|string|max:100',
            'gender'     => 'nullable|string|max:20',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'sort'       => 'nullable|in:best,rating,sessions,name',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $search  = trim((string) ($data['search'] ?? ''));
        $perPage = $data['per_page'] ?? 20;
        $sort    = $data['sort'] ?? 'best';

        $query = User::where('role', 'mentor')
            ->where('is_active', true)
            ->where('mentor_status', 'approved')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('field', 'like', '%'.$search.'%')
                        ->orWhere('company', 'like', '%'.$search.'%')
                        ->orWhere('designation', 'like', '%'.$search.'%')
                        ->orWhere('bio', 'like', '%'.$search.'%');
                });
            })
            ->when(! empty($data['field']), fn ($q) => $q->where('field', 'like', '%'.$data['field'].'%'))
            ->when(! empty($data['company']), fn ($q) => $q->where('company', 'like', '%'.$data['company'].'%'))
            ->when(! empty($data['gender']), fn ($q) => $q->where('gender', $data['gender']))
            ->when(isset($data['min_rating']), fn ($q) => $q->where('rating', '>=', (float) $data['min_rating']));

        match ($sort) {
            'rating'   => $query->orderByDesc('rating'),
            'sessions' => $query->orderByDesc('total_sessions'),
            'name'     => $query->orderBy('name'),
            default    => $query->orderByDesc('rating')->orderByDesc('total_sessions'),
        };

        $paginator = $query
            ->paginate($perPage, [
                'id', 'name', 'field', 'expertise', 'bio', 'rating', 'total_sessions',
                'avatar_url', 'gender', 'company', 'designation', 'experience_years',
                'rate_per_minute', 'slug',
            ])
            ->withQueryString();

        $mentors = collect($paginator->items())->map(fn ($m) => array_merge($m->toArray(), [
            'available' => true,
            'nextSlot'  => null,
            'initials'  => strtoupper(implode('', array_map(
                fn ($p) => $p[0] ?? '',
                array_slice(explode(' ', (string) $m->name), 0, 2)
            ))),
        ]))->values();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mentors'    => $mentors,
            'meta'       => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
            'filters'    => [
                'search'     => $search !== '' ? $search : null,
                'field'      => $data['field'] ?? null,
                'company'    => $data['company'] ?? null,
                'gender'     => $data['gender'] ?? null,
                'min_rating' => isset($data['min_rating']) ? (float) $data['min_rating'] : null,
                'sort'       => $sort,
            ],
        ]);
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
     * Mentor availability for booking UIs.
     *
     * - ?date=YYYY-MM-DD&duration=30  → non-booked start times that fit duration
     * - ?week=1&days=31&start=YYYY-MM-DD → calendar overview (which dates have slots)
     * - (no params) → weekly schedule + 7-day summary
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

        if ($request->boolean('week') || $request->filled('days') || $request->filled('start')) {
            $days = min(42, max(7, (int) $request->input('days', 14)));
            $start = $request->filled('start')
                ? \Carbon\Carbon::parse($request->input('start'), 'Asia/Kolkata')->startOfDay()
                : null;
            $overview = $this->availabilityService->weekOverview($mentor, $start, $days);

            return response()->json([
                'mentor_id' => $mentor->id,
                ...$overview,
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
