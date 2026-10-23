<?php

namespace App\Support;

use App\Models\ConsultationSession;
use App\Models\EducationStream;
use App\Models\MenteeEnrollment;
use App\Models\MentorRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MentorMenteeJoinedAt
{
    /**
     * Attach mentor_joined_at (the date this mentor became the mentee's mentor).
     *
     * @param  Collection<int, User>  $mentees
     * @return Collection<int, User>
     */
    public static function attach(Collection $mentees, int $mentorId): Collection
    {
        $ids = $mentees->pluck('id')->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return $mentees;
        }

        $requestDates = MentorRequest::query()
            ->where('mentor_id', $mentorId)
            ->whereIn('mentee_id', $ids)
            ->where('status', MentorRequest::STATUS_ACCEPTED)
            ->whereNotNull('responded_at')
            ->selectRaw('mentee_id, MAX(responded_at) as joined_at')
            ->groupBy('mentee_id')
            ->pluck('joined_at', 'mentee_id');

        $enrollmentDates = MenteeEnrollment::query()
            ->where('mentor_id', $mentorId)
            ->whereIn('mentee_id', $ids)
            ->selectRaw('mentee_id, MIN(COALESCE(start_date, created_at)) as joined_at')
            ->groupBy('mentee_id')
            ->pluck('joined_at', 'mentee_id');

        $trackDates = EducationStream::query()
            ->where('mentor_id', $mentorId)
            ->whereIn('mentee_id', $ids)
            ->selectRaw('mentee_id, MIN(created_at) as joined_at')
            ->groupBy('mentee_id')
            ->pluck('joined_at', 'mentee_id');

        $sessionDates = ConsultationSession::query()
            ->where('mentor_id', $mentorId)
            ->whereIn('mentee_id', $ids)
            ->selectRaw('mentee_id, MIN(created_at) as joined_at')
            ->groupBy('mentee_id')
            ->pluck('joined_at', 'mentee_id');

        return $mentees->each(function (User $mentee) use ($mentorId, $requestDates, $enrollmentDates, $trackDates, $sessionDates) {
            $mentee->setAttribute('mentor_joined_at', static::resolve(
                $mentee,
                $mentorId,
                $requestDates->get($mentee->id),
                $enrollmentDates->get($mentee->id),
                $trackDates->get($mentee->id),
                $sessionDates->get($mentee->id),
            ));
        });
    }

    public static function for(User $mentee, int $mentorId): ?Carbon
    {
        static::attach(collect([$mentee]), $mentorId);

        $joinedAt = $mentee->getAttribute('mentor_joined_at');

        return $joinedAt instanceof Carbon ? $joinedAt : null;
    }

    private static function resolve(User $mentee, int $mentorId, mixed ...$fallbacks): ?Carbon
    {
        if ((int) $mentee->assigned_mentor_id === $mentorId && $mentee->assigned_mentor_at) {
            return $mentee->assigned_mentor_at instanceof Carbon
                ? $mentee->assigned_mentor_at
                : Carbon::parse($mentee->assigned_mentor_at);
        }

        foreach ($fallbacks as $value) {
            if (filled($value)) {
                return $value instanceof Carbon ? $value : Carbon::parse($value);
            }
        }

        if ((int) $mentee->assigned_mentor_id === $mentorId && $mentee->created_at) {
            return $mentee->created_at instanceof Carbon
                ? $mentee->created_at
                : Carbon::parse($mentee->created_at);
        }

        return null;
    }
}
