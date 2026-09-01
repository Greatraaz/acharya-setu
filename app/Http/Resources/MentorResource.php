<?php

namespace App\Http\Resources;

use App\Models\User;

class MentorResource
{
    /**
     * Public mentor profile fields for list/detail API responses.
     * Excludes secrets: password, bank_details, wallet, deleted credentials.
     *
     * @param  array<string, mixed>  $extra
     */
    public static function toArray(User $mentor, array $extra = []): array
    {
        $name = (string) $mentor->name;

        return array_merge([
            'id'                   => $mentor->id,
            'name'                   => $mentor->name,
            'slug'                   => $mentor->slug,
            'email'                  => $mentor->email,
            'bio'                    => $mentor->bio,
            'expertise'              => $mentor->expertise ?? [],
            'field'                  => $mentor->field,
            'college'                => $mentor->college,
            'year'                   => $mentor->year,
            'gender'                 => $mentor->gender,
            'rating'                 => (float) ($mentor->rating ?? 0),
            'total_sessions'         => (int) ($mentor->total_sessions ?? 0),
            'avatar_url'             => $mentor->avatar_url,
            'phone'                  => $mentor->phone,
            'location'               => $mentor->location,
            'linkedin'               => $mentor->linkedin,
            'company'                => $mentor->company,
            'designation'            => $mentor->designation,
            'experience_years'       => (int) ($mentor->experience_years ?? 0),
            'rate_per_minute'        => (float) ($mentor->rate_per_minute ?? 0),
            'education_stream'       => $mentor->education_stream,
            'career_goals'           => $mentor->career_goals ?? [],
            'strengths'              => $mentor->strengths ?? [],
            'preferences'            => $mentor->preferencesForResponse(),
            'mentor_status'          => $mentor->mentor_status,
            'is_active'              => (bool) $mentor->is_active,
            'onboarding_completed'   => (bool) $mentor->onboarding_completed,
            'has_pending_changes'    => (bool) $mentor->has_pending_changes,
            'approved_at'            => $mentor->approved_at?->toIso8601String(),
            'profile_url'            => $mentor->profile_url,
            'initials'               => self::initials($name),
            'created_at'             => $mentor->created_at?->toIso8601String(),
            'updated_at'             => $mentor->updated_at?->toIso8601String(),
        ], $extra);
    }

    public static function initials(string $name): string
    {
        return strtoupper(implode('', array_map(
            fn ($part) => $part[0] ?? '',
            array_slice(explode(' ', trim($name)), 0, 2)
        )));
    }

    /** @return list<string> */
    public static function distinctFields(): array
    {
        return User::query()
            ->where('role', 'mentor')
            ->where('mentor_status', User::MENTOR_STATUS_APPROVED)
            ->where('is_active', true)
            ->whereNotNull('field')
            ->where('field', '!=', '')
            ->distinct()
            ->orderBy('field')
            ->pluck('field')
            ->values()
            ->all();
    }
}
