<?php

namespace App\Services;

use App\Models\CurriculumMcq;
use App\Models\CurriculumMcqTopic;
use App\Models\CurriculumMonth;
use App\Models\CurriculumTask;
use App\Models\CurriculumWeek;
use App\Models\EducationStream;
use App\Models\User;
use App\Support\MentorIndustry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CurriculumTemplateImportService
{
    public const HEADERS = [
        'template_name',
        'industry',
        'icon',
        'color',
        'description',
        'expertise_tags',
        'is_active',
        'sort_order',
        'month_number',
        'month_title',
        'month_theme',
        'month_description',
        'learning_outcomes',
        'milestone_badge',
        'month_active',
        'week_number',
        'week_title',
        'week_focus',
        'mentor_guide',
        'video_url',
        'week_active',
        'task_title',
        'task_description',
        'task_type',
        'estimated_minutes',
        'is_required',
        'submission_type',
        'task_order',
        'task_active',
        'mcq_topic',
        'mcq_question',
        'mcq_option_1',
        'mcq_option_2',
        'mcq_option_3',
        'mcq_option_4',
        'mcq_correct_option',
        'mcq_explanation',
        'mcq_difficulty',
        'mcq_points',
    ];

    /**
     * @return array{created:int, updated:int, skipped:int, errors:list<string>}
     */
    public function import(UploadedFile $file, ?User $mentor = null): array
    {
        $mentorIndustry = $mentor ? MentorIndustry::canonicalize($mentor->field) : null;
        if ($mentor && ! $mentorIndustry) {
            throw ValidationException::withMessages([
                'file' => 'Set your industry on your profile before importing templates.',
            ]);
        }

        try {
            $rows = $this->readRows($file);
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'file' => 'Could not read that spreadsheet. Use the sample .xlsx file.',
            ]);
        }

        if ($rows === []) {
            return [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors'  => ['The spreadsheet has no data rows. Download the sample file for the required columns.'],
            ];
        }

        $grouped = [];
        $errors = [];
        $skipped = 0;

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $name = Str::limit($this->str($row['template_name'] ?? ''), 100, '');
            if ($name === '') {
                $skipped++;
                $errors[] = "Row {$line}: template_name is required.";
                continue;
            }

            $industry = $mentorIndustry ?: MentorIndustry::canonicalize($this->str($row['industry'] ?? ''));
            if (! $industry || ! in_array($industry, MentorIndustry::all(), true)) {
                $skipped++;
                $errors[] = "Row {$line}: industry must match a mentor onboarding domain (see the Guide sheet).";
                continue;
            }

            $key = strtolower($name).'|'.$industry.'|'.($mentor?->id ?? 'platform');
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'name'        => $name,
                    'industry'    => $industry,
                    'icon'        => $this->str($row['icon'] ?? '') ?: '🗺️',
                    'color'       => $this->color($row['color'] ?? null),
                    'description' => $this->str($row['description'] ?? '') ?: null,
                    'tags'        => $this->tags($row['expertise_tags'] ?? ''),
                    'is_active'   => $this->bool($row['is_active'] ?? true, true),
                    'sort_order'  => (int) ($row['sort_order'] ?? 0),
                    'months'      => [],
                ];
            }

            $monthNumber = (int) ($row['month_number'] ?? 0);
            if ($monthNumber < 1) {
                continue;
            }
            if ($monthNumber > 12) {
                $skipped++;
                $errors[] = "Row {$line}: month_number must be 1–12.";
                continue;
            }

            if (! isset($grouped[$key]['months'][$monthNumber])) {
                $grouped[$key]['months'][$monthNumber] = [
                    'title'       => $this->str($row['month_title'] ?? '') ?: 'Month '.$monthNumber,
                    'theme'       => $this->str($row['month_theme'] ?? '') ?: null,
                    'description' => $this->str($row['month_description'] ?? '') ?: null,
                    'outcomes'    => $this->outcomes($row['learning_outcomes'] ?? ''),
                    'badge'       => $this->str($row['milestone_badge'] ?? '') ?: null,
                    'is_active'   => $this->bool($row['month_active'] ?? true, true),
                    'weeks'       => [],
                ];
            } else {
                $month = &$grouped[$key]['months'][$monthNumber];
                if ($this->str($row['month_title'] ?? '') !== '') {
                    $month['title'] = $this->str($row['month_title']);
                }
                unset($month);
            }

            $weekNumber = (int) ($row['week_number'] ?? 0);
            if ($weekNumber < 1) {
                continue;
            }
            if ($weekNumber > 52) {
                $skipped++;
                $errors[] = "Row {$line}: week_number must be 1–52.";
                continue;
            }

            if (! isset($grouped[$key]['months'][$monthNumber]['weeks'][$weekNumber])) {
                $grouped[$key]['months'][$monthNumber]['weeks'][$weekNumber] = [
                    'title'        => $this->str($row['week_title'] ?? '') ?: 'Week '.$weekNumber,
                    'focus'        => $this->str($row['week_focus'] ?? '') ?: null,
                    'mentor_guide' => $this->str($row['mentor_guide'] ?? '') ?: null,
                    'video_url'    => $this->str($row['video_url'] ?? '') ?: null,
                    'is_active'    => $this->bool($row['week_active'] ?? true, true),
                    'tasks'        => [],
                    'mcqs'         => [],
                ];
            }

            $week = &$grouped[$key]['months'][$monthNumber]['weeks'][$weekNumber];
            $taskTitle = $this->str($row['task_title'] ?? '');
            if ($taskTitle !== '') {
                $type = strtolower($this->str($row['task_type'] ?? 'task')) ?: 'task';
                if (! array_key_exists($type, CurriculumTask::TYPES)) {
                    $type = 'task';
                }
                $submission = strtolower($this->str($row['submission_type'] ?? 'none')) ?: 'none';
                if (! array_key_exists($submission, CurriculumTask::SUBMISSION_TYPES)) {
                    $submission = 'none';
                }
                $week['tasks'][] = [
                    'title'             => $taskTitle,
                    'description'       => $this->str($row['task_description'] ?? '') ?: null,
                    'type'              => $type,
                    'estimated_minutes' => max(0, (int) ($row['estimated_minutes'] ?? 0)),
                    'is_required'       => $this->bool($row['is_required'] ?? true, true),
                    'submission_type'   => $submission,
                    'order_index'       => (int) ($row['task_order'] ?? count($week['tasks'])),
                    'is_active'         => $this->bool($row['task_active'] ?? true, true),
                ];
            }

            $question = $this->str($row['mcq_question'] ?? '');
            if ($question !== '') {
                $options = array_values(array_filter([
                    $this->str($row['mcq_option_1'] ?? ''),
                    $this->str($row['mcq_option_2'] ?? ''),
                    $this->str($row['mcq_option_3'] ?? ''),
                    $this->str($row['mcq_option_4'] ?? ''),
                ]));
                if (count($options) < 2) {
                    $skipped++;
                    $errors[] = "Row {$line}: MCQ needs at least two options.";
                } else {
                    $correct = (int) ($row['mcq_correct_option'] ?? 1);
                    $difficulty = strtolower($this->str($row['mcq_difficulty'] ?? 'medium')) ?: 'medium';
                    if (! in_array($difficulty, ['easy', 'medium', 'hard'], true)) {
                        $difficulty = 'medium';
                    }
                    $week['mcqs'][] = [
                        'topic'         => $this->str($row['mcq_topic'] ?? '') ?: 'General',
                        'question'      => $question,
                        'options'       => $options,
                        'correct_index' => max(0, min(count($options) - 1, $correct - 1)),
                        'explanation'   => $this->str($row['mcq_explanation'] ?? '') ?: null,
                        'difficulty'    => $difficulty,
                        'points'        => max(1, (int) ($row['mcq_points'] ?? 1)),
                    ];
                }
            }
            unset($week);
        }

        $created = 0;
        $updated = 0;

        foreach ($grouped as $payload) {
            try {
                DB::transaction(function () use ($payload, $mentor, &$created, &$updated) {
                    $wasNew = $this->persistTemplate($payload, $mentor);
                    if ($wasNew) {
                        $created++;
                    } else {
                        $updated++;
                    }
                });
            } catch (\Throwable $e) {
                report($e);
                $errors[] = 'Could not save “'.$payload['name'].'”. Check month/week numbers and required fields.';
            }
        }

        return compact('created', 'updated', 'skipped', 'errors');
    }

    public function summaryMessage(array $result): string
    {
        $parts = [];
        if ($result['created']) {
            $parts[] = $result['created'].' new template(s) created';
        }
        if ($result['updated']) {
            $parts[] = $result['updated'].' existing template(s) updated';
        }
        if ($result['skipped']) {
            $parts[] = $result['skipped'].' row(s) skipped';
        }

        if ($parts === []) {
            return 'No templates were imported. Check the spreadsheet columns and try the sample file.';
        }

        return 'Import complete: '.implode(', ', $parts).'.';
    }

    public function downloadSample(): StreamedResponse
    {
        $spreadsheet = $this->makeSampleSpreadsheet();

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'curriculum-templates-sample.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function persistTemplate(array $payload, ?User $mentor): bool
    {
        $query = EducationStream::query()
            ->templates()
            ->whereRaw('LOWER(name) = ?', [strtolower($payload['name'])])
            ->where('expertise_field', $payload['industry']);

        if ($mentor) {
            $query->where('mentor_id', $mentor->id);
        } else {
            $query->whereNull('mentor_id');
        }

        $template = $query->first();
        $isNew = $template === null;

        if ($isNew) {
            $template = EducationStream::create([
                'mentee_id'        => null,
                'mentor_id'        => $mentor?->id,
                'name'             => $payload['name'],
                'slug'             => $this->uniqueSlug($payload['name']),
                'icon'             => $payload['icon'],
                'color'            => $payload['color'],
                'description'      => $payload['description'],
                'is_active'        => $payload['is_active'],
                'is_template'      => true,
                'expertise_stream' => $payload['industry'],
                'expertise_field'  => $payload['industry'],
                'expertise_tags'   => $payload['tags'],
                'sort_order'       => $payload['sort_order'],
            ]);
        } else {
            $template->update([
                'icon'             => $payload['icon'],
                'color'            => $payload['color'],
                'description'      => $payload['description'],
                'is_active'        => $payload['is_active'],
                'expertise_stream' => $payload['industry'],
                'expertise_field'  => $payload['industry'],
                'expertise_tags'   => $payload['tags'],
                'sort_order'       => $payload['sort_order'],
            ]);
            CurriculumMonth::query()->where('stream_id', $template->id)->delete();
        }

        foreach ($payload['months'] as $monthNumber => $monthData) {
            $month = CurriculumMonth::create([
                'stream_id'         => $template->id,
                'mentee_id'         => null,
                'month_number'      => $monthNumber,
                'title'             => $monthData['title'],
                'theme'             => $monthData['theme'],
                'description'       => $monthData['description'],
                'learning_outcomes' => $monthData['outcomes'],
                'milestone_badge'   => $monthData['badge'],
                'is_active'         => $monthData['is_active'],
                'sort_order'        => $monthNumber,
            ]);

            foreach ($monthData['weeks'] as $weekNumber => $weekData) {
                $week = CurriculumWeek::create([
                    'month_id'     => $month->id,
                    'mentee_id'    => null,
                    'week_number'  => $weekNumber,
                    'title'        => $weekData['title'],
                    'focus'        => $weekData['focus'],
                    'mentor_guide' => $weekData['mentor_guide'],
                    'video_url'    => $weekData['video_url'],
                    'is_active'    => $weekData['is_active'],
                    'sort_order'   => $weekNumber,
                ]);

                foreach ($weekData['tasks'] as $order => $task) {
                    CurriculumTask::create([
                        'week_id'            => $week->id,
                        'mentee_id'          => null,
                        'title'              => $task['title'],
                        'description'        => $task['description'],
                        'type'               => $task['type'],
                        'order_index'        => $task['order_index'] ?: $order,
                        'estimated_minutes'  => $task['estimated_minutes'],
                        'is_required'        => $task['is_required'],
                        'is_active'          => $task['is_active'],
                        'submission_type'    => $task['submission_type'],
                    ]);
                }

                $topics = [];
                foreach ($weekData['mcqs'] as $order => $mcq) {
                    $topicName = $mcq['topic'];
                    if (! isset($topics[$topicName])) {
                        $topics[$topicName] = CurriculumMcqTopic::create([
                            'week_id'     => $week->id,
                            'mentee_id'   => null,
                            'name'        => $topicName,
                            'order_index' => count($topics),
                            'is_active'   => true,
                        ]);
                    }
                    CurriculumMcq::create([
                        'week_id'       => $week->id,
                        'topic_id'      => $topics[$topicName]->id,
                        'mentee_id'     => null,
                        'question'      => $mcq['question'],
                        'options'       => $mcq['options'],
                        'correct_index' => $mcq['correct_index'],
                        'explanation'   => $mcq['explanation'],
                        'difficulty'    => $mcq['difficulty'],
                        'points'        => $mcq['points'] ?? 1,
                        'order_index'   => $order,
                        'is_active'     => true,
                    ]);
                }
            }
        }

        return $isNew;
    }

    /** @return list<array<string, mixed>> */
    private function readRows(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $raw = $sheet->toArray(null, true, true, false);
        if ($raw === []) {
            return [];
        }

        $headerRow = array_shift($raw);
        $map = [];
        foreach ($headerRow as $i => $label) {
            $key = $this->normalizeHeader((string) $label);
            if ($key !== '') {
                $map[$i] = $key;
            }
        }

        $rows = [];
        foreach ($raw as $line) {
            if (! is_array($line) || $this->rowEmpty($line)) {
                continue;
            }
            $row = [];
            foreach ($map as $i => $key) {
                $row[$key] = $line[$i] ?? null;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function normalizeHeader(string $label): string
    {
        $key = strtolower(trim($label));
        $key = str_replace([' ', '-'], '_', $key);

        $aliases = [
            'name' => 'template_name',
            'template' => 'template_name',
            'domain' => 'industry',
            'field' => 'industry',
            'tags' => 'expertise_tags',
            'skills' => 'expertise_tags',
            'month' => 'month_number',
            'week' => 'week_number',
            'task' => 'task_title',
            'type' => 'task_type',
            'minutes' => 'estimated_minutes',
            'submission' => 'submission_type',
            'question' => 'mcq_question',
            'option_1' => 'mcq_option_1',
            'option_2' => 'mcq_option_2',
            'option_3' => 'mcq_option_3',
            'option_4' => 'mcq_option_4',
            'correct_option' => 'mcq_correct_option',
            'correct' => 'mcq_correct_option',
            'points' => 'mcq_points',
            'mcq_point' => 'mcq_points',
        ];

        return $aliases[$key] ?? $key;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'template';
        $slug = $base;
        $i = 2;
        while (EducationStream::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function str(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function bool(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $v = strtolower(trim((string) $value));

        return in_array($v, ['1', 'true', 'yes', 'y', 'active'], true);
    }

    private function color(mixed $value): string
    {
        $color = $this->str($value);
        if (preg_match('/^#?[0-9a-fA-F]{6}$/', $color)) {
            return str_starts_with($color, '#') ? $color : '#'.$color;
        }

        return '#7c3aed';
    }

    /** @return list<string> */
    private function tags(mixed $value): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($tag) => trim($tag),
            preg_split('/[,|;]/', $this->str($value)) ?: []
        ))));
    }

    /** @return list<string> */
    private function outcomes(mixed $value): array
    {
        return array_values(array_filter(array_map(
            fn ($item) => trim($item),
            preg_split('/\r\n|\r|\n|\|/', $this->str($value)) ?: []
        )));
    }

    private function rowEmpty(array $line): bool
    {
        foreach ($line as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function makeSampleSpreadsheet(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Templates');
        $sheet->fromArray(self::HEADERS, null, 'A1');
        $sheet->fromArray($this->sampleRows(), null, 'A2');
        $sheet->freezePane('A2');
        $lastCol = Coordinate::stringFromColumnIndex($this->headerCount());
        $sheet->getStyle('A1:'.$lastCol.'1')->getFont()->setBold(true);
        for ($i = 1; $i <= $this->headerCount(); $i++) {
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);
        }

        $guide = $spreadsheet->createSheet();
        $guide->setTitle('Guide');
        $guide->fromArray($this->guideRows(), null, 'A1');
        $guide->getStyle('A1:C1')->getFont()->setBold(true);
        foreach (range('A', 'C') as $col) {
            $guide->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function headerCount(): int
    {
        return count(self::HEADERS);
    }

    /** @return list<list<string>> */
    private function guideRows(): array
    {
        $rows = [
            ['Column', 'Required', 'Notes'],
            ['template_name', 'Yes', 'Same name + industry (and owner) become one template. Multiple templates can sit in one sheet.'],
            ['industry', 'Yes (admin)', implode(' | ', MentorIndustry::all())],
            ['icon', 'No', 'Emoji or short mark. Default 🗺️'],
            ['color', 'No', 'Hex like #0f766e'],
            ['description', 'No', 'Shown on the template card'],
            ['expertise_tags', 'No', 'Comma or | separated'],
            ['is_active / month_active / week_active / task_active / is_required', 'No', 'yes/no (default yes)'],
            ['sort_order', 'No', 'Integer'],
            ['month_number', 'Yes to add content', '1–12. Repeat the template columns on every content row.'],
            ['month_title, month_theme, month_description, learning_outcomes, milestone_badge', 'No', 'Outcomes can be pipe- or newline-separated'],
            ['week_number', 'Yes to add tasks/MCQs', '1–52 within that month'],
            ['week_title, week_focus, mentor_guide, video_url', 'No', ''],
            ['task_title', 'Yes for a task', 'Leave blank to only add an MCQ on that week'],
            ['task_type', 'No', implode(', ', array_keys(CurriculumTask::TYPES))],
            ['estimated_minutes, task_order, submission_type', 'No', 'submission_type: '.implode(', ', array_keys(CurriculumTask::SUBMISSION_TYPES))],
            ['mcq_topic, mcq_question, mcq_option_1–4, mcq_correct_option, mcq_explanation, mcq_difficulty, mcq_points', 'No', 'Need a question plus at least two options. correct_option is 1-based. difficulty: easy, medium, hard. points default 1'],
            ['Mentor import', '—', 'Industry is forced to the mentor’s profile domain. Re-importing the same template name updates it.'],
        ];

        return $rows;
    }

    /** @return list<list<mixed>> */
    private function sampleRows(): array
    {
        return [
            [
                'Data Science Starter', 'Data Science & AI / ML', '🤖', '#0f766e',
                'SQL, Python, and a first ML project.', 'Python, SQL, ML', 'yes', 1,
                1, 'Foundations', 'Fundamentals', 'Clean data and write SQL.', 'Query with joins|Clean a dataset', 'Analyst', 'yes',
                1, 'SQL week', 'Joins and aggregations', 'Review mistakes together.', '', 'yes',
                'Finish 20 SQL practice queries', 'Use an online SQL playground.', 'task', 120, 'yes', 'link', 0, 'yes',
                '', '', '', '', '', '', '', '', '', '',
            ],
            [
                'Data Science Starter', 'Data Science & AI / ML', '🤖', '#0f766e',
                'SQL, Python, and a first ML project.', 'Python, SQL, ML', 'yes', 1,
                1, 'Foundations', 'Fundamentals', 'Clean data and write SQL.', 'Query with joins|Clean a dataset', 'Analyst', 'yes',
                1, 'SQL week', 'Joins and aggregations', 'Review mistakes together.', '', 'yes',
                'Explain 5 SQL pitfalls', 'Write them in your own words.', 'reflection', 30, 'yes', 'text', 1, 'yes',
                'SQL basics', 'Which clause filters rows?', 'WHERE', 'HAVING', 'GROUP BY', 'ORDER BY', 1, 'WHERE filters before grouping.', 'easy', 1,
            ],
            [
                'Product Sense Path', 'Product Management', '🧭', '#4338ca',
                'Discovery to a PM interview case.', 'Product Sense, Discovery', 'yes', 2,
                1, 'Discovery', 'Research', 'Talk to users before building.', 'Interview 3 users|Write a problem brief', 'Discoverer', 'yes',
                1, 'User interviews', 'Jobs-to-be-done', 'Listen more than you talk.', '', 'yes',
                'Interview 3 users and log pains', 'Capture verbatim quotes.', 'task', 90, 'yes', 'text', 0, 'yes',
                '', '', '', '', '', '', '', '', '', '',
            ],
        ];
    }
}
