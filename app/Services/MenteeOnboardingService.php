<?php

namespace App\Services;

use App\Models\EducationStream;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MenteeOnboardingService
{
    public const TOTAL_STEPS = 4;

    public function catalogStreams(): Collection
    {
        try {
            return EducationStream::query()
                ->where('is_active', true)
                ->whereNull('mentee_id')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->unique()
                ->values();
        } catch (\Throwable) {
            return collect();
        }
    }

    public function menteeTracks(int $menteeId): array
    {
        return EducationStream::query()
            ->where('mentee_id', $menteeId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('name')
            ->all();
    }

    public function syncMenteeTracks(int $menteeId, array $trackNames): void
    {
        $tracks = collect($trackNames)
            ->map(fn ($track) => trim((string) $track))
            ->filter()
            ->unique(fn ($track) => Str::lower($track))
            ->values();

        if ($tracks->isEmpty()) {
            return;
        }

        $selectedSlugs = $tracks
            ->map(fn ($name) => Str::slug($name))
            ->filter()
            ->values();

        EducationStream::where('mentee_id', $menteeId)
            ->whereNull('mentor_id')
            ->whereNotIn('slug', $selectedSlugs)
            ->update(['is_active' => false]);

        foreach ($tracks as $index => $name) {
            $slug = Str::slug($name);
            if ($slug === '') {
                continue;
            }

            EducationStream::updateOrCreate(
                ['mentee_id' => $menteeId, 'slug' => $slug],
                [
                    'name'       => $name,
                    'mentor_id'  => null,
                    'is_active'  => true,
                    'sort_order' => $index,
                ]
            );
        }
    }

    public function mergePreferences(User $user, array $data): array
    {
        $sessionModes = array_values(array_filter($data['session_modes'] ?? []));
        $mentoringFormat = $data['mentoring_format'] ?? $this->mentoringFormatFromSessionModes($sessionModes);

        return array_merge($user->preferences ?? [], array_filter([
            'weekly_time_commitment' => $data['weekly_time_commitment'] ?? null,
            'monthly_budget'         => $data['monthly_budget'] ?? null,
            'preferred_language'     => $data['preferred_language'] ?? null,
            'mentoring_format'       => $mentoringFormat,
            'session_modes'          => $sessionModes ?: null,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    private function mentoringFormatFromSessionModes(array $sessionModes): ?string
    {
        $first = $sessionModes[0] ?? null;

        return match ($first) {
            'video', 'audio', 'chat' => $first,
            'in_person'              => 'hybrid',
            default                  => null,
        };
    }

    public function missingCompletionFields(User $user): array
    {
        $missing = [];

        if (empty($user->name)) {
            $missing[] = 'name';
        }
        if (empty($user->location)) {
            $missing[] = 'address';
        }
        if (empty($user->education_stream)) {
            $missing[] = 'education_stream';
        }
        if (empty($user->preferences['weekly_time_commitment'] ?? null)) {
            $missing[] = 'weekly_time_commitment';
        }
        if (empty($user->preferences['preferred_language'] ?? null)) {
            $missing[] = 'preferred_language';
        }
        if (empty($user->preferences['mentoring_format'] ?? null)) {
            $missing[] = 'mentoring_format';
        }

        $hasTracks = EducationStream::where('mentee_id', $user->id)
            ->where('is_active', true)
            ->exists();

        if (! $hasTracks) {
            $missing[] = 'tracks';
        }

        return $missing;
    }

    public function isComplete(User $user): bool
    {
        return empty($this->missingCompletionFields($user));
    }

    /**
     * @return array{completed: bool, assigned: bool, mentor: ?User, match_score: int, missing: array<int, string>}
     */
    public function complete(User $user, bool $autoAssignMentor = true): array
    {
        $missing = $this->missingCompletionFields($user);

        if (! empty($missing)) {
            return [
                'completed'   => false,
                'assigned'    => false,
                'mentor'      => $user->assignedMentor,
                'match_score' => 0,
                'missing'     => $missing,
            ];
        }

        $user->update([
            'onboarding_completed' => true,
            'onboarding_step'      => self::TOTAL_STEPS,
        ]);

        $user->refresh();

        $assignment = ['assigned' => false, 'mentor' => $user->assignedMentor, 'match_score' => 0];

        if ($autoAssignMentor && ! $user->assigned_mentor_id) {
            $assignment = app(MentorMatcherService::class)->assignBestMentor($user);
        }

        return [
            'completed'   => true,
            'assigned'    => $assignment['assigned'],
            'mentor'      => $assignment['mentor'],
            'match_score' => $assignment['match_score'],
            'missing'     => [],
        ];
    }

    public function adminValidationRules(bool $isUpdate = false): array
    {
        $rules = [
            'name'                   => 'required|string|max:100',
            'email'                  => 'required|email',
            'phone'                  => 'nullable|string|max:20',
            'gender'                 => 'nullable|in:male,female,other,prefer_not_to_say',
            'address'                => 'required|string|max:200',
            'avatar'                 => 'nullable|image|max:2048',
            'education_stream'       => 'required|string|max:100',
            'field'                  => 'nullable|string|max:100',
            'college'                => 'nullable|string|max:200',
            'year'                   => 'nullable|string|max:20',
            'tracks'                 => 'required|array|min:1',
            'tracks.*'               => 'string|max:100',
            'weekly_time_commitment' => 'required|string|max:100',
            'monthly_budget'         => 'nullable|string|max:100',
            'preferred_language'     => 'required|string|max:100',
            'session_modes'          => 'required|array|min:1',
            'session_modes.*'        => 'in:video,audio,chat,in_person',
            'assigned_mentor_id'     => 'nullable|exists:users,id',
            'subscription_plan'      => 'nullable|in:free,basic,pro,enterprise',
            'auto_assign_mentor'     => 'nullable|boolean',
            'is_active'              => 'nullable|boolean',
        ];

        if ($isUpdate) {
            $rules['new_password'] = ['nullable', \Illuminate\Validation\Rules\Password::min(8)];
        } else {
            $rules['password'] = ['required', \Illuminate\Validation\Rules\Password::min(8)];
        }

        return $rules;
    }
}
