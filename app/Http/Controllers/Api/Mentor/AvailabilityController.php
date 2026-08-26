<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentorAvailability;
use App\Services\MentorAvailabilityService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function __construct(
        private readonly MentorAvailabilityService $availabilityService
    ) {}

    /**
     * GET /mentor/availability
     * Returns flat slot list + by_day grouped ranges.
     */
    public function index(Request $request): JsonResponse
    {
        $mentor = $request->user();
        $slots = MentorAvailability::where('mentor_id', $mentor->id)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'status'       => true,
            'statuscode'   => 200,
            'availability' => $this->decorateSlots($slots),
            'by_day'       => $this->groupByDay($slots),
            'total'        => $slots->count(),
        ]);
    }

    /**
     * GET /mentor/availability/available
     */
    public function available(Request $request): JsonResponse
    {
        $slots = MentorAvailability::where('mentor_id', $request->user()->id)
            ->where('is_available', true)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->orderBy('start_time')
            ->get(['id', 'day_of_week', 'start_time', 'end_time', 'is_available']);

        return response()->json([
            'status'       => true,
            'statuscode'   => 200,
            'availability' => $this->decorateSlots($slots),
            'by_day'       => $this->groupByDay($slots),
            'total'        => $slots->count(),
        ]);
    }

    /**
     * GET /mentors/{mentorId}/availability
     * Optional ?date=YYYY-MM-DD returns bookable start times for that day.
     */
    public function getByMentor(int $mentorId, Request $request): JsonResponse
    {
        $mentor = \App\Models\User::where('role', 'mentor')
            ->where('mentor_status', 'approved')
            ->find($mentorId);

        if (! $mentor) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Mentor not found.',
            ], 404);
        }

        if ($request->filled('date')) {
            $request->validate([
                'date'     => 'date',
                'duration' => 'nullable|integer|in:15,30,45,60,90,120',
            ]);
            $payload = $this->availabilityService->slotsForDate(
                $mentor,
                $request->input('date'),
                $request->filled('duration') ? (int) $request->input('duration') : null
            );

            return response()->json([
                'status'     => true,
                'statuscode' => 200,
                'mentor_id'  => $mentorId,
                ...$payload,
            ]);
        }

        if ($request->boolean('week') || $request->filled('days') || $request->filled('start')) {
            $days = min(42, max(7, (int) $request->input('days', 14)));
            $start = $request->filled('start')
                ? \Carbon\Carbon::parse($request->input('start'), 'Asia/Kolkata')->startOfDay()
                : null;
            $overview = $this->availabilityService->weekOverview($mentor, $start, $days);

            return response()->json([
                'status'     => true,
                'statuscode' => 200,
                'mentor_id'  => $mentorId,
                ...$overview,
            ]);
        }

        $slots = MentorAvailability::where('mentor_id', $mentorId)
            ->where('is_available', true)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->orderBy('start_time')
            ->get(['id', 'day_of_week', 'start_time', 'end_time']);

        // Prefer preference-backed overview when table is empty but prefs exist
        $overview = $this->availabilityService->weekOverview($mentor, null, 7);

        return response()->json([
            'status'         => true,
            'statuscode'     => 200,
            'mentor_id'      => $mentorId,
            'availability'   => $slots,
            'by_day'         => $this->groupByDay($slots),
            'weekly_summary' => $overview['weekly_summary'],
            'total'          => $slots->count(),
        ]);
    }

    /**
     * PUT /mentor/availability — replace ALL discrete slots (each window has its own duration).
     *
     * {
     *   "slots": [
     *     { "day_of_week": "Monday", "start_time": "09:00", "end_time": "10:00", "is_available": true },
     *     { "day_of_week": "Monday", "start_time": "10:00", "end_time": "10:30", "is_available": true },
     *     { "day_of_week": "Monday", "start_time": "14:00", "duration": 45, "is_available": true }
     *   ]
     * }
     * duration (minutes) may be sent instead of end_time.
     */
    public function update(Request $request): JsonResponse
    {
        $d = $this->validateSlotsPayload($request);
        $this->assertNoOverlaps($d['slots']);

        $mentor = $request->user();
        MentorAvailability::where('mentor_id', $mentor->id)->delete();

        foreach ($d['slots'] as $slot) {
            MentorAvailability::create(array_merge($slot, ['mentor_id' => $mentor->id]));
        }

        $this->availabilityService->syncPreferencesFromTable($mentor);

        $slots = MentorAvailability::where('mentor_id', $mentor->id)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'status'       => true,
            'statuscode'   => 200,
            'message'      => 'Availability updated successfully',
            'total'        => $slots->count(),
            'availability' => $this->decorateSlots($slots),
            'by_day'       => $this->groupByDay($slots),
        ]);
    }

    /**
     * POST /mentor/availability — append discrete slots (does not wipe existing).
     */
    public function store(Request $request): JsonResponse
    {
        $d = $this->validateSlotsPayload($request);

        $mentor = $request->user();
        $existing = MentorAvailability::where('mentor_id', $mentor->id)->get()
            ->map(fn ($s) => [
                'day_of_week'  => $s->day_of_week,
                'start_time'   => substr((string) $s->start_time, 0, 5),
                'end_time'     => substr((string) $s->end_time, 0, 5),
                'is_available' => (bool) $s->is_available,
            ])
            ->all();

        $this->assertNoOverlaps(array_merge($existing, $d['slots']));

        $created = [];
        foreach ($d['slots'] as $slot) {
            $created[] = MentorAvailability::create(
                array_merge($slot, ['mentor_id' => $mentor->id])
            );
        }

        $this->availabilityService->syncPreferencesFromTable($mentor);

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Slots added successfully',
            'added'      => count($created),
            'slots'      => $this->decorateSlots(collect($created)),
            'by_day'     => $this->groupByDay(
                MentorAvailability::where('mentor_id', $mentor->id)->orderBy('start_time')->get()
            ),
        ], 201);
    }

    public function toggle(Request $request, int $id): JsonResponse
    {
        $slot = MentorAvailability::where('id', $id)
            ->where('mentor_id', $request->user()->id)
            ->first();

        if (! $slot) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Slot not found.',
            ], 404);
        }

        $slot->update(['is_available' => ! $slot->is_available]);
        $this->availabilityService->syncPreferencesFromTable($request->user());

        return response()->json([
            'status'       => true,
            'statuscode'   => 200,
            'message'      => 'Slot marked '.($slot->is_available ? 'available' : 'unavailable'),
            'is_available' => $slot->is_available,
            'slot'         => $slot->fresh(),
        ]);
    }

    public function updateSlot(Request $request, int $id): JsonResponse
    {
        $slot = MentorAvailability::where('id', $id)
            ->where('mentor_id', $request->user()->id)
            ->first();

        if (! $slot) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Slot not found.',
            ], 404);
        }

        $d = $request->validate([
            'day_of_week'  => 'sometimes|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'   => 'sometimes|string|date_format:H:i',
            'end_time'     => 'sometimes|string|date_format:H:i',
            'is_available' => 'sometimes|boolean',
        ]);

        $start = $d['start_time'] ?? substr((string) $slot->start_time, 0, 5);
        $end = $d['end_time'] ?? substr((string) $slot->end_time, 0, 5);
        if ($start >= $end) {
            throw ValidationException::withMessages([
                'end_time' => 'End time must be after start time.',
            ]);
        }

        $slot->update($d);
        $this->availabilityService->syncPreferencesFromTable($request->user());

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Slot updated successfully',
            'slot'       => $slot->fresh(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $slot = MentorAvailability::where('id', $id)
            ->where('mentor_id', $request->user()->id)
            ->first();

        if (! $slot) {
            return response()->json([
                'status'     => false,
                'statuscode' => 404,
                'message'    => 'Slot not found.',
            ], 404);
        }

        $slot->delete();
        $this->availabilityService->syncPreferencesFromTable($request->user());

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Slot deleted successfully',
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $deleted = MentorAvailability::where('mentor_id', $request->user()->id)->delete();
        $this->availabilityService->syncPreferencesFromTable($request->user());

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'All availability slots cleared',
            'deleted'    => $deleted,
        ]);
    }

    private function validateSlotsPayload(Request $request): array
    {
        $d = $request->validate([
            'slots'                => 'required|array|min:1',
            'slots.*.day_of_week'  => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'slots.*.start_time'   => 'required|string|date_format:H:i',
            'slots.*.end_time'     => 'nullable|string|date_format:H:i',
            'slots.*.duration'     => 'nullable|integer|min:15|max:480',
            'slots.*.is_available' => 'nullable|boolean',
        ]);

        foreach ($d['slots'] as $i => $slot) {
            $start = substr((string) $slot['start_time'], 0, 5);
            $end = isset($slot['end_time']) ? substr((string) $slot['end_time'], 0, 5) : null;
            $duration = isset($slot['duration']) ? (int) $slot['duration'] : null;

            if ((! $end || $end === '') && $duration) {
                $end = \Carbon\Carbon::createFromFormat('H:i', $start)->addMinutes($duration)->format('H:i');
            }

            if (! $end) {
                throw ValidationException::withMessages([
                    "slots.{$i}.end_time" => 'Provide end_time or duration for each slot.',
                ]);
            }
            if ($start >= $end) {
                throw ValidationException::withMessages([
                    "slots.{$i}.end_time" => 'End time must be after start time.',
                ]);
            }

            $mins = \Carbon\Carbon::createFromFormat('H:i', $start)->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', $end));
            if ($mins < 15) {
                throw ValidationException::withMessages([
                    "slots.{$i}.end_time" => 'Each slot must be at least 15 minutes.',
                ]);
            }

            $d['slots'][$i] = [
                'day_of_week'  => $slot['day_of_week'],
                'start_time'   => $start,
                'end_time'     => $end,
                'is_available' => array_key_exists('is_available', $slot)
                    ? filter_var($slot['is_available'], FILTER_VALIDATE_BOOLEAN)
                    : true,
            ];
        }

        return $d;
    }

    /**
     * @param  list<array{day_of_week:string,start_time:string,end_time:string}>  $slots
     */
    private function assertNoOverlaps(array $slots): void
    {
        $byDay = [];
        foreach ($slots as $slot) {
            if (($slot['is_available'] ?? true) === false) {
                continue;
            }
            $day = $slot['day_of_week'];
            $byDay[$day][] = [
                'from' => substr($slot['start_time'], 0, 5),
                'to'   => substr($slot['end_time'], 0, 5),
            ];
        }

        foreach ($byDay as $day => $ranges) {
            usort($ranges, fn ($a, $b) => strcmp($a['from'], $b['from']));
            for ($i = 0; $i < count($ranges); $i++) {
                for ($j = $i + 1; $j < count($ranges); $j++) {
                    $a = $ranges[$i];
                    $b = $ranges[$j];
                    // Adjacent OK (10:00 end + 10:00 start); only reject true overlap
                    if ($a['from'] < $b['to'] && $b['from'] < $a['to']) {
                        throw ValidationException::withMessages([
                            'slots' => "Overlapping slots on {$day}: {$a['from']}–{$a['to']} and {$b['from']}–{$b['to']}.",
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection|iterable  $slots
     * @return array<string, list<array{id?:int,start_time:string,end_time:string,is_available?:bool}>>
     */
    private function groupByDay(iterable $slots): array
    {
        $days = [
            'Monday' => [], 'Tuesday' => [], 'Wednesday' => [], 'Thursday' => [],
            'Friday' => [], 'Saturday' => [], 'Sunday' => [],
        ];

        foreach ($slots as $slot) {
            $day = is_array($slot) ? ($slot['day_of_week'] ?? '') : $slot->day_of_week;
            if (! isset($days[$day])) {
                continue;
            }
            $start = substr((string) (is_array($slot) ? $slot['start_time'] : $slot->start_time), 0, 5);
            $end = substr((string) (is_array($slot) ? $slot['end_time'] : $slot->end_time), 0, 5);
            $mins = 0;
            try {
                $mins = \Carbon\Carbon::createFromFormat('H:i', $start)->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', $end));
            } catch (\Throwable) {
            }
            $days[$day][] = [
                'id'           => is_array($slot) ? ($slot['id'] ?? null) : $slot->id,
                'start_time'   => $start,
                'end_time'     => $end,
                'duration'     => $mins,
                'is_available' => is_array($slot)
                    ? (bool) ($slot['is_available'] ?? true)
                    : (bool) $slot->is_available,
            ];
        }

        return $days;
    }

    /**
     * @param  \Illuminate\Support\Collection|iterable  $slots
     * @return list<array<string,mixed>>
     */
    private function decorateSlots(iterable $slots): array
    {
        $out = [];
        foreach ($slots as $slot) {
            $arr = is_array($slot) ? $slot : $slot->toArray();
            $start = substr((string) ($arr['start_time'] ?? ''), 0, 5);
            $end = substr((string) ($arr['end_time'] ?? ''), 0, 5);
            $mins = 0;
            try {
                $mins = \Carbon\Carbon::createFromFormat('H:i', $start)->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', $end));
            } catch (\Throwable) {
            }
            $arr['start_time'] = $start;
            $arr['end_time'] = $end;
            $arr['duration'] = $mins;
            $out[] = $arr;
        }

        return $out;
    }
}
