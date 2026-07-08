<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class CurriculumTask extends Model
{
    protected $fillable = [
        'week_id', 'mentee_id', 'plan_id', 'title', 'description', 'type', 'order_index',
        'estimated_minutes', 'is_required', 'is_active', 'attachments', 'submission_type',
    ];
 
    protected $casts = [
        'is_required'  => 'boolean',
        'is_active'    => 'boolean',
        'attachments'  => 'array',
    ];
 
    const TYPES = [
        'task'       => 'Task',
        'reading'    => 'Reading',
        'video'      => 'Video',
        'project'    => 'Project',
        'quiz'       => 'Quiz',
        'reflection' => 'Reflection',
    ];
 
    const SUBMISSION_TYPES = [
        'none'  => 'No Submission',
        'text'  => 'Text',
        'file'  => 'File Upload',
        'link'  => 'URL Link',
        'pdf'   => 'PDF',
        'video' => 'Video',
    ];
 
    const TYPE_ICONS = [
        'task'       => '✅',
        'reading'    => '📖',
        'video'      => '🎬',
        'project'    => '🚀',
        'quiz'       => '❓',
        'reflection' => '💭',
    ];

    /** Allowed attachment extensions for task create/update (multipart `attachments[]`). */
    const ALLOWED_ATTACHMENT_MIMES = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'heic', 'heif',
        'pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt',
        'mp4', 'mov', 'avi', 'webm', 'mpeg',
    ];

    const ATTACHMENT_MAX_KB = 10240; // 10MB

    public static function buildAttachmentUrl(string $filePath): string
    {
        return url('/api/v1/media/curriculum-tasks/' . basename($filePath));
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

        if (str_contains($path, '/api/v1/media/curriculum-tasks/')) {
            $filename = basename($path);
            return $filename !== '' ? 'curriculum-tasks/' . $filename : null;
        }

        if (str_contains($path, '/storage/curriculum-tasks/')) {
            $relative = ltrim(str_replace('/storage/', '', $path), '/');
            return $relative !== '' ? $relative : null;
        }

        return null;
    }
 
    public function week(): BelongsTo
    {
        return $this->belongsTo(CurriculumWeek::class, 'week_id');
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function getAttachmentsAttribute($value): array
    {
        $attachments = is_array($value) ? $value : (json_decode((string) $value, true) ?: []);
        if (! is_array($attachments)) {
            return [];
        }

        return array_map(function ($attachment) {
            if (! is_array($attachment)) {
                return $attachment;
            }

            $url = $attachment['url'] ?? '';
            $path = is_string($url) ? self::resolveAttachmentPathFromUrl($url) : null;

            if ($path) {
                $attachment['url'] = self::buildAttachmentUrl($path);
            }

            return $attachment;
        }, $attachments);
    }
 
    public function getProgressForUser(int $userId): ?StudentCurriculumProgress
    {
        return StudentCurriculumProgress::where('user_id', $userId)
            ->where('item_type', 'task')
            ->where('item_id', $this->id)
            ->first();
    }
 
    public function isCompletedByUser(int $userId): bool
    {
        return StudentCurriculumProgress::where('user_id', $userId)
            ->where('item_type', 'task')
            ->where('item_id', $this->id)
            ->where('is_completed', true)
            ->exists();
    }
}