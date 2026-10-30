<?php

namespace App\Services;

use App\Models\CurriculumMcq;
use App\Models\CurriculumMcqTopic;
use App\Models\CurriculumMonth;
use App\Models\CurriculumTask;
use App\Models\CurriculumWeek;
use App\Models\EducationStream;
use App\Models\MenteeEnrollment;
use App\Models\TaskSupportingMaterial;
use App\Models\User;
use App\Support\MentorIndustry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CurriculumTemplateService
{
    public function templatesForMentor(User $mentor): Collection
    {
        $industry = MentorIndustry::canonicalize($mentor->field);

        $templates = EducationStream::query()
            ->templates()
            ->active()
            ->withCount('months')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn (EducationStream $template) => $this->visibleToMentor($mentor, $template))
            ->values();

        $counts = $this->contentCounts($templates->pluck('id'));

        return $templates
            ->map(function (EducationStream $template) use ($counts, $industry, $mentor) {
                $template->setAttribute('match_score', 100);
                $template->setAttribute('matched', true);
                $template->setAttribute('owned', (int) $template->mentor_id === (int) $mentor->id);
                $template->setAttribute('industry', $industry ?: $template->expertise_field);
                $template->setAttribute('weeks_count', (int) ($counts['weeks'][$template->id] ?? 0));
                $template->setAttribute('tasks_count', (int) ($counts['tasks'][$template->id] ?? 0));

                return $template;
            })
            ->values();
    }

    public function visibleToMentor(User $mentor, EducationStream $template): bool
    {
        if (! $template->is_template || ! $template->is_active) {
            return false;
        }

        if ($template->mentor_id) {
            return (int) $template->mentor_id === (int) $mentor->id;
        }

        return MentorIndustry::matches(
            $template->expertise_field ?: $template->expertise_stream,
            $mentor->field
        );
    }

    public function matchScore(EducationStream $template, User $mentor): int
    {
        return $this->visibleToMentor($mentor, $template) ? 100 : 0;
    }

    public function cloneToMentee(
        EducationStream $template,
        User $mentor,
        User $mentee,
        ?string $name = null,
        ?string $description = null,
    ): EducationStream {
        if (! $template->is_template) {
            throw ValidationException::withMessages([
                'template' => 'This curriculum is not a starter template.',
            ]);
        }

        return DB::transaction(function () use ($template, $mentor, $mentee, $name, $description) {
            $template->load([
                'months.weeks.tasks',
                'months.weeks.mcqTopics.mcqs',
                'months.weeks.mcqs',
                'months.weeks.supportingMaterials',
            ]);

            $trackName = trim((string) ($name ?: $template->name)) ?: $template->name;

            $track = EducationStream::create([
                'mentee_id'      => $mentee->id,
                'mentor_id'      => $mentor->id,
                'name'           => $trackName,
                'slug'           => $this->uniqueSlug($trackName, $mentee->id),
                'icon'           => $template->icon,
                'color'          => $template->color,
                'description'    => $description !== null && $description !== '' ? $description : $template->description,
                'is_active'      => true,
                'is_template'    => false,
                'cloned_from_id' => $template->id,
                'sort_order'     => 0,
            ]);

            foreach ($template->months as $month) {
                $newMonth = $month->replicate();
                $newMonth->stream_id = $track->id;
                $newMonth->mentee_id = $mentee->id;
                $newMonth->save();

                foreach ($month->weeks as $week) {
                    $this->cloneWeek($week, $newMonth, $mentor, $mentee);
                }
            }

            $this->ensureEnrollment($track);

            return $track->fresh(['mentee:id,name,email,avatar_url']);
        });
    }

    public function summarize(EducationStream $template): array
    {
        $counts = $this->contentCounts(collect([$template->id]));

        return [
            'id'                => $template->id,
            'name'              => $template->name,
            'slug'              => $template->slug,
            'description'       => $template->description,
            'icon'              => $template->icon,
            'color'             => $template->color,
            'industry'          => $template->expertise_field ?: $template->expertise_stream,
            'expertise_stream'  => $template->expertise_stream,
            'expertise_field'   => $template->expertise_field,
            'expertise_tags'    => array_values($template->expertise_tags ?? []),
            'is_active'         => (bool) $template->is_active,
            'months_count'      => (int) ($template->months_count ?? $template->months()->count()),
            'weeks_count'       => (int) ($template->weeks_count ?? ($counts['weeks'][$template->id] ?? 0)),
            'tasks_count'       => (int) ($template->tasks_count ?? ($counts['tasks'][$template->id] ?? 0)),
            'match_score'       => (int) ($template->match_score ?? 0),
            'matched'           => (bool) ($template->matched ?? false),
        ];
    }

    public function outline(EducationStream $template): array
    {
        $template->load([
            'months' => fn ($q) => $q->orderBy('month_number')->with([
                'weeks' => fn ($w) => $w->orderBy('week_number')->with([
                    'tasks:id,week_id,title,type,estimated_minutes,is_required',
                    'mcqTopics:id,week_id,name',
                ]),
            ]),
        ]);

        return $template->months->map(fn (CurriculumMonth $month) => [
            'id'           => $month->id,
            'month_number' => $month->month_number,
            'title'        => $month->title,
            'theme'        => $month->theme,
            'description'  => $month->description,
            'weeks'        => $month->weeks->map(fn (CurriculumWeek $week) => [
                'id'          => $week->id,
                'week_number' => $week->week_number,
                'title'       => $week->title,
                'focus'       => $week->focus,
                'tasks'       => $week->tasks->map(fn (CurriculumTask $task) => [
                    'id'    => $task->id,
                    'title' => $task->title,
                    'type'  => $task->type,
                ])->values(),
                'mcq_topics'  => $week->mcqTopics->pluck('name')->values(),
            ])->values(),
        ])->values()->all();
    }

    private function cloneWeek(CurriculumWeek $week, CurriculumMonth $newMonth, User $mentor, User $mentee): void
    {
        $newWeek = $week->replicate();
        $newWeek->month_id = $newMonth->id;
        $newWeek->mentee_id = $mentee->id;
        $newWeek->save();

        foreach ($week->tasks as $task) {
            $copy = $task->replicate();
            $copy->week_id = $newWeek->id;
            $copy->mentee_id = $mentee->id;
            $copy->save();
        }

        $topicMap = [];
        foreach ($week->mcqTopics as $topic) {
            $newTopic = $topic->replicate();
            $newTopic->week_id = $newWeek->id;
            $newTopic->mentee_id = $mentee->id;
            $newTopic->save();
            $topicMap[$topic->id] = $newTopic->id;

            foreach ($topic->mcqs as $mcq) {
                $this->cloneMcq($mcq, $newWeek, $mentee->id, $newTopic->id);
            }
        }

        foreach ($week->mcqs as $mcq) {
            if ($mcq->topic_id && isset($topicMap[$mcq->topic_id])) {
                continue;
            }

            $this->cloneMcq(
                $mcq,
                $newWeek,
                $mentee->id,
                $mcq->topic_id ? ($topicMap[$mcq->topic_id] ?? null) : null
            );
        }

        foreach ($week->supportingMaterials as $material) {
            $copy = $material->replicate();
            $copy->week_id = $newWeek->id;
            $copy->mentee_id = $mentee->id;
            $copy->mentor_id = $mentor->id;
            $copy->save();
        }
    }

    private function cloneMcq(CurriculumMcq $mcq, CurriculumWeek $week, int $menteeId, ?int $topicId): void
    {
        $copy = $mcq->replicate();
        $copy->week_id = $week->id;
        $copy->topic_id = $topicId;
        $copy->mentee_id = $menteeId;
        $copy->save();
    }

    private function ensureEnrollment(EducationStream $track): void
    {
        if (! $track->mentee_id || ! $track->mentor_id) {
            return;
        }

        MenteeEnrollment::firstOrCreate(
            [
                'mentee_id' => $track->mentee_id,
                'stream_id' => $track->id,
            ],
            [
                'mentor_id'         => $track->mentor_id,
                'start_date'        => now()->toDateString(),
                'expected_end_date' => now()->addMonths(6)->toDateString(),
                'status'            => 'active',
                'current_month'     => 1,
                'current_week'      => 1,
            ]
        );
    }

    private function uniqueSlug(string $name, int $menteeId): string
    {
        $base = Str::slug($name) ?: 'track';
        $slug = $base;

        if (EducationStream::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$menteeId;
        }

        $i = 2;
        while (EducationStream::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$menteeId.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /** @param  \Illuminate\Support\Collection<int, int|string>  $streamIds */
    private function contentCounts(Collection $streamIds): array
    {
        $ids = $streamIds->filter()->map(fn ($id) => (int) $id)->values();
        if ($ids->isEmpty()) {
            return ['weeks' => [], 'tasks' => []];
        }

        $weeks = CurriculumWeek::query()
            ->join('curriculum_months', 'curriculum_months.id', '=', 'curriculum_weeks.month_id')
            ->whereIn('curriculum_months.stream_id', $ids)
            ->selectRaw('curriculum_months.stream_id as stream_id, count(curriculum_weeks.id) as aggregate')
            ->groupBy('curriculum_months.stream_id')
            ->pluck('aggregate', 'stream_id');

        $tasks = CurriculumTask::query()
            ->join('curriculum_weeks', 'curriculum_weeks.id', '=', 'curriculum_tasks.week_id')
            ->join('curriculum_months', 'curriculum_months.id', '=', 'curriculum_weeks.month_id')
            ->whereIn('curriculum_months.stream_id', $ids)
            ->selectRaw('curriculum_months.stream_id as stream_id, count(curriculum_tasks.id) as aggregate')
            ->groupBy('curriculum_months.stream_id')
            ->pluck('aggregate', 'stream_id');

        return [
            'weeks' => $weeks->all(),
            'tasks' => $tasks->all(),
        ];
    }
}
