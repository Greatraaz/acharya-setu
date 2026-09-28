<?php

namespace App\Services;

use App\Models\EducationStream;
use App\Models\MenteeEnrollment;
use App\Models\MenteeTask;
use App\Models\StudentCurriculumProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class MenteeTaskService
{
    public function validationRules(bool $creating = true): array
    {
        return [
            'mentee_id'          => [$creating ? 'required' : 'sometimes', 'integer', Rule::exists('users', 'id')->where('role', 'mentee')],
            'mentor_id'          => [$creating ? 'sometimes' : 'sometimes', 'nullable', 'integer', Rule::exists('users', 'id')->where('role', 'mentor')],
            'title'              => [$creating ? 'required' : 'sometimes', 'string', 'max:200'],
            'description'        => 'nullable|string',
            'type'               => ['nullable', Rule::in(array_keys(MenteeTask::TYPES))],
            'submission_type'    => ['nullable', Rule::in(array_keys(MenteeTask::SUBMISSION_TYPES))],
            'estimated_minutes'  => 'nullable|integer|min:1|max:600',
            'due_at'             => 'nullable|date',
            'is_required'        => 'nullable',
            'is_active'          => 'nullable',
            'replace_attachments'=> 'nullable',
            'clear_attachments'  => 'nullable',
        ];
    }

    public function create(User $actor, array $data, ?Request $request = null): MenteeTask
    {
        $menteeId = (int) $data['mentee_id'];
        $mentorId = isset($data['mentor_id']) ? (int) $data['mentor_id'] : null;

        if ($actor->role === 'mentor') {
            $this->assertMentorLinkedToMentee((int) $actor->id, $menteeId);
            $mentorId = (int) $actor->id;
        } elseif ($actor->role === 'admin') {
            if (! $mentorId) {
                throw new InvalidArgumentException('Assign a mentor to this task.');
            }
            $this->assertMentorLinkedToMentee($mentorId, $menteeId);
        } else {
            abort(403);
        }

        if ($request) {
            $this->validateAttachmentFiles($request);
        }

        $attachments = $request
            ? $this->processUploadedAttachments($request, [])
            : [];

        return MenteeTask::create([
            'mentee_id'         => $menteeId,
            'mentor_id'         => $mentorId,
            'created_by'        => $actor->id,
            'title'             => $data['title'],
            'description'       => $data['description'] ?? null,
            'type'              => $data['type'] ?? 'task',
            'submission_type'   => $data['submission_type'] ?? 'none',
            'attachments'       => $attachments,
            'estimated_minutes' => isset($data['estimated_minutes']) ? (int) $data['estimated_minutes'] : null,
            'due_at'            => $data['due_at'] ?? null,
            'is_required'       => array_key_exists('is_required', $data)
                ? filter_var($data['is_required'], FILTER_VALIDATE_BOOLEAN)
                : true,
            'is_active'         => array_key_exists('is_active', $data)
                ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN)
                : true,
        ]);
    }

    public function update(User $actor, MenteeTask $task, array $data, ?Request $request = null): MenteeTask
    {
        $this->assertCanManage($actor, $task);

        if ($request) {
            $this->validateAttachmentFiles($request);
        }

        $fields = collect($data)->only([
            'title', 'description', 'type', 'submission_type', 'estimated_minutes', 'due_at',
        ])->filter(fn ($v) => $v !== null)->all();

        if ($actor->role === 'admin' && isset($data['mentor_id'])) {
            $mentorId = (int) $data['mentor_id'];
            $this->assertMentorLinkedToMentee($mentorId, (int) $task->mentee_id);
            $fields['mentor_id'] = $mentorId;
        }

        if ($actor->role === 'admin' && isset($data['mentee_id'])) {
            $menteeId = (int) $data['mentee_id'];
            $mentorId = (int) ($fields['mentor_id'] ?? $task->mentor_id);
            $this->assertMentorLinkedToMentee($mentorId, $menteeId);
            $fields['mentee_id'] = $menteeId;
        }

        if ($request?->hasFile('attachments')) {
            $replace = $request->boolean('replace_attachments', false);
            $existing = $replace ? [] : ($task->attachments ?? []);
            if ($replace) {
                $this->deleteStoredAttachments($task->attachments ?? []);
            }
            $fields['attachments'] = $this->processUploadedAttachments($request, $existing);
        } elseif ($request?->boolean('clear_attachments')) {
            $this->deleteStoredAttachments($task->attachments ?? []);
            $fields['attachments'] = [];
        }

        if (array_key_exists('is_required', $data) || $request?->has('is_required')) {
            $fields['is_required'] = $request
                ? $request->boolean('is_required')
                : filter_var($data['is_required'], FILTER_VALIDATE_BOOLEAN);
        }
        if (array_key_exists('is_active', $data) || $request?->has('is_active')) {
            $fields['is_active'] = $request
                ? $request->boolean('is_active')
                : filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        if ($fields !== []) {
            $task->update($fields);
        }

        return $task->fresh(['mentee', 'mentor', 'creator']);
    }

    public function delete(User $actor, MenteeTask $task): void
    {
        $this->assertCanManage($actor, $task);
        $this->deleteStoredAttachments($task->attachments ?? []);
        StudentCurriculumProgress::query()
            ->where('item_type', MenteeTask::ITEM_TYPE)
            ->where('item_id', $task->id)
            ->delete();
        $task->delete();
    }

    public function submit(User $mentee, MenteeTask $task, Request $request): StudentCurriculumProgress
    {
        if ((int) $task->mentee_id !== (int) $mentee->id) {
            abort(404, 'Task not found.');
        }
        if (! $task->is_active) {
            throw new InvalidArgumentException('This task is no longer active.');
        }

        $extra = [];
        $complete = true;

        if ($task->submission_type && $task->submission_type !== 'none') {
            $request->validate([
                'submission_text' => 'nullable|string|max:5000',
                'submission_url'  => 'nullable|url|max:2000',
                'submission_file' => 'nullable|file|max:'.MenteeTask::ATTACHMENT_MAX_KB,
            ]);

            $hasText = $request->filled('submission_text');
            $hasUrl = $request->filled('submission_url');
            $hasFile = $request->hasFile('submission_file');

            if (! $hasText && ! $hasUrl && ! $hasFile) {
                throw ValidationException::withMessages([
                    'submission' => 'Please provide a submission (text, link, or file).',
                ]);
            }

            $extra['submission_status'] = 'submitted';
            $complete = false;

            if ($hasFile) {
                /** @var UploadedFile $file */
                $file = $request->file('submission_file');
                $path = $file->store('submissions/'.$mentee->id, 'public');
                $extra['submission_url'] = asset('storage/'.$path);
            }
            if ($hasText) {
                $extra['submission_text'] = $request->input('submission_text');
            }
            if ($hasUrl) {
                $extra['submission_url'] = $request->input('submission_url');
            }
            $extra['mentor_feedback'] = null;
            $extra['reviewed_at'] = null;
        }

        return StudentCurriculumProgress::markComplete(
            (int) $mentee->id,
            MenteeTask::ITEM_TYPE,
            (int) $task->id,
            array_merge($extra, ['is_completed' => $complete])
        );
    }

    public function review(User $mentor, StudentCurriculumProgress $progress, string $status, ?string $feedback = null): StudentCurriculumProgress
    {
        if ($progress->item_type !== MenteeTask::ITEM_TYPE) {
            abort(422, 'Only mentee task submissions can be reviewed here.');
        }

        if (! in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'submission_status' => 'Status must be approved or rejected.',
            ]);
        }

        $task = MenteeTask::find($progress->item_id);
        if (! $task) {
            abort(404, 'Task not found.');
        }

        if ($mentor->role === 'mentor') {
            if ((int) $task->mentor_id !== (int) $mentor->id) {
                abort(403, 'This task is assigned to another mentor.');
            }
            $this->assertMentorLinkedToMentee((int) $mentor->id, (int) $progress->user_id);
        } elseif ($mentor->role !== 'admin') {
            abort(403);
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

    public function pendingForMentor(int $mentorId, ?int $menteeId = null)
    {
        $taskIds = MenteeTask::query()
            ->where('mentor_id', $mentorId)
            ->when($menteeId, fn ($q) => $q->where('mentee_id', $menteeId))
            ->pluck('id');

        return StudentCurriculumProgress::query()
            ->with('user:id,name,email,avatar_url')
            ->where('item_type', MenteeTask::ITEM_TYPE)
            ->where('submission_status', 'submitted')
            ->whereIn('item_id', $taskIds)
            ->when($menteeId, fn ($q) => $q->where('user_id', $menteeId))
            ->latest('updated_at');
    }

    public function assertCanManage(User $actor, MenteeTask $task): void
    {
        if ($actor->role === 'admin') {
            return;
        }

        if ($actor->role === 'mentor' && (int) $task->mentor_id === (int) $actor->id) {
            $this->assertMentorLinkedToMentee((int) $actor->id, (int) $task->mentee_id);

            return;
        }

        abort(403, 'You cannot manage this task.');
    }

    public function assertMentorLinkedToMentee(int $mentorId, int $menteeId): void
    {
        $linked = MenteeEnrollment::where('mentor_id', $mentorId)->where('mentee_id', $menteeId)->exists()
            || User::where('id', $menteeId)->where('role', 'mentee')->where('assigned_mentor_id', $mentorId)->exists()
            || EducationStream::where('mentor_id', $mentorId)->where('mentee_id', $menteeId)->exists();

        if (! $linked) {
            throw new InvalidArgumentException('Selected mentor is not linked to this mentee.');
        }
    }

    public function validateAttachmentFiles(Request $request): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        $request->validate([
            'attachments'   => 'array',
            'attachments.*' => 'file|mimes:'.implode(',', MenteeTask::ALLOWED_ATTACHMENT_MIMES).'|max:'.MenteeTask::ATTACHMENT_MAX_KB,
        ]);
    }

    public function processUploadedAttachments(Request $request, array $existing = []): array
    {
        $attachments = $existing;

        if (! $request->hasFile('attachments')) {
            return $attachments;
        }

        foreach ($request->file('attachments') as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('mentee-tasks', 'public');
            $attachments[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'url'  => MenteeTask::buildAttachmentUrl($path),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $attachments;
    }

    public function deleteStoredAttachments(array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $path = $attachment['path'] ?? null;
            if (! is_string($path) || $path === '') {
                $url = $attachment['url'] ?? '';
                $path = $url !== '' ? MenteeTask::resolveAttachmentPathFromUrl($url) : null;
            }

            if (is_string($path) && $path !== '') {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
