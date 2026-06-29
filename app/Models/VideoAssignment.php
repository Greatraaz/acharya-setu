<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoAssignment extends Model
{
    protected $fillable = [
        'video_id',
        'mentee_id',
        'watched',
        'watched_at',
    ];

    protected $casts = [
        'watched'    => 'boolean',
        'watched_at' => 'datetime',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function mentee()
    {
        return $this->belongsTo(User::class, 'mentee_id');
    }
}