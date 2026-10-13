<?php

namespace App\Support;

use App\Models\ConsultationSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MentorBrowseQuery
{
    public static function fromRequest(Request $request, ?User $mentee = null): Builder
    {
        $search = trim((string) $request->input('search', $request->input('q', '')));
        $field = trim((string) $request->input('field', ''));

        return User::query()
            ->where('role', 'mentor')
            ->where('mentor_status', User::MENTOR_STATUS_APPROVED)
            ->where('is_active', true)
            ->when($mentee?->assigned_mentor_id && $request->boolean('exclude_assigned'), function (Builder $q) use ($mentee) {
                $q->where('id', '!=', $mentee->assigned_mentor_id);
            })
            ->when($search !== '', function (Builder $q) use ($search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('designation', 'like', '%'.$search.'%')
                        ->orWhere('company', 'like', '%'.$search.'%')
                        ->orWhere('field', 'like', '%'.$search.'%')
                        ->orWhere('bio', 'like', '%'.$search.'%')
                        ->orWhere('expertise', 'like', '%'.$search.'%');
                });
            })
            ->when($field !== '', fn (Builder $q) => $q->where('field', 'like', '%'.$field.'%'))
            ->when($request->filled('company'), fn (Builder $q) => $q->where('company', 'like', '%'.$request->input('company').'%'))
            ->when($request->filled('gender'), fn (Builder $q) => $q->where('gender', $request->input('gender')))
            ->when($request->filled('min_rating'), fn (Builder $q) => $q->where('rating', '>=', (float) $request->input('min_rating')))
            ->when(self::durationMinutes($request) !== null, function (Builder $q) use ($request) {
                self::whereSupportsDuration($q, (int) self::durationMinutes($request));
            });
    }

    public static function durationMinutes(Request $request): ?int
    {
        if (! $request->filled('duration')) {
            return null;
        }

        $minutes = (int) $request->input('duration');

        return in_array($minutes, ConsultationSession::BOOKING_DURATIONS, true) ? $minutes : null;
    }

    public static function whereSupportsDuration(Builder $query, int $minutes): Builder
    {
        return $query->where(function (Builder $q) use ($minutes) {
            $q->whereExists(function ($sub) use ($minutes) {
                $sub->selectRaw('1')
                    ->from('mentor_availabilities')
                    ->whereColumn('mentor_availabilities.mentor_id', 'users.id')
                    ->where('is_available', true)
                    ->whereRaw('TIMESTAMPDIFF(MINUTE, TIME(start_time), TIME(end_time)) = ?', [$minutes]);
            })->orWhereRaw(
                "CAST(JSON_UNQUOTE(JSON_EXTRACT(preferences, '$.session_length')) AS UNSIGNED) = ?",
                [$minutes]
            );
        });
    }

    public static function applySort(Builder $query, string $sort = 'best'): Builder
    {
        return match ($sort) {
            'rating'   => $query->orderByDesc('rating'),
            'sessions' => $query->orderByDesc('total_sessions'),
            'name'     => $query->orderBy('name'),
            'rate_asc' => $query->orderBy('rate_per_minute'),
            'rate_desc'=> $query->orderByDesc('rate_per_minute'),
            default    => $query->orderByDesc('rating')->orderByDesc('total_sessions'),
        };
    }
}
