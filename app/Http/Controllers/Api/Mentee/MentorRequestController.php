<?php

namespace App\Http\Controllers\Api\Mentee;

use App\Http\Controllers\Controller;
use App\Models\{MentorRequest, User};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\Rule;

class MentorRequestController extends Controller
{
    // GET /mentee/mentor-requests
    public function index(Request $request): JsonResponse
    {
        $requests = MentorRequest::where('mentee_id', $request->user()->id)
            ->with('mentor:id,name,email,avatar_url,field,company,designation,rating')
            ->latest()
            ->get()
            ->map(fn (MentorRequest $req) => $this->formatRequest($req));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'requests'   => $requests,
            'total'      => $requests->count(),
        ]);
    }

    // POST /mentee/mentor-requests
    public function store(Request $request): JsonResponse
    {
        $mentee = $request->user();

        if ($mentee->assigned_mentor_id) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'You already have an assigned mentor.',
            ], 422);
        }

        $data = $request->validate([
            'mentor_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'mentor')->where('is_active', true)),
            ],
            'message' => 'nullable|string|max:1000',
        ]);

        $mentor = User::where('id', $data['mentor_id'])
            ->where('role', 'mentor')
            ->where('is_active', true)
            ->first();

        if (! $mentor || ! $mentor->isApproved()) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'This mentor is not available for requests.',
            ], 422);
        }

        $existingPending = MentorRequest::where('mentee_id', $mentee->id)
            ->where('mentor_id', $data['mentor_id'])
            ->where('status', MentorRequest::STATUS_PENDING)
            ->exists();

        if ($existingPending) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'You already have a pending request to this mentor.',
            ], 422);
        }

        $mentorRequest = MentorRequest::create([
            'mentee_id' => $mentee->id,
            'mentor_id' => $data['mentor_id'],
            'message'   => $data['message'] ?? null,
            'status'    => MentorRequest::STATUS_PENDING,
        ]);

        $mentorRequest->load('mentor:id,name,email,avatar_url,field,company,designation,rating');

        return response()->json([
            'status'     => true,
            'statuscode' => 201,
            'message'    => 'Mentor request sent successfully.',
            'request'    => $this->formatRequest($mentorRequest),
        ], 201);
    }

    // DELETE /mentee/mentor-requests/{id}
    public function destroy(Request $request, int $id): JsonResponse
    {
        $mentorRequest = MentorRequest::where('mentee_id', $request->user()->id)
            ->findOrFail($id);

        if (! $mentorRequest->isPending()) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'Only pending requests can be cancelled.',
            ], 422);
        }

        $mentorRequest->update([
            'status'        => MentorRequest::STATUS_CANCELLED,
            'responded_at'  => now(),
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Mentor request cancelled.',
        ]);
    }

    private function formatRequest(MentorRequest $req): array
    {
        return [
            'id'           => $req->id,
            'mentor_id'    => $req->mentor_id,
            'message'      => $req->message,
            'status'       => $req->status,
            'mentor_note'  => $req->mentor_note,
            'responded_at' => $req->responded_at,
            'mentor'       => $req->mentor,
            'created_at'   => $req->created_at,
            'updated_at'   => $req->updated_at,
        ];
    }
}
