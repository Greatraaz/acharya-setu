<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorResource;
use App\Models\{MentorRequest, SessionReview, User, MentorAvailability};
use App\Services\MentorAvailabilityService;
use App\Support\MentorBrowseQuery;
use Illuminate\Http\{Request, JsonResponse};

class MentorsController extends Controller
{
    public function __construct(
        private readonly MentorAvailabilityService $availabilityService
    ) {}

    /**
     * List approved mentors (full public profile fields).
     * GET /api/v1/mentee/mentors
     */
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search'            => 'nullable|string|max:100',
            'q'                 => 'nullable|string|max:100',
            'field'             => 'nullable|string|max:100',
            'company'           => 'nullable|string|max:100',
            'gender'            => 'nullable|string|max:20',
            'min_rating'        => 'nullable|numeric|min:0|max:5',
            'sort'              => 'nullable|in:best,rating,sessions,name,rate_asc,rate_desc',
            'per_page'          => 'nullable|integer|min:1|max:100',
            'exclude_assigned'  => 'nullable|boolean',
        ]);

        $search  = trim((string) ($data['search'] ?? $data['q'] ?? ''));
        $perPage = (int) ($data['per_page'] ?? 20);
        $sort    = $data['sort'] ?? 'best';
        $mentee  = $request->user();

        $query = MentorBrowseQuery::fromRequest($request, $mentee);
        MentorBrowseQuery::applySort($query, $sort);

        $paginator = $query->paginate($perPage)->withQueryString();

        $pendingMentorIds = [];
        if ($mentee) {
            $pendingMentorIds = MentorRequest::where('mentee_id', $mentee->id)
                ->where('status', MentorRequest::STATUS_PENDING)
                ->pluck('mentor_id')
                ->all();
        }

        $mentors = collect($paginator->items())->map(function (User $m) use ($pendingMentorIds) {
            return MentorResource::toArray($m, [
                'available'            => true,
                'has_pending_request'  => in_array($m->id, $pendingMentorIds, true),
                'next_slot'            => null,
            ]);
        })->values();

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
                'search'           => $search !== '' ? $search : null,
                'field'            => $data['field'] ?? null,
                'company'          => $data['company'] ?? null,
                'gender'           => $data['gender'] ?? null,
                'min_rating'       => isset($data['min_rating']) ? (float) $data['min_rating'] : null,
                'sort'             => $sort,
                'exclude_assigned' => $request->boolean('exclude_assigned'),
                'fields'           => MentorResource::distinctFields(),
            ],
        ]);
    }

    /**
     * Available mentors for change/choose mentor screen (mentee).
     * GET /api/v1/mentee/mentors/available
     */
    public function available(Request $request): JsonResponse
    {
        $request->merge(['exclude_assigned' => true]);

        $mentee = $request->user();
        $assignedMentor = $mentee->assignedMentor;

        $pendingRequests = MentorRequest::where('mentee_id', $mentee->id)
            ->where('status', MentorRequest::STATUS_PENDING)
            ->with('mentor')
            ->latest()
            ->get()
            ->map(fn (MentorRequest $req) => [
                'id'         => $req->id,
                'mentor_id'  => $req->mentor_id,
                'status'     => $req->status,
                'message'    => $req->message,
                'created_at' => $req->created_at?->toIso8601String(),
                'mentor'     => $req->mentor ? MentorResource::toArray($req->mentor) : null,
            ]);

        $listResponse = $this->index($request);
        $payload = $listResponse->getData(true);

        return response()->json([
            'status'           => true,
            'statuscode'       => 200,
            'assigned_mentor'  => $assignedMentor ? MentorResource::toArray($assignedMentor) : null,
            'pending_requests' => $pendingRequests,
            'mentors'          => $payload['mentors'] ?? [],
            'meta'             => $payload['meta'] ?? [],
            'filters'          => $payload['filters'] ?? [],
        ]);
    }

    /**
     * Single mentor profile (all public fields).
     * GET /api/v1/mentee/mentors/{id}
     */
    public function show(int $id): JsonResponse
    {
        $mentor = User::where('id', $id)
            ->where('role', 'mentor')
            ->where('mentor_status', User::MENTOR_STATUS_APPROVED)
            ->where('is_active', true)
            ->firstOrFail();

        $reviews = SessionReview::where('reviewee_id', $id)
            ->with('reviewer:id,name,avatar_url')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($review) => [
                'id'                   => $review->id,
                'overall_rating'       => $review->overall_rating,
                'communication_rating' => $review->communication_rating,
                'knowledge_rating'     => $review->knowledge_rating,
                'punctuality_rating'   => $review->punctuality_rating,
                'helpfulness_rating'   => $review->helpfulness_rating,
                'review_text'          => $review->review_text,
                'would_recommend'      => $review->would_recommend,
                'created_at'           => $review->created_at?->toIso8601String(),
                'reviewer'             => $review->reviewer ? [
                    'id'         => $review->reviewer->id,
                    'name'       => $review->reviewer->name,
                    'avatar_url' => $review->reviewer->avatar_url,
                ] : null,
            ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mentor'     => MentorResource::toArray($mentor),
            'reviews'    => $reviews,
        ]);
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
