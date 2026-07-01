<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\{User, MenteeEnrollment, StudentCurriculumProgress};
use Illuminate\Http\{Request, JsonResponse};

class MenteeController extends Controller
{
    // ─────────────────────────────────────────────
    //  GET /mentor/mentees
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $mentor = $request->user();

        $enrolledIds = MenteeEnrollment::where('mentor_id', $mentor->id)->pluck('mentee_id');
        $assignedIds = User::where('assigned_mentor_id', $mentor->id)
            ->where('role', 'mentee')
            ->pluck('id');

        $menteeIds = $enrolledIds->merge($assignedIds)->unique()->values();

        $mentees = User::whereIn('id', $menteeIds)
            ->where('role', 'mentee')
            ->with(['enrollments' => fn ($q) => $q->where('mentor_id', $mentor->id)->with('stream:id,name,slug,icon,color')])
            ->orderBy('name')
            ->get()
            ->map(fn (User $mentee) => $this->formatMentee($mentee, $mentor->id));

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'count'      => $mentees->count(),
            'mentees'    => $mentees,
        ]);
    }

    // ─────────────────────────────────────────────
    //  GET /mentor/mentees/{mentee}
    // ─────────────────────────────────────────────
    public function show(Request $request, int $mentee): JsonResponse
    {
        $mentor = $request->user();
        $menteeModel = $this->findMentorMentee($mentor->id, $mentee);

        $menteeModel->load([
            'enrollments' => fn ($q) => $q->where('mentor_id', $mentor->id)->with('stream'),
            'menteeSessions' => fn ($q) => $q->where('mentor_id', $mentor->id)->latest('scheduled_at')->limit(10),
        ]);

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'mentee'     => $this->formatMentee($menteeModel, $mentor->id, true),
        ]);
    }

    private function findMentorMentee(int $mentorId, int $menteeId): User
    {
        $isLinked = MenteeEnrollment::where('mentor_id', $mentorId)->where('mentee_id', $menteeId)->exists()
            || User::where('id', $menteeId)->where('role', 'mentee')->where('assigned_mentor_id', $mentorId)->exists();

        if (!$isLinked) {
            abort(404, 'Mentee not found for this mentor.');
        }

        return User::where('id', $menteeId)->where('role', 'mentee')->firstOrFail();
    }

    private function formatMentee(User $mentee, int $mentorId, bool $detailed = false): array
    {
        $enrollments = $mentee->enrollments
            ->where('mentor_id', $mentorId)
            ->values()
            ->map(function (MenteeEnrollment $enrollment) use ($mentee) {
                return [
                    'id'                => $enrollment->id,
                    'stream_id'         => $enrollment->stream_id,
                    'stream'            => $enrollment->stream,
                    'status'            => $enrollment->status,
                    'start_date'        => $enrollment->start_date?->toDateString(),
                    'expected_end_date' => $enrollment->expected_end_date?->toDateString(),
                    'current_month'     => $enrollment->current_month,
                    'current_week'      => $enrollment->current_week,
                    'mentor_notes'      => $enrollment->mentor_notes,
                    'progress'          => StudentCurriculumProgress::getOverallProgress($mentee->id, $enrollment->stream_id),
                ];
            });

        $data = [
            'id'                   => $mentee->id,
            'name'                 => $mentee->name,
            'email'                => $mentee->email,
            'phone'                => $mentee->phone,
            'gender'               => $mentee->gender,
            'avatar_url'           => $mentee->avatar_url,
            'field'                => $mentee->field,
            'college'              => $mentee->college,
            'year'                 => $mentee->year,
            'location'             => $mentee->location,
            'education_stream'     => $mentee->education_stream,
            'career_goals'         => $mentee->career_goals ?? [],
            'preferences'          => $mentee->preferencesForResponse(),
            'subscription_plan'    => $mentee->subscription_plan,
            'onboarding_step'      => $mentee->onboarding_step,
            'onboarding_completed' => (bool) $mentee->onboarding_completed,
            'is_active'            => (bool) $mentee->is_active,
            'enrollments'          => $enrollments,
            'enrollments_count'    => $enrollments->count(),
        ];

        if ($detailed) {
            $data['bio'] = $mentee->bio;
            $data['strengths'] = $mentee->strengths ?? [];
            $data['total_sessions'] = $mentee->menteeSessions()->where('mentor_id', $mentorId)->count();
            $data['completed_sessions'] = $mentee->menteeSessions()->where('mentor_id', $mentorId)->where('status', 'completed')->count();
            $data['recent_sessions'] = $mentee->menteeSessions->map(fn ($s) => [
                'id'           => $s->id,
                'scheduled_at' => $s->scheduled_at,
                'status'       => $s->status,
                'duration'     => $s->duration ?? null,
            ]);
        }

        return $data;
    }
}
