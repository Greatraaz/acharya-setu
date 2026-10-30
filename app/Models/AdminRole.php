<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminRole extends Model
{
    protected $fillable = ['name', 'is_super', 'modules'];

    protected $casts = [
        'is_super' => 'boolean',
        'modules' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'admin_role_id');
    }

    public static function catalog(): array
    {
        return [
            'mentors' => ['label' => 'Mentors & approvals', 'group' => 'People', 'match' => ['admin.mentors.', 'admin.mentor.', 'admin.mentor-approvals.']],
            'mentees' => ['label' => 'Mentees', 'group' => 'People', 'match' => ['admin.mentees.', 'admin.mentee.']],
            'sessions' => ['label' => 'Sessions', 'group' => 'Sessions', 'match' => ['admin.sessions.', 'admin.session-invoices.']],
            'call-logs' => ['label' => 'Call records', 'group' => 'Sessions', 'match' => ['admin.call-logs.']],
            'wallet' => ['label' => 'Wallet & add fund', 'group' => 'Finance', 'match' => ['admin.wallet.']],
            'transactions' => ['label' => 'Transactions', 'group' => 'Finance', 'match' => ['admin.transactions.']],
            'earnings' => ['label' => 'Mentor earnings', 'group' => 'Finance', 'match' => ['admin.earnings.']],
            'withdrawals' => ['label' => 'Withdrawals', 'group' => 'Finance', 'match' => ['admin.withdrawals.']],
            'offers' => ['label' => 'Offers', 'group' => 'Finance', 'match' => ['admin.offers.']],
            'plans' => ['label' => 'Premium plans', 'group' => 'Finance', 'match' => ['admin.plans.']],
            'subscriptions' => ['label' => 'Subscriptions & invoices', 'group' => 'Finance', 'match' => ['admin.subscriptions.', 'admin.subscription-cancels.', 'admin.invoices.']],
            'curriculum' => ['label' => 'Curriculum', 'group' => 'Learning', 'match' => ['admin.curriculum.']],
            'mentee-tasks' => ['label' => 'Mentee tasks', 'group' => 'Learning', 'match' => ['admin.mentee-tasks.']],
            'quizzes' => ['label' => 'Quizzes & MCQs', 'group' => 'Learning', 'match' => ['admin.quizzes.']],
            'assessments' => ['label' => 'Assessments', 'group' => 'Learning', 'match' => ['admin.assessments.', 'admin.assessment-questions.', 'admin.wellness.']],
            'mentor-videos' => ['label' => 'Mentor videos', 'group' => 'Learning', 'match' => ['admin.mentor-videos.']],
            'career-services' => ['label' => 'Career services', 'group' => 'Services', 'match' => ['admin.career-services.', 'admin.career-service-invoices.']],
            'mock-interviews' => ['label' => 'Mock interviews', 'group' => 'Services', 'match' => ['admin.mock-interviews.']],
            'jobs' => ['label' => 'Job listings', 'group' => 'Services', 'match' => ['admin.jobs.']],
            'community' => ['label' => 'Community', 'group' => 'Community', 'match' => ['admin.community.']],
            'bulk-emails' => ['label' => 'Bulk email', 'group' => 'Community', 'match' => ['admin.bulk-emails.']],
            'insights' => ['label' => 'Insights & content', 'group' => 'Utilities', 'match' => [
                'admin.blogs.', 'admin.white-papers.', 'admin.case-studies.', 'admin.testimonials.',
                'admin.learner-success-stories.', 'admin.podcasts.', 'admin.videos.', 'admin.events-webinars.',
                'admin.download-centres.', 'admin.mentorship-guides.', 'admin.career-guides.', 'admin.industry-reports.',
            ]],
            'settings' => ['label' => 'App settings', 'group' => 'Settings', 'match' => ['admin.settings.']],
            'logs' => ['label' => 'Activity logs', 'group' => 'Settings', 'match' => ['admin.logs.']],
            'roles' => ['label' => 'Roles & team', 'group' => 'Settings', 'match' => ['admin.roles.']],
        ];
    }

    public static function groupedCatalog(): array
    {
        $groups = [];
        foreach (self::catalog() as $key => $module) {
            $groups[$module['group']][$key] = $module['label'];
        }

        return $groups;
    }

    public static function moduleForRoute(?string $route): ?string
    {
        if (! $route) {
            return null;
        }

        foreach (self::catalog() as $key => $module) {
            foreach ($module['match'] as $prefix) {
                if ($route === rtrim($prefix, '.') || str_starts_with($route, $prefix)) {
                    return $key;
                }
            }
        }

        return null;
    }

    public function allows(string $module): bool
    {
        if ($this->is_super) {
            return true;
        }

        return in_array($module, $this->modules ?? [], true);
    }

    public function moduleLabels(): array
    {
        if ($this->is_super) {
            return ['Full access'];
        }

        $catalog = self::catalog();

        return collect($this->modules ?? [])
            ->map(fn ($key) => $catalog[$key]['label'] ?? $key)
            ->values()
            ->all();
    }
}
