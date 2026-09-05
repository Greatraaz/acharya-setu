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
        $user = User::find($menteeId);
        $goals = $user?->career_goals;

        if (is_array($goals) && $goals !== []) {
            return array_values(array_filter(array_map(
                fn ($g) => trim((string) $g),
                $goals
            )));
        }

        return [];
    }

    /**
     * Normalize stored preference values so admin/frontend selects match legacy DB strings.
     */
    public function preferencesForForm(array $preferences): array
    {
        if (! empty($preferences['weekly_time_commitment'])) {
            $preferences['weekly_time_commitment'] = $this->normalizeWeeklyTimeCommitment(
                (string) $preferences['weekly_time_commitment']
            );
        }

        if (! empty($preferences['monthly_budget'])) {
            $preferences['monthly_budget'] = $this->normalizeMonthlyBudget(
                (string) $preferences['monthly_budget']
            );
        }

        $modes = $preferences['session_modes'] ?? null;
        if (! is_array($modes) || $modes === []) {
            $modes = $this->sessionModesFromMentoringFormat($preferences['mentoring_format'] ?? null);
        } else {
            $modes = array_values(array_intersect(
                $modes,
                ['video', 'audio', 'chat', 'in_person']
            ));
            if ($modes === []) {
                $modes = $this->sessionModesFromMentoringFormat($preferences['mentoring_format'] ?? null);
            }
        }
        $preferences['session_modes'] = $modes;

        return $preferences;
    }

    public function normalizeWeeklyTimeCommitment(string $value): string
    {
        $v = mb_strtolower(trim($value));
        $v = str_replace(['–', '—', '−'], '-', $v);
        $v = preg_replace('/\s+/', ' ', $v) ?? $v;

        foreach ([
            '1-3 hours' => ['1-3 hours', '1-3 hours per week', '1-3 hours/week'],
            '3-5 hours' => ['3-5 hours', '3-5 hours per week', '3-5 hours/week'],
            '5-10 hours' => ['5-10 hours', '5-10 hours per week', '5-10 hours/week'],
            '10+ hours' => ['10+ hours', '10+ hours per week', '10+ hours/week'],
        ] as $canonical => $aliases) {
            if (in_array($v, $aliases, true)) {
                return $canonical;
            }
        }

        if (preg_match('/\b1\s*-\s*3\b/', $v)) {
            return '1-3 hours';
        }
        if (preg_match('/\b3\s*-\s*5\b/', $v)) {
            return '3-5 hours';
        }
        if (preg_match('/\b5\s*-\s*10\b/', $v)) {
            return '5-10 hours';
        }
        if (preg_match('/\b10\s*\+/', $v)) {
            return '10+ hours';
        }

        return $value;
    }

    public function normalizeMonthlyBudget(string $value): string
    {
        $v = mb_strtolower(trim($value));
        $v = str_replace(['–', '—', '−'], '-', $v);
        $v = preg_replace('/\s+/', ' ', $v) ?? $v;
        $v = str_replace([',', '₹', 'rs.', 'rs'], '', $v);

        if (str_contains($v, 'under') || preg_match('/^<?\s*500$/', $v)) {
            return 'under_500';
        }
        if (preg_match('/500\s*-\s*1000/', $v) || $v === '500-1000') {
            return '500-1000';
        }
        if (preg_match('/1000\s*-\s*2500/', $v) || preg_match('/1000\s*-\s*3000/', $v) || $v === '1000-2500') {
            return '1000-2500';
        }
        if (str_contains($v, '2500+') || str_contains($v, '2500 +') || str_contains($v, '3000+')) {
            return '2500+';
        }

        return in_array($value, ['under_500', '500-1000', '1000-2500', '2500+'], true)
            ? $value
            : $value;
    }

    /**
     * @return list<string>
     */
    public function sessionModesFromMentoringFormat(?string $format): array
    {
        return match ($format) {
            'video' => ['video'],
            'audio' => ['audio'],
            'chat' => ['chat'],
            'hybrid', 'in_person' => ['in_person'],
            'one_on_one', 'group' => ['video'],
            default => [],
        };
    }

    /**
     * Persist career interests only. Does NOT create curriculum tracks —
     * mentors/admins must create EducationStream rows manually for each mentee.
     */
    public function assignCatalogStream(User $user, ?string $streamName): ?EducationStream
    {
        $streamName = trim((string) $streamName);
        if ($streamName === '') {
            return null;
        }

        // Store the preferred learning area on the user profile only.
        if ($user->education_stream !== $streamName) {
            $user->update(['education_stream' => $streamName]);
        }

        return null;
    }

    /**
     * Save mentee career goals / interests. Never creates curriculum tracks.
     */
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

        User::where('id', $menteeId)->update([
            'career_goals' => $tracks->all(),
        ]);
    }

    /** @deprecated Slug helper retained for any legacy callers. */
    private function menteeTrackSlug(string $name, int $menteeId): string
    {
        $base = Str::slug($name) ?: 'track';

        return $base . '-mentee-' . $menteeId;
    }

    public function mergePreferences(User $user, array $data): array
    {
        $sessionModes = array_values(array_filter($data['session_modes'] ?? []));
        $mentoringFormat = $data['mentoring_format']
            ?? $this->mentoringFormatFromSessionModes($sessionModes)
            ?? ($user->preferences['mentoring_format'] ?? null);

        $weekly = $data['weekly_time_commitment'] ?? null;
        if (is_string($weekly) && $weekly !== '') {
            $weekly = $this->normalizeWeeklyTimeCommitment($weekly);
        }

        $budget = $data['monthly_budget'] ?? null;
        if (is_string($budget) && $budget !== '') {
            $budget = $this->normalizeMonthlyBudget($budget);
        }

        return array_merge($user->preferences ?? [], array_filter([
            'weekly_time_commitment' => $weekly,
            'monthly_budget'         => $budget,
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

        $hasTracks = ! empty($user->career_goals);

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
