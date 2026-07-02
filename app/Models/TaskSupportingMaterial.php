<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskSupportingMaterial extends Model
{
    protected $fillable = [
        'week_id',
        'mentee_id',
        'mentor_id',
        'title',
        'type',
        'file_name',
        'file_path',
        'file_url',
        'mime_type',
        'file_size',
        'link',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'file_size'  => 'integer',
        'sort_order' => 'integer',
    ];

    const TYPES = [
        'pdf'       => 'PDF',
        'doc'       => 'Document',
        'image'     => 'Image',
        'videolink' => 'Video Link',
        'ppt'       => 'Presentation',
    ];

    const FILE_TYPES = ['pdf', 'doc', 'image', 'ppt'];

    public function week(): BelongsTo
    {
        return $this->belongsTo(CurriculumWeek::class, 'week_id');
    }

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function requiresFile(): bool
    {
        return in_array($this->type, self::FILE_TYPES, true);
    }
}
