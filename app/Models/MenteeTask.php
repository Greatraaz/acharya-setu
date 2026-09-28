<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenteeTask extends Model
{
    public const ITEM_TYPE = 'mentee_task';

    protected $fillable = [
        'mentee_id',
        'mentor_id',
        'created_by',
        'title',
        'description',
        'type',
        'submission_type',
        'attachments',
        'estimated_minutes',
        'order_index',
        'is_required',
        'is_active',
        'due_at',
    ];

    protected $casts = [
        'attachments'       => 'array',
        'is_required'       => 'boolean',
        'is_active'         => 'boolean',
        'estimated_minutes' => 'integer',
        'order_index'       => 'integer',
        'due_at'            => 'datetime',
    ];

    public const TYPES = CurriculumTask::TYPES;

    public const SUBMISSION_TYPES = CurriculumTask::SUBMISSION_TYPES;

    public const TYPE_ICONS = CurriculumTask::TYPE_ICONS;

    public const ALLOWED_ATTACHMENT_MIMES = CurriculumTask::ALLOWED_ATTACHMENT_MIMES;

    public const ATTACHMENT_MAX_KB = CurriculumTask::ATTACHMENT_MAX_KB;

    public static function buildAttachmentUrl(string $filePath): string
    {
        return url('/api/v1/media/mentee-tasks/'.basename($filePath));
    }

    public static function resolveAttachmentPathFromUrl(string $url): ?string
    {
        if (trim($url) === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        if ($path === '') {
            return null;
        }

        if (str_contains($path, '/api/v1/media/mentee-tasks/')) {
            $filename = basename($path);

            return $filename !== '' ? 'mentee-tasks/'.$filename : null;
        }

        if (str_contains($path, '/storage/mentee-tasks/')) {
            $relative = ltrim(str_replace('/storage/', '', $path), '/');

            return $relative !== '' ? $relative : null;
        }

        return null;
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAttachmentsAttribute($value): array
    {
        $attachments = is_array($value) ? $value : (json_decode((string) $value, true) ?: []);
        if (! is_array($attachments)) {
            return [];
        }

        return array_values(array_map(function ($attachment) {
            if (! is_array($attachment)) {
                return $attachment;
            }

            $path = $attachment['path'] ?? null;
            if (! is_string($path) || $path === '') {
                $url = $attachment['url'] ?? '';
                $path = is_string($url) ? self::resolveAttachmentPathFromUrl($url) : null;
            }

            if (is_string($path) && $path !== '') {
                $attachment['path'] = $path;
                $attachment['url'] = self::buildAttachmentUrl($path);
            }

            if (empty($attachment['name']) && is_string($path)) {
                $attachment['name'] = basename($path);
            }

            return $attachment;
        }, $attachments));
    }

    public function getProgressForUser(?int $userId = null): ?StudentCurriculumProgress
    {
        $userId = $userId ?? (int) $this->mentee_id;

        return StudentCurriculumProgress::query()
            ->where('user_id', $userId)
            ->where('item_type', self::ITEM_TYPE)
            ->where('item_id', $this->id)
            ->first();
    }

    public function isCompletedByUser(?int $userId = null): bool
    {
        $userId = $userId ?? (int) $this->mentee_id;

        return StudentCurriculumProgress::query()
            ->where('user_id', $userId)
            ->where('item_type', self::ITEM_TYPE)
            ->where('item_id', $this->id)
            ->where('is_completed', true)
            ->exists();
    }

    public function uiStatus(?StudentCurriculumProgress $progress = null): string
    {
        $progress ??= $this->getProgressForUser();

        if (! $progress) {
            return 'pending';
        }

        if ($progress->is_completed || $progress->submission_status === 'approved') {
            return 'completed';
        }

        if ($progress->submission_status === 'submitted') {
            return 'awaiting_review';
        }

        if ($progress->submission_status === 'rejected') {
            return 'rejected';
        }

        return 'in_progress';
    }

    public function toPublicArray(?StudentCurriculumProgress $progress = null): array
    {
        $progress ??= $this->getProgressForUser();

        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'description'        => $this->description,
            'type'               => $this->type,
            'type_label'         => self::TYPES[$this->type] ?? ucfirst((string) $this->type),
            'type_icon'          => self::TYPE_ICONS[$this->type] ?? '✅',
            'submission_type'    => $this->submission_type,
            'submission_label'   => self::SUBMISSION_TYPES[$this->submission_type] ?? $this->submission_type,
            'attachments'        => $this->attachments ?? [],
            'estimated_minutes'  => $this->estimated_minutes,
            'is_required'        => (bool) $this->is_required,
            'is_active'          => (bool) $this->is_active,
            'due_at'             => $this->due_at?->toDateTimeString(),
            'mentee_id'          => (int) $this->mentee_id,
            'mentor_id'          => (int) $this->mentor_id,
            'created_by'         => (int) $this->created_by,
            'mentee'             => $this->mentee ? [
                'id'         => $this->mentee->id,
                'name'       => $this->mentee->name,
                'email'      => $this->mentee->email,
                'avatar_url' => $this->mentee->avatar_url,
            ] : null,
            'mentor'             => $this->mentor ? [
                'id'         => $this->mentor->id,
                'name'       => $this->mentor->name,
                'email'      => $this->mentor->email,
                'avatar_url' => $this->mentor->avatar_url,
            ] : null,
            'ui_status'          => $this->uiStatus($progress),
            'progress'           => $progress ? [
                'id'                => $progress->id,
                'is_completed'      => (bool) $progress->is_completed,
                'submission_status' => $progress->submission_status,
                'submission_text'   => $progress->submission_text,
                'submission_url'    => $progress->submissionLink(),
                'mentor_feedback'   => $progress->mentor_feedback,
                'reviewed_at'       => $progress->reviewed_at?->toDateTimeString(),
                'completed_at'      => $progress->completed_at?->toDateTimeString(),
                'updated_at'        => $progress->updated_at?->toDateTimeString(),
            ] : null,
            'created_at'         => $this->created_at?->toDateTimeString(),
        ];
    }
}
