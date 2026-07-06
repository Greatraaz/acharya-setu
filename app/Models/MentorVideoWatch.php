<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorVideoWatch extends Model
{
    protected $fillable = [
        'mentee_id',
        'mentor_video_file_id',
        'watched_at',
    ];

    protected $casts = [
        'watched_at' => 'datetime',
    ];

    public function mentee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }

    public function videoFile(): BelongsTo
    {
        return $this->belongsTo(MentorVideoFile::class, 'mentor_video_file_id');
    }
}
