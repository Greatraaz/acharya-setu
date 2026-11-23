<?php

namespace App\Support;

class SidebarNav
{
    /**
     * Whether the current route should mark a menu entry active.
     *
     * @param  string|array<int, string>  $patterns  Route names or wildcards (e.g. admin.quizzes.*)
     */
    public static function matches(string|array $patterns, ?string $current = null): bool
    {
        $current = $current ?? optional(request()->route())->getName();
        if (! is_string($current) || $current === '') {
            return false;
        }

        foreach ((array) $patterns as $pattern) {
            if (! is_string($pattern) || $pattern === '' || $pattern === '#') {
                continue;
            }

            if (str_ends_with($pattern, '*')) {
                $prefix = substr($pattern, 0, -1);
                if ($prefix !== '' && ($current === rtrim($prefix, '.') || str_starts_with($current, $prefix))) {
                    return true;
                }

                continue;
            }

            if ($current === $pattern) {
                return true;
            }

            // admin.quizzes.index → also match admin.quizzes.show / create / …
            if (preg_match('/\.(index|create|store|show|edit|update|destroy)$/', $pattern)) {
                $base = preg_replace('/\.(index|create|store|show|edit|update|destroy)$/', '', $pattern);
                if (is_string($base) && $base !== '' && str_starts_with($current, $base.'.')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Pick the best matching menu route among candidates (longest pattern wins).
     *
     * @param  array<string, string|array<int, string>>  $candidates  menuRoute => patterns
     */
    public static function bestMatch(array $candidates, ?string $current = null): ?string
    {
        $current = $current ?? optional(request()->route())->getName();
        if (! is_string($current) || $current === '') {
            return null;
        }

        $bestRoute = null;
        $bestScore = -1;

        foreach ($candidates as $menuRoute => $patterns) {
            foreach ((array) $patterns as $pattern) {
                if (! self::matches($pattern, $current)) {
                    continue;
                }

                $score = strlen(rtrim((string) $pattern, '*'));
                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestRoute = $menuRoute;
                }
            }
        }

        return $bestRoute;
    }

    /**
     * Default active patterns for a named menu route.
     *
     * @return array<int, string>
     */
    public static function defaultPatterns(string $menuRoute): array
    {
        return match ($menuRoute) {
            'admin.dashboard' => ['admin.dashboard'],
            'admin.mentors.index' => [
                'admin.mentors.index',
                'admin.mentors.trashed',
                'admin.mentors.export',
                'admin.mentors.toggle-status',
                'admin.mentors.create',
                'admin.mentor.store',
                'admin.mentor.edit',
                'admin.mentor.update',
            ],
            'admin.mentor-approvals.index' => [
                'admin.mentor-approvals.*',
                'admin.mentors.approvals',
                'admin.mentors.pending-changes',
                'admin.mentors.review',
                'admin.mentors.approve',
                'admin.mentors.reject',
                'admin.mentors.suspend',
                'admin.mentors.reinstate',
                'admin.mentors.approve-change',
                'admin.mentors.reject-change',
                'admin.mentors.destroy',
                'admin.mentors.restore',
            ],
            'admin.experts.index' => ['admin.experts.*'],
            'admin.mentees.index' => [
                'admin.mentees.*',
                'admin.mentee.store',
                'admin.mentee.edit',
                'admin.mentee.update',
                'admin.mentees.create',
            ],
            'admin.sessions.index' => ['admin.sessions.*', 'admin.session-invoices.*'],
            'admin.call-logs.index' => ['admin.call-logs.*'],
            'admin.wallet.index' => [
                'admin.wallet.index',
                'admin.wallet.export',
                'admin.wallet.users',
                'admin.wallet.customer.*',
                'admin.wallet.transfer',
                'admin.wallet.adjust',
            ],
            'admin.wallet.add-fund' => ['admin.wallet.add-fund', 'admin.wallet.add-fund.*'],
            'admin.transactions.index' => ['admin.transactions.*'],
            'admin.earnings.index' => ['admin.earnings.*'],
            'admin.withdrawals.index' => ['admin.withdrawals.*'],
            'admin.offers.index' => ['admin.offers.*'],
            'admin.plans.index' => ['admin.plans.*'],
            'admin.subscriptions.index' => ['admin.subscriptions.*'],
            'admin.subscription-cancels.index' => ['admin.subscription-cancels.*'],
            'admin.curriculum.templates' => ['admin.curriculum.templates*'],
            'admin.curriculum.streams' => [
                'admin.curriculum.streams*',
                'admin.curriculum.months*',
                'admin.curriculum.weeks*',
                'admin.curriculum.tasks*',
                'admin.curriculum.mcqs*',
                'admin.curriculum.enrollments*',
                'admin.curriculum.progress*',
                'admin.curriculum.submissions*',
                'admin.mentee.journey.*',
            ],
            'admin.mentee-tasks.index' => ['admin.mentee-tasks.*'],
            'admin.quizzes.index' => ['admin.quizzes.*'],
            'admin.assessments.index' => ['admin.assessments.*', 'admin.assessment-categories.*'],
            'admin.assessment-questions.index' => ['admin.assessment-questions.*'],
            'admin.mentor-videos.index' => ['admin.mentor-videos.*'],
            'admin.career-services.index' => ['admin.career-services.*'],
            'admin.mock-interviews.index' => ['admin.mock-interviews.*'],
            'admin.jobs.index' => ['admin.jobs.*'],
            'admin.community.index' => ['admin.community.*'],
            'admin.bulk-emails.create' => ['admin.bulk-emails.*'],
            'admin.roles.index' => ['admin.roles.*'],
            'admin.settings.index' => ['admin.settings.*'],
            'admin.logs.index' => ['admin.logs.*'],
            'admin.events-webinars.index' => ['admin.events-webinars.*', 'admin.insight-events.*'],
            default => [$menuRoute, preg_replace('/\.[^.]+$/', '.*', $menuRoute) ?: $menuRoute],
        };
    }
}
