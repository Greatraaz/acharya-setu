<?php

namespace App\Support;

use App\Models\ConsultationSession;
use App\Models\EducationStream;
use App\Models\MenteeEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MentorMenteesQuery
{
    public static function for(int|string $mentorId): Builder
    {
        $sessionIds = ConsultationSession::where('mentor_id', $mentorId)->pluck('mentee_id');
        $assignedIds = User::where('assigned_mentor_id', $mentorId)->where('role', 'mentee')->pluck('id');
        $enrolledIds = MenteeEnrollment::where('mentor_id', $mentorId)->pluck('mentee_id');
        $trackIds = EducationStream::where('mentor_id', $mentorId)->pluck('mentee_id');

        $ids = $sessionIds
            ->merge($assignedIds)
            ->merge($enrolledIds)
            ->merge($trackIds)
            ->unique()
            ->filter()
            ->values();

        return User::query()
            ->where('role', 'mentee')
            ->whereIn('id', $ids)
            ->orderBy('name');
    }
}
