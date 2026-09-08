<?php

namespace App\Services;

use App\Models\CurriculumMcq;
use App\Models\CurriculumTask;
use App\Models\EducationStream;
use App\Models\MenteeEnrollment;
use App\Models\StudentCurriculumProgress;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CurriculumSubmissionReviewService
{
    /**
     * Pending task/MCQ submissions for a mentor (optionally one mentee).
     */
    public function pendingForMentor(int $mentorId, ?int $menteeId = null, int $perPage = 20): LengthAwarePaginator
    {
        $taskIds = $this->mentorTaskIds($mentorId, $menteeId);
        $mcqIds = $this->mentorMcqIds($mentorId, $menteeId);

        $query = StudentCurriculumProgress::query()
            ->with('user:id,name,email,avatar_url')
            ->where('submission_status', 'submitted')
            ->when($menteeId, fn (Builder $q) => $q->where('user_id', $menteeId))
            ->where(function (Builder $q) use ($taskIds, $mcqIds) {
                $q->where(function (Builder $inner) use ($taskIds) {
                    $inner->where('item_type', 'task')->whereIn('item_id', $taskIds);
                })->orWhere(function (Builder $inner) use ($mcqIds) {
                    $inner->where('item_type', 'mcq')->whereIn('item_id', $mcqIds);
                });
            })
            ->latest('updated_at');

        return $query->paginate($perPage)->withQueryString();
    }

    public function pendingCountForMentor(int $mentorId, ?int $menteeId = null): int
    {
        if ($menteeId) {
            return $this->pendingCountForMentee($mentorId, $menteeId);
        }

        $taskIds = $this->mentorTaskIds($mentorId);
        $mcqIds = $this->mentorMcqIds($mentorId);

        return StudentCurriculumProgress::query()
            ->where('submission_status', 'submitted')
            ->where(function (Builder $q) use ($taskIds, $mcqIds) {
                $q->where(function (Builder $inner) use ($taskIds) {
                    $inner->where('item_type', 'task')->whereIn('item_id', $taskIds);
                })->orWhere(function (Builder $inner) use ($mcqIds) {
                    $inner->where('item_type', 'mcq')->whereIn('item_id', $mcqIds);
                });
            })
            ->count();
    }

    /**
     * @return array{progress: StudentCurriculumProgress, item: CurriculumTask|CurriculumMcq|null, context: array, attempt: ?\App\Models\McqAttempt}
     */
    public function decorate(StudentCurriculumProgress $progress): array
    {
        $item = null;
        $context = [];
        $attempt = null;

        if ($progress->item_type === 'task') {
            $item = CurriculumTask::with('week.month.stream')->find($progress->item_id);
            if ($item) {
                $context = [
                    'title' => $item->title,
                    'description' => $item->description,
                    'week_id' => $item->week_id,
                    'week_number' => $item->week?->week_number,
                    'month_number' => $item->week?->month?->month_number,
                    'track_name' => $item->week?->month?->stream?->name,
                    'track_id' => $item->week?->month?->stream_id,
                    'submission_type' => $item->submission_type,
                ];
            }
        } elseif ($progress->item_type === 'mcq') {
            $item = CurriculumMcq::with('week.month.stream')->find($progress->item_id);
            if ($item) {
                $attempt = $item->getAttemptForUser((int) $progress->user_id);
                $options = is_array($item->options) ? $item->options : [];
                $selectedIndex = $attempt?->selected_index;
                $correctIndex = (int) $item->correct_index;

                $context = [
                    'title' => $item->question,
                    'week_id' => $item->week_id,
                    'week_number' => $item->week?->week_number,
                    'month_number' => $item->week?->month?->month_number,
                    'track_name' => $item->week?->month?->stream?->name,
                    'track_id' => $item->week?->month?->stream_id,
                    'options' => $options,
                    'correct_index' => $correctIndex,
                    'correct_option' => $options[$correctIndex] ?? null,
                    'selected_index' => $selectedIndex,
                    'selected_option' => $selectedIndex !== null ? ($options[$selectedIndex] ?? null) : null,
                    'is_correct' => (bool) ($attempt?->is_correct),
                    'points_earned' => $attempt?->points_earned,
                    'explanation' => $item->explanation,
                ];
            }
        }

        return compact('progress', 'item', 'context', 'attempt');
    }

    public function pendingCollectionForMentor(int $mentorId, ?int $menteeId = null): Collection
    {
        $taskIds = $this->mentorTaskIds($mentorId, $menteeId);
        $mcqIds = $this->mentorMcqIds($mentorId, $menteeId);

        return StudentCurriculumProgress::query()
            ->with('user:id,name,email,avatar_url')
            ->where('submission_status', 'submitted')
            ->when($menteeId, fn (Builder $q) => $q->where('user_id', $menteeId))
            ->where(function (Builder $q) use ($taskIds, $mcqIds) {
                $q->where(function (Builder $inner) use ($taskIds) {
                    $inner->where('item_type', 'task')->whereIn('item_id', $taskIds);
                })->orWhere(function (Builder $inner) use ($mcqIds) {
                    $inner->where('item_type', 'mcq')->whereIn('item_id', $mcqIds);
                });
            })
            ->latest('updated_at')
            ->get()
            ->map(fn (StudentCurriculumProgress $p) => $this->decorate($p));
    }

    public function pendingCountForMentee(int $mentorId, int $menteeId): int
    {
        $taskIds = $this->mentorTaskIds($mentorId, $menteeId);
        $mcqIds = $this->mentorMcqIds($mentorId, $menteeId);

        return StudentCurriculumProgress::query()
            ->where('user_id', $menteeId)
            ->where('submission_status', 'submitted')
            ->where(function (Builder $q) use ($taskIds, $mcqIds) {
                $q->where(function (Builder $inner) use ($taskIds) {
                    $inner->where('item_type', 'task')->whereIn('item_id', $taskIds);
                })->orWhere(function (Builder $inner) use ($mcqIds) {
                    $inner->where('item_type', 'mcq')->whereIn('item_id', $mcqIds);
                });
            })
            ->count();
    }

    public function assertMentorOwnsProgress(User $mentor, StudentCurriculumProgress $progress): void
    {
        if (! in_array($progress->item_type, ['task', 'mcq'], true)) {
            abort(422, 'Only task and MCQ submissions can be reviewed.');
        }

        $menteeId = (int) $progress->user_id;

        if (! $this->mentorLinkedToMentee($mentor->id, $menteeId)) {
            abort(403, 'You are not assigned to this mentee.');
        }

        if ($progress->item_type === 'task') {
            $task = CurriculumTask::with('week.month.stream')->find($progress->item_id);
            if (! $task || (int) $task->mentee_id !== $menteeId) {
                abort(404, 'Task submission not found.');
            }
            $streamMentorId = (int) ($task->week?->month?->stream?->mentor_id ?? 0);
            if ($streamMentorId && $streamMentorId !== (int) $mentor->id) {
                abort(403, 'This submission belongs to another mentor.');
            }

            return;
        }

        $mcq = CurriculumMcq::with('week.month.stream')->find($progress->item_id);
        if (! $mcq || (int) $mcq->mentee_id !== $menteeId) {
            abort(404, 'MCQ submission not found.');
        }
        $streamMentorId = (int) ($mcq->week?->month?->stream?->mentor_id ?? 0);
        if ($streamMentorId && $streamMentorId !== (int) $mentor->id) {
            abort(403, 'This submission belongs to another mentor.');
        }
    }

    public function review(StudentCurriculumProgress $progress, string $status, ?string $feedback = null): StudentCurriculumProgress
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'submission_status' => 'Status must be approved or rejected.',
            ]);
        }

        if ($progress->submission_status === 'approved' && $status === 'approved') {
            return $progress;
        }

        // MCQs: points + completion only when the mentee's answer is correct and mentor approves.
        if ($status === 'approved' && $progress->item_type === 'mcq') {
            $mcq = CurriculumMcq::find($progress->item_id);
            $attempt = $mcq?->getAttemptForUser((int) $progress->user_id);
            $isCorrect = (bool) ($attempt?->is_correct);

            if (! $isCorrect) {
                $progress->update([
                    'submission_status' => 'rejected',
                    'mentor_feedback'   => $feedback ?: 'Answer was incorrect. Please try again.',
                    'reviewed_at'       => now(),
                    'is_completed'      => false,
                    'completed_at'      => null,
                ]);

                if ($attempt) {
                    $attempt->update(['points_earned' => 0]);
                }

                return $progress->fresh();
            }

            if ($attempt && $mcq) {
                $attempt->update([
                    'points_earned' => (int) $mcq->points,
                    'is_correct'    => true,
                ]);
            }
        }

        if ($status === 'rejected' && $progress->item_type === 'mcq') {
            $mcq = CurriculumMcq::find($progress->item_id);
            $attempt = $mcq?->getAttemptForUser((int) $progress->user_id);
            if ($attempt) {
                $attempt->update(['points_earned' => 0]);
            }
        }

        $progress->update([
            'submission_status' => $status,
            'mentor_feedback'   => $feedback,
            'reviewed_at'       => now(),
            'is_completed'      => $status === 'approved',
            'completed_at'      => $status === 'approved' ? now() : null,
        ]);

        return $progress->fresh();
    }

    public function toApiArray(StudentCurriculumProgress $progress): array
    {
        $decorated = $this->decorate($progress);
        $context = $decorated['context'];

        return [
            'id'                => $progress->id,
            'item_type'         => $progress->item_type,
            'item_id'           => $progress->item_id,
            'mentee_id'         => $progress->user_id,
            'mentee'            => $progress->user ? [
                'id'         => $progress->user->id,
                'name'       => $progress->user->name,
                'email'      => $progress->user->email,
                'avatar_url' => $progress->user->avatar_url,
            ] : null,
            'title'             => $context['title'] ?? null,
            'track_name'        => $context['track_name'] ?? null,
            'month_number'      => $context['month_number'] ?? null,
            'week_number'       => $context['week_number'] ?? null,
            'week_id'           => $context['week_id'] ?? null,
            'submission_type'   => $context['submission_type'] ?? null,
            'submission_status' => $progress->submission_status,
            'submission_text'   => $progress->submission_text,
            'submission_url'    => $progress->submission_url,
            'mentor_feedback'   => $progress->mentor_feedback,
            'is_completed'      => (bool) $progress->is_completed,
            'reviewed_at'       => $progress->reviewed_at,
            'submitted_at'      => $progress->updated_at,
            'created_at'        => $progress->created_at,
            'mcq'               => $progress->item_type === 'mcq' ? [
                'options'          => $context['options'] ?? [],
                'selected_index'   => $context['selected_index'] ?? null,
                'selected_option'  => $this->optionLabel($context['selected_option'] ?? null),
                'correct_index'    => $context['correct_index'] ?? null,
                'correct_option'   => $this->optionLabel($context['correct_option'] ?? null),
                'is_correct'       => $context['is_correct'] ?? null,
                'points_earned'    => $context['points_earned'] ?? null,
                'explanation'      => $context['explanation'] ?? null,
            ] : null,
        ];
    }

    /**
     * Flatten tasks + group same-week MCQs for mentor list UI.
     *
     * Accepts progress models, decorate() rows, or already-shaped API arrays.
     *
     * @param  Collection<int, StudentCurriculumProgress|array>  $progressRows
     * @return Collection<int, array>
     */
    public function groupForApi(Collection $progressRows): Collection
    {
        $items = $progressRows->map(function ($row) {
            if ($row instanceof StudentCurriculumProgress) {
                return $this->toApiArray($row);
            }

            if (is_array($row) && ($row['progress'] ?? null) instanceof StudentCurriculumProgress) {
                return $this->toApiArray($row['progress']);
            }

            if (is_array($row) && isset($row['item_type'])) {
                return $row;
            }

            throw new \InvalidArgumentException('Invalid pending submission row for API grouping.');
        });

        $tasks = $items->where('item_type', 'task')->values();
        $mcqGroups = $items
            ->where('item_type', 'mcq')
            ->groupBy(fn (array $row) => ($row['mentee_id'] ?? 0).':'.($row['week_id'] ?? 0))
            ->map(function (Collection $mcqs) {
                $first = $mcqs->first();
                $sorted = $mcqs->sortByDesc(fn (array $row) => (string) ($row['submitted_at'] ?? ''))->values();
                $weekNumber = $first['week_number'] ?? null;

                return [
                    'id'                => null,
                    'item_type'         => 'mcq_week',
                    'item_id'           => null,
                    'mentee_id'         => $first['mentee_id'] ?? null,
                    'mentee'            => $first['mentee'] ?? null,
                    'title'             => $weekNumber
                        ? 'Week '.$weekNumber.' MCQs'
                        : 'Week MCQs',
                    'track_name'        => $first['track_name'] ?? null,
                    'month_number'      => $first['month_number'] ?? null,
                    'week_number'       => $weekNumber,
                    'week_id'           => $first['week_id'] ?? null,
                    'submission_type'   => null,
                    'submission_status' => 'submitted',
                    'submission_text'   => null,
                    'submission_url'    => null,
                    'mentor_feedback'   => null,
                    'is_completed'      => false,
                    'reviewed_at'       => null,
                    'submitted_at'      => $sorted->first()['submitted_at'] ?? null,
                    'created_at'        => $sorted->last()['created_at'] ?? null,
                    'pending_count'     => $sorted->count(),
                    'mcq'               => null,
                    'mcqs'              => $sorted->map(fn (array $row) => [
                        'id'                => $row['id'],
                        'item_id'           => $row['item_id'],
                        'title'             => $row['title'],
                        'submission_status' => $row['submission_status'],
                        'is_completed'      => $row['is_completed'],
                        'reviewed_at'       => $row['reviewed_at'],
                        'submitted_at'      => $row['submitted_at'],
                        'created_at'        => $row['created_at'],
                        'mentor_feedback'   => $row['mentor_feedback'],
                        'mcq'               => $row['mcq'],
                    ])->values()->all(),
                ];
            })
            ->values();

        return $tasks
            ->concat($mcqGroups)
            ->sortByDesc(fn (array $row) => (string) ($row['submitted_at'] ?? ''))
            ->values();
    }

    private function optionLabel(mixed $option): ?string
    {
        if ($option === null) {
            return null;
        }
        if (is_array($option)) {
            return (string) ($option['text'] ?? json_encode($option));
        }

        return (string) $option;
    }

    /** @return Collection<int, int> */
    private function mentorTaskIds(int $mentorId, ?int $menteeId = null): Collection
    {
        return CurriculumTask::query()
            ->where('is_active', true)
            ->when($menteeId, fn ($q) => $q->where('mentee_id', $menteeId))
            ->whereHas('week.month.stream', function (Builder $q) use ($mentorId, $menteeId) {
                $q->where('mentor_id', $mentorId);
                if ($menteeId) {
                    $q->where('mentee_id', $menteeId);
                }
            })
            ->pluck('id');
    }

    /** @return Collection<int, int> */
    private function mentorMcqIds(int $mentorId, ?int $menteeId = null): Collection
    {
        return CurriculumMcq::query()
            ->where('is_active', true)
            ->when($menteeId, fn ($q) => $q->where('mentee_id', $menteeId))
            ->whereHas('week.month.stream', function (Builder $q) use ($mentorId, $menteeId) {
                $q->where('mentor_id', $mentorId);
                if ($menteeId) {
                    $q->where('mentee_id', $menteeId);
                }
            })
            ->pluck('id');
    }

    private function mentorLinkedToMentee(int $mentorId, int $menteeId): bool
    {
        return MenteeEnrollment::where('mentor_id', $mentorId)->where('mentee_id', $menteeId)->exists()
            || User::where('id', $menteeId)->where('role', 'mentee')->where('assigned_mentor_id', $mentorId)->exists()
            || EducationStream::where('mentor_id', $mentorId)->where('mentee_id', $menteeId)->exists();
    }
}
