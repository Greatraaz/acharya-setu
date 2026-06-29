<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    protected $fillable = [
        'mentor_id',
        'title',
        'description',
        'video_url',
        'thumbnail_url',
        'category',
        'duration',
        'is_premium',
        'views',
        'is_active',
    ];

    protected $casts = [
        'is_premium' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}