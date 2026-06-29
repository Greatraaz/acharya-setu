<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\MentorAvailability;
use Illuminate\Http\{Request, JsonResponse};

class AvailabilityController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  GET /mentor/availability
    //  Authenticated mentor fetches their own slots
    // ─────────────────────────────────────────────────────────────
   
    public function index(Request $request): JsonResponse
    {
        $slots = MentorAvailability::where('mentor_id', $request->user()->id)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->get();
        
        $statusCode = 200;

        return response()->json([
            'status'       => true,
            'statuscode'   => $statusCode,
            'availability' => $slots,
            'total'        => $slots->count(),
        ], $statusCode);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /mentor/availability/available
    //  Authenticated mentor fetches only is_available = true slots
    // ─────────────────────────────────────────────────────────────
    
    public function available(Request $request): JsonResponse
    {
        $slots = MentorAvailability::where('mentor_id', $request->user()->id)
            ->where('is_available', true)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->get(['id', 'day_of_week', 'start_time', 'end_time']);

        $statusCode = 200;
        return response()->json([
            'status'       => true,
            'statuscode'   => $statusCode,
            'availability' => $slots,
            'total'        => $slots->count(),
        ], $statusCode);
    }

    // ─────────────────────────────────────────────────────────────
    //  GET /mentors/{mentorId}/availability
    //  Public — mentee fetches a specific mentor's available slots
    // ─────────────────────────────────────────────────────────────
   
    public function getByMentor(int $mentorId): JsonResponse
    {
        $slots = MentorAvailability::where('mentor_id', $mentorId)
            ->where('is_available', true)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->get(['id', 'day_of_week', 'start_time', 'end_time']);

        $statusCode = 200;
        return response()->json([
            'status'       => true,
            'statuscode'   => $statusCode,
            'mentor_id'    => $mentorId,
            'availability' => $slots,
            'total'        => $slots->count(),
        ], $statusCode);
    }

    // ─────────────────────────────────────────────────────────────
    //  PUT /mentor/availability
    //  Replace ALL slots in one go (bulk)
    // ─────────────────────────────────────────────────────────────
 
    public function update(Request $request): JsonResponse
    {
        $d = $request->validate([
            'slots'                => 'required|array',
            'slots.*.day_of_week'  => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'slots.*.start_time'   => 'required|string|date_format:H:i',
            'slots.*.end_time'     => 'required|string|date_format:H:i|after:slots.*.start_time',
            'slots.*.is_available' => 'required|boolean',
        ]);

        MentorAvailability::where('mentor_id', $request->user()->id)->delete();

        foreach ($d['slots'] as $slot) {
            MentorAvailability::create(array_merge($slot, ['mentor_id' => $request->user()->id]));
        }

        $slots = MentorAvailability::where('mentor_id', $request->user()->id)
            ->orderByRaw("FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")
            ->get();

        $statusCode = 200;
        return response()->json([
            'status'       => true,
            'statuscode'   => $statusCode,
            'message'      => 'Availability updated successfully',
            'total'        => $slots->count(),
            'availability' => $slots,
        ], $statusCode);
    }

   
    public function store(Request $request): JsonResponse
    {
        $d = $request->validate([
            'slots'                => 'required|array|min:1',
            'slots.*.day_of_week'  => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'slots.*.start_time'   => 'required|string|date_format:H:i',
            'slots.*.end_time'     => 'required|string|date_format:H:i|after:slots.*.start_time',
            'slots.*.is_available' => 'required|boolean',
        ]);

        $created = [];
        foreach ($d['slots'] as $slot) {
            $created[] = MentorAvailability::create(
                array_merge($slot, ['mentor_id' => $request->user()->id])
            );
        }

        $statusCode = 201;
        return response()->json([
            'status'     => true,
            'statuscode' => $statusCode,
            'message'    => 'Slots added successfully',
            'added'      => count($created),
            'slots'      => $created,
        ], $statusCode);
    }

    // ─────────────────────────────────────────────────────────────
    //  PATCH /mentor/availability/{id}/toggle
    //  Toggle is_available on a single slot
    // ─────────────────────────────────────────────────────────────
   
    public function toggle(Request $request, int $id): JsonResponse
    {
        $slot = MentorAvailability::where('id', $id)
            ->where('mentor_id', $request->user()->id)
            ->first();

        if (!$slot) {
            $statusCode = 404;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => 'Slot not found.'
            ], $statusCode);
        }

        $slot->update(['is_available' => !$slot->is_available]);
        $statusCode = 200;

        return response()->json([
            'status'       => true,
            'statuscode'   => $statusCode,
            'message'      => 'Slot marked ' . ($slot->is_available ? 'available' : 'unavailable'),
            'is_available' => $slot->is_available,
            'slot'         => $slot->fresh(),
        ], $statusCode);
    }

    // ─────────────────────────────────────────────────────────────
    //  PUT /mentor/availability/{id}
    //  Update a single slot's time or availability
    // ─────────────────────────────────────────────────────────────
   
    public function updateSlot(Request $request, int $id): JsonResponse
    {
        $slot = MentorAvailability::where('id', $id)
            ->where('mentor_id', $request->user()->id)
            ->first();

        if (!$slot) {
            $statusCode = 404;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => 'Slot not found.'
            ], $statusCode);
        }

        $d = $request->validate([
            'day_of_week'  => 'sometimes|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time'   => 'sometimes|string|date_format:H:i',
            'end_time'     => 'sometimes|string|date_format:H:i',
            'is_available' => 'sometimes|boolean',
        ]);

        $slot->update($d);
        $statusCode = 200;
        return response()->json([
            'status'     => true,
            'statuscode' => $statusCode,
            'message'    => 'Slot updated successfully',
            'slot'       => $slot->fresh(),
        ], $statusCode);
    }

    // ─────────────────────────────────────────────────────────────
    //  DELETE /mentor/availability/{id}
    //  Delete a single slot
    // ─────────────────────────────────────────────────────────────
    
    public function destroy(Request $request, int $id): JsonResponse
    {
        $slot = MentorAvailability::where('id', $id)
            ->where('mentor_id', $request->user()->id)
            ->first();

        if (!$slot) {
            $statusCode = 404;
            return response()->json([
                'status'     => false,
                'statuscode' => $statusCode,
                'message'    => 'Slot not found.'
            ], $statusCode);
        }

        $slot->delete();
        $statusCode = 200;
        return response()->json([
            'status'     => true,
            'statuscode' => $statusCode,
            'message'    => 'Slot deleted successfully',
        ], $statusCode);
    }

    // ─────────────────────────────────────────────────────────────
    //  DELETE /mentor/availability
    //  Clear ALL slots for the authenticated mentor
    // ─────────────────────────────────────────────────────────────
    
    public function destroyAll(Request $request): JsonResponse
    {
        $deleted = MentorAvailability::where('mentor_id', $request->user()->id)->delete();
        $statusCode = 200;
        return response()->json([
            'status'     => true,
            'statuscode' => $statusCode,
            'message'    => 'All availability slots cleared',
            'deleted'    => $deleted,
        ], $statusCode);
    }
}