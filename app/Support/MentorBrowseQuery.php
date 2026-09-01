<?php

namespace App\Support;

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
            ->when($request->filled('min_rating'), fn (Builder $q) => $q->where('rating', '>=', (float) $request->input('min_rating')));
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
