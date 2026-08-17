<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentProgress;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentScoreBand;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AssessmentService
{
    public function tableExists(): bool
    {
        return Schema::hasTable('assessments');
    }

    public function listWithStats(): Collection
    {
        if (! $this->tableExists()) {
            return collect();
        }

        return Assessment::query()
            ->withCount(['questions'])
            ->withCount(['progress as completion_count' => fn ($q) => $q->whereNotNull('completed_at')])
            ->latest()
            ->get()
            ->map(function (Assessment $assessment) {
                $assessment->question_count = (int) $assessment->questions_count;

                return $assessment;
            });
    }

    public function createFromRequest(Request $request, ?int $createdBy = null): Assessment
    {
        $data = $this->validatedAssessment($request);

        $assessment = Assessment::create([
            'id'           => $this->nextId(),
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'image'        => $data['image'] ?? null,
            'icon'         => $data['icon'] ?? null,
            'status'       => $data['status'] ?? 'active',
            'created_by'   => $createdBy,
        ]);

        $this->syncScoreBands($assessment, $request->input('bands', []));

        return $assessment->fresh(['scoreBands']);
    }

    public function updateFromRequest(Request $request, Assessment $assessment): Assessment
    {
        $data = $this->validatedAssessment($request, $assessment->id);

        $assessment->update([
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'image'        => $data['image'] ?? $assessment->image,
            'icon'         => $data['icon'] ?? $assessment->icon,
            'status'       => $data['status'] ?? $assessment->status ?? 'active',
        ]);

        $this->syncScoreBands($assessment, $request->input('bands', []));

        return $assessment->fresh(['scoreBands']);
    }

    public function delete(Assessment $assessment): void
    {
        if (Schema::hasTable('assessment_progress')) {
            AssessmentProgress::where('assessment_id', $assessment->id)->delete();
        }

        $assessment->questions()->delete();
        $assessment->scoreBands()->delete();
        $assessment->delete();
    }

    public function formatForApi(Assessment $assessment, bool $includeQuestions = false): array
    {
        $assessment->loadMissing(['scoreBands']);

        $payload = [
            'id'               => $assessment->id,
            'title'            => $assessment->title,
            'description'      => $assessment->description,
            'instructions'     => $assessment->instructions,
            'image'            => $assessment->imageUrl(),
            'icon'             => $assessment->iconUrl(),
            'status'           => $assessment->status ?? 'active',
            'question_count'   => $assessment->questions()->count(),
            'questionCount'    => $assessment->questions()->count(),
            'completion_count' => Schema::hasTable('assessment_progress')
                ? AssessmentProgress::where('assessment_id', $assessment->id)->whereNotNull('completed_at')->count()
                : 0,
            'score_bands'      => $assessment->scoreBands->map(fn (AssessmentScoreBand $b) => [
                'index'       => $b->band_index,
                'from'        => $b->range_from,
                'to'          => $b->range_to,
                'heading'     => $b->heading,
                'description' => $b->description,
            ])->values(),
            'created_at'       => $assessment->created_at,
            'updated_at'       => $assessment->updated_at,
        ];

        if ($includeQuestions) {
            $payload['questions'] = $assessment->questions()
                ->get()
                ->map(fn (AssessmentQuestion $q) => [
                    'id'       => $q->id,
                    'question' => $q->question,
                    'options'  => collect($q->optionLabels())->map(fn ($text, $score) => [
                        'score' => (int) $score,
                        'text'  => $text,
                    ])->values(),
                ])
                ->values()
                ->all();
        }

        return $payload;
    }

    public function validatedAssessment(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'                => 'required|string|max:200',
            'description'          => 'nullable|string',
            'instructions'         => 'nullable|string',
            'status'               => 'nullable|in:active,inactive',
            'image'                => 'nullable|string|max:500',
            'icon'                 => 'nullable|string|max:500',
            'bands'                => 'required|array|size:4',
            'bands.*.from'         => 'required|integer|min:0',
            'bands.*.to'           => 'required|integer|min:0',
            'bands.*.heading'      => 'required|string|max:200',
            'bands.*.description'  => 'nullable|string',
        ]);
    }

    public function syncScoreBands(Assessment $assessment, array $bands): void
    {
        foreach (range(0, 3) as $index) {
            $band = $bands[$index] ?? [];
            $from = (int) ($band['from'] ?? 0);
            $to = (int) ($band['to'] ?? 0);

            if ($to < $from) {
                throw ValidationException::withMessages([
                    "bands.$index.to" => 'The "To" value must be greater than or equal to "From".',
                ]);
            }

            AssessmentScoreBand::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'band_index'    => $index,
                ],
                [
                    'range_from'  => $from,
                    'range_to'    => $to,
                    'heading'     => trim((string) ($band['heading'] ?? '')),
                    'description' => $band['description'] ?? null,
                ]
            );
        }
    }

    public function nextId(): int
    {
        return (int) (Assessment::max('id') ?? 0) + 1;
    }
}
