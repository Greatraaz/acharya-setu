<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentProgress;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentScoreBand;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
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

        return $this->statsQuery()
            ->get()
            ->map(function (Assessment $assessment) {
                $assessment->question_count = (int) $assessment->questions_count;

                return $assessment;
            });
    }

    public function listWithStatsPaginated(int $perPage = 20, ?Request $request = null)
    {
        if (! $this->tableExists()) {
            return Assessment::query()->whereRaw('1 = 0')->paginate($perPage);
        }

        return $this->applyListFilters($this->statsQuery(), $request)
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Assessment $assessment) {
                $assessment->question_count = (int) $assessment->questions_count;

                return $assessment;
            });
    }

    private function statsQuery()
    {
        return Assessment::query()
            ->withCount(['questions'])
            ->withCount([
                'progress as completion_count' => fn ($q) =>
                    $q->whereNotNull('completed_at'),
            ])
            ->latest();
    }

    private function applyListFilters($query, ?Request $request)
    {
        if (! $request) {
            return $query;
        }

        $search = trim((string) $request->input('search', $request->input('q', '')));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $status = $request->input('status');
        if (in_array($status, ['active', 'inactive'], true)) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function createFromRequest(
        Request $request,
        ?int $createdBy = null
    ): Assessment {
        $data = $request->all();
        $assignToAll = $this->resolveAssignToAll($request);

        $payload = [
            'id'           => $this->nextId(),
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'image'        => $request->input('image'),
            'icon'         => $request->input('icon'),
            'status'       => $data['status'] ?? 'active',
            'created_by'   => $createdBy,
        ];

        if (Schema::hasColumn('assessments', 'assign_to_all')) {
            $payload['assign_to_all'] = $assignToAll;
        }

        $assessment = Assessment::create($payload);

        $this->syncScoreBands(
            $assessment,
            $request->input('bands', [])
        );

        $this->syncAssignments($assessment, $request, $createdBy, $assignToAll);

        return $assessment->fresh(['scoreBands', 'assignedMentees']);
    }

    public function updateFromRequest(
        Request $request,
        Assessment $assessment
    ): Assessment {
        $data = $request->all();
        $assignToAll = $this->resolveAssignToAll($request);

        $updateData = [
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'instructions' => $data['instructions'] ?? null,
            'status'       => $data['status'] ?? $assessment->status ?? 'active',
        ];

        if (Schema::hasColumn('assessments', 'assign_to_all')) {
            $updateData['assign_to_all'] = $assignToAll;
        }

        if ($request->filled('image')) {
            $updateData['image'] = $request->input('image');
        }

        if ($request->filled('icon')) {
            $updateData['icon'] = $request->input('icon');
        }

        $assessment->update($updateData);

        $this->syncScoreBands(
            $assessment,
            $request->input('bands', [])
        );

        $this->syncAssignments(
            $assessment,
            $request,
            $assessment->created_by ? (int) $assessment->created_by : null,
            $assignToAll
        );

        return $assessment->fresh(['scoreBands', 'assignedMentees']);
    }

    public function delete(Assessment $assessment): void
    {
        if (Schema::hasTable('assessment_progress')) {
            AssessmentProgress::where('assessment_id', $assessment->id)->delete();
        }

        if (Schema::hasTable('assessment_assignments')) {
            $assessment->assignedMentees()->detach();
        }

        $assessment->questions()->delete();
        $assessment->scoreBands()->delete();
        $assessment->delete();
    }

    public function formatForApi(
        Assessment $assessment,
        bool $includeQuestions = false
    ): array {
        $assessment->loadMissing(['scoreBands', 'assignedMentees:id,name,email,role']);

        $payload = [
            'id'               => $assessment->id,
            'title'            => $assessment->title,
            'description'      => $assessment->description,
            'instructions'     => $assessment->instructions,
            'image'            => $assessment->imageUrl(),
            'icon'             => $assessment->iconUrl(),
            'status'           => $assessment->status ?? 'active',
            'assign_to_all'    => (bool) ($assessment->assign_to_all ?? true),
            'mentee_ids'       => $assessment->relationLoaded('assignedMentees')
                ? $assessment->assignedMentees->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
                : [],
            'assigned_mentees' => $assessment->relationLoaded('assignedMentees')
                ? $assessment->assignedMentees->map(fn (User $u) => $u->only(['id', 'name', 'email', 'role']))->values()->all()
                : [],
            'question_count'   => $assessment->questions()->count(),
            'questionCount'    => $assessment->questions()->count(),
            'completion_count' => Schema::hasTable('assessment_progress')
                ? AssessmentProgress::where('assessment_id', $assessment->id)
                    ->whereNotNull('completed_at')
                    ->count()
                : 0,
            'score_bands'      => $assessment->scoreBands
                ->map(fn (AssessmentScoreBand $b) => [
                    'index'       => $b->band_index,
                    'from'        => $b->range_from,
                    'to'          => $b->range_to,
                    'heading'     => $b->heading,
                    'description' => $b->description,
                ])
                ->values(),
            'created_at'       => $assessment->created_at,
            'updated_at'       => $assessment->updated_at,
        ];

        if ($includeQuestions) {
            $payload['questions'] = $assessment->questions()
                ->get()
                ->map(fn (AssessmentQuestion $q) => [
                    'id'       => $q->id,
                    'question' => $q->question,
                    'options'  => collect($q->optionLabels())
                        ->map(fn ($text, $score) => [
                            'score' => (int) $score,
                            'text'  => $text,
                        ])
                        ->values(),
                ])
                ->values()
                ->all();
        }

        return $payload;
    }

    public function validatedAssessment(
        Request $request,
        ?int $ignoreId = null,
        ?User $actor = null
    ): array {
        $actor = $actor ?? $request->user();
        $allowedMenteeIds = $this->allowedMenteeIdsForActor($actor);
        $allowedOptionValues = array_merge(['all'], array_map('strval', $allowedMenteeIds));

        // Normalize single dropdown value ("all" or one mentee id).
        $rawIds = $request->input('mentee_ids', []);
        if (! is_array($rawIds)) {
            $rawIds = [$rawIds];
        }
        $rawIds = array_values(array_filter($rawIds, fn ($id) => $id !== null && $id !== ''));
        if ($rawIds === []) {
            $rawIds = ['all'];
        }
        $request->merge(['mentee_ids' => $rawIds]);

        return $request->validate([
            'title'               => 'required|string|max:200',
            'description'         => 'nullable|string',
            'instructions'        => 'nullable|string',
            'status'              => 'nullable|in:active,inactive',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'icon'                => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'bands'               => 'required|array|size:4',
            'bands.*.from'        => 'required|integer|min:0',
            'bands.*.to'          => 'required|integer|min:0',
            'bands.*.heading'     => 'required|string|max:200',
            'bands.*.description' => 'nullable|string',
            'assignment_scope'    => 'nullable|in:all,selected',
            'assign_to_all'       => 'nullable|boolean',
            'mentee_ids'          => 'required|array|min:1',
            'mentee_ids.*'        => ['required', Rule::in($allowedOptionValues)],
        ]);
    }

    /**
     * @return list<int>
     */
    public function allowedMenteeIdsForActor(?User $actor): array
    {
        if (! $actor) {
            return [];
        }

        if ($actor->isAdmin()) {
            return User::query()
                ->where('role', 'mentee')
                ->orderBy('name')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        if ($actor->isMentor()) {
            return User::menteeIdsLinkedToMentor((int) $actor->id)
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return [];
    }

    public function assigneeOptionsForActor(?User $actor): Collection
    {
        $ids = $this->allowedMenteeIdsForActor($actor);

        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->where('role', 'mentee')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    private function resolveAssignToAll(Request $request): bool
    {
        if ($request->filled('assignment_scope')) {
            return $request->input('assignment_scope') === 'all';
        }

        if ($request->has('assign_to_all')) {
            return $request->boolean('assign_to_all');
        }

        // Prefer explicit mentee_ids when provided without scope.
        if ($request->filled('mentee_ids') && is_array($request->input('mentee_ids')) && count($request->input('mentee_ids')) > 0) {
            $ids = array_filter($request->input('mentee_ids'), fn ($id) => (string) $id !== 'all');

            return count($ids) === 0;
        }

        return true;
    }

    private function syncAssignments(
        Assessment $assessment,
        Request $request,
        ?int $assignedBy,
        bool $assignToAll
    ): void {
        if (! Schema::hasTable('assessment_assignments')) {
            return;
        }

        if ($assignToAll) {
            $assessment->assignedMentees()->detach();

            return;
        }

        $ids = collect($request->input('mentee_ids', []))
            ->filter(fn ($id) => (string) $id !== 'all')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $sync = [];
        foreach ($ids as $menteeId) {
            $sync[$menteeId] = [
                'assigned_by' => $assignedBy,
            ];
        }

        $assessment->assignedMentees()->sync($sync);
    }

    public function syncScoreBands(
        Assessment $assessment,
        array $bands
    ): void {
        foreach (range(0, 3) as $index) {
            $band = $bands[$index] ?? [];

            $from = (int) ($band['from'] ?? 0);
            $to   = (int) ($band['to'] ?? 0);

            if ($to < $from) {
                throw ValidationException::withMessages([
                    "bands.$index.to" =>
                        'The "To" value must be greater than or equal to "From".',
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