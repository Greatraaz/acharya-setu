<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class MentorMatcherService
{
    /**
     * Find the best-matching approved mentor and assign them to the mentee.
     *
     * @return array{assigned: bool, mentor: ?User, match_score: int, reason: ?string}
     */
    public function assignBestMentor(User $mentee): array
    {
        if ($mentee->assigned_mentor_id) {
            return [
                'assigned'    => false,
                'mentor'      => $mentee->assignedMentor,
                'match_score' => 0,
                'reason'      => 'already_assigned',
            ];
        }

        $mentors = User::mentors()
            ->active()
            ->approved()
            ->where('onboarding_completed', true)
            ->withCount('assignedMentees')
            ->get();

        if ($mentors->isEmpty()) {
            return [
                'assigned'    => false,
                'mentor'      => null,
                'match_score' => 0,
                'reason'      => 'no_mentors_available',
            ];
        }

        $ranked = $this->rankMentors($mentee, $mentors);
        $best   = $ranked->first();

        $mentee->update(['assigned_mentor_id' => $best['mentor']->id]);

        return [
            'assigned'    => true,
            'mentor'      => $best['mentor']->fresh(),
            'match_score' => $best['score'],
            'reason'      => null,
        ];
    }

    /**
     * @param  Collection<int, User>  $mentors
     * @return Collection<int, array{mentor: User, score: int}>
     */
    public function rankMentors(User $mentee, Collection $mentors): Collection
    {
        return $mentors
            ->map(fn (User $mentor) => [
                'mentor' => $mentor,
                'score'  => $this->scoreMentor($mentee, $mentor),
            ])
            ->sort(function (array $a, array $b) {
                if ($a['score'] !== $b['score']) {
                    return $b['score'] <=> $a['score'];
                }

                $ratingCompare = ((float) $b['mentor']->rating) <=> ((float) $a['mentor']->rating);
                if ($ratingCompare !== 0) {
                    return $ratingCompare;
                }

                return ($a['mentor']->assigned_mentees_count ?? 0) <=> ($b['mentor']->assigned_mentees_count ?? 0);
            })
            ->values();
    }

    public function scoreMentor(User $mentee, User $mentor): int
    {
        $score       = 0;
        $preferences = is_array($mentee->preferences) ? $mentee->preferences : [];
        $careerGoals = is_array($mentee->career_goals) ? $mentee->career_goals : [];
        $expertise   = is_array($mentor->expertise) ? $mentor->expertise : [];
        $strengths   = is_array($mentor->strengths) ? $mentor->strengths : [];
        $mentorPrefs = is_array($mentor->preferences) ? $mentor->preferences : [];

        if ($this->stringsMatch($mentee->education_stream ?? '', $mentor->education_stream ?? '')) {
            $score += 30;
        }

        if ($this->stringsMatch($mentee->field ?? '', $mentor->field ?? '')) {
            $score += 25;
        }

        foreach ($careerGoals as $goal) {
            if (! is_string($goal) || trim($goal) === '') {
                continue;
            }

            foreach ($expertise as $skill) {
                if (is_string($skill) && $this->stringsMatch($goal, $skill)) {
                    $score += 15;
                    break;
                }
            }

            if ($this->textContains($mentor->field ?? '', $goal) || $this->textContains($mentor->bio ?? '', $goal)) {
                $score += 5;
            }
        }

        $score += $this->scoreBudgetFit($preferences['monthly_budget'] ?? null, (float) $mentor->rate_per_minute);

        $preferredLanguage = $preferences['preferred_language'] ?? null;
        if ($preferredLanguage) {
            $languageHaystack = implode(' ', array_filter([
                $mentor->bio,
                $mentor->field,
                implode(' ', $expertise),
                implode(' ', $strengths),
            ]));

            if ($this->textContains($languageHaystack, $preferredLanguage)) {
                $score += 10;
            }
        }

        $mentoringFormat = $preferences['mentoring_format'] ?? null;
        if ($mentoringFormat && isset($mentorPrefs['mentoring_format'])) {
            if ($this->stringsMatch($mentoringFormat, (string) $mentorPrefs['mentoring_format'])) {
                $score += 10;
            }
        }

        $score += (int) min(10, round(((float) $mentor->rating) * 2));

        // Prefer mentors with fewer assigned mentees (up to 5-point bonus).
        $load = (int) ($mentor->assigned_mentees_count ?? 0);
        $score += max(0, 5 - min($load, 5));

        return $score;
    }

    private function scoreBudgetFit(?string $monthlyBudget, float $ratePerMinute): int
    {
        $maxBudget = $this->parseBudgetMax($monthlyBudget);
        if ($maxBudget === null) {
            return 0;
        }

        // Assume ~4 hours of mentoring per month.
        $affordableRate = $maxBudget / 240;

        if ($ratePerMinute <= $affordableRate) {
            return 20;
        }

        if ($ratePerMinute <= $affordableRate * 1.25) {
            return 10;
        }

        return 0;
    }

    private function parseBudgetMax(?string $budget): ?float
    {
        if ($budget === null || trim($budget) === '') {
            return null;
        }

        $budget = strtolower(trim($budget));

        if (preg_match('/(\d+)\s*[-–]\s*(\d+)/', $budget, $matches)) {
            return (float) $matches[2];
        }

        if (preg_match('/under[_\s-]?(\d+)/', $budget, $matches)) {
            return (float) $matches[1];
        }

        if (preg_match('/(\d+)\+/', $budget, $matches)) {
            return (float) $matches[1] * 2;
        }

        if (preg_match('/(\d+)/', $budget, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }

    private function stringsMatch(string $a, string $b): bool
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        if ($a === '' || $b === '') {
            return false;
        }

        return $a === $b || str_contains($a, $b) || str_contains($b, $a);
    }

    private function textContains(string $haystack, string $needle): bool
    {
        $haystack = $this->normalize($haystack);
        $needle   = $this->normalize($needle);

        if ($haystack === '' || $needle === '') {
            return false;
        }

        return str_contains($haystack, $needle);
    }
}
