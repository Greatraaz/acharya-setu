<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\{MentorRequest, User};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\DB;

class MentorRequestController extends Controller
{
    // GET /mentor/mentor-requests
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', MentorRequest::STATUS_PENDING);

        $requests = MentorRequest::where('mentor_id', $request->user()->id)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->with('mentee:id,name,email,avatar_url,field,college,year,education_stream')
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

    // POST /mentor/mentor-requests/{id}/accept
    public function accept(Request $request, int $id): JsonResponse
    {
        $mentorRequest = MentorRequest::where('mentor_id', $request->user()->id)
            ->findOrFail($id);

        if (! $mentorRequest->isPending()) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'This request is no longer pending.',
            ], 422);
        }

        $mentee = User::where('id', $mentorRequest->mentee_id)
            ->where('role', 'mentee')
            ->firstOrFail();

        DB::transaction(function () use ($mentorRequest, $mentee, $request) {
            $mentorRequest->update([
                'status'       => MentorRequest::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            // Assign (or switch) mentee to this mentor
            $mentee->update(['assigned_mentor_id' => $request->user()->id]);

            // Auto-reject other pending requests from this mentee
            MentorRequest::where('mentee_id', $mentee->id)
                ->where('id', '!=', $mentorRequest->id)
                ->where('status', MentorRequest::STATUS_PENDING)
                ->update([
                    'status'        => MentorRequest::STATUS_REJECTED,
                    'mentor_note'   => 'Mentee accepted another mentor.',
                    'responded_at'  => now(),
                ]);
        });

        $mentorRequest->load('mentee:id,name,email,avatar_url,field,college,year');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Mentee request accepted.',
            'request'    => $this->formatRequest($mentorRequest),
        ]);
    }

    // POST /mentor/mentor-requests/{id}/reject
    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'mentor_note' => 'nullable|string|max:1000',
        ]);

        $mentorRequest = MentorRequest::where('mentor_id', $request->user()->id)
            ->findOrFail($id);

        if (! $mentorRequest->isPending()) {
            return response()->json([
                'status'     => false,
                'statuscode' => 422,
                'message'    => 'This request is no longer pending.',
            ], 422);
        }

        $mentorRequest->update([
            'status'       => MentorRequest::STATUS_REJECTED,
            'mentor_note'  => $data['mentor_note'] ?? null,
            'responded_at' => now(),
        ]);

        $mentorRequest->load('mentee:id,name,email,avatar_url,field,college,year');

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Mentee request rejected.',
            'request'    => $this->formatRequest($mentorRequest),
        ]);
    }

    private function formatRequest(MentorRequest $req): array
    {
        return [
            'id'           => $req->id,
            'mentee_id'    => $req->mentee_id,
            'message'      => $req->message,
            'status'       => $req->status,
            'mentor_note'  => $req->mentor_note,
            'responded_at' => $req->responded_at,
            'mentee'       => $req->mentee,
            'created_at'   => $req->created_at,
            'updated_at'   => $req->updated_at,
        ];
    }
}
