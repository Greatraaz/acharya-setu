<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorVideoFile extends Model
{
    protected $fillable = [
        'mentor_video_id',
        'video_url',
        'file_name',
        'sort_order',
    ];

    public function mentorVideo(): BelongsTo
    {
        return $this->belongsTo(MentorVideo::class);
    }
}
