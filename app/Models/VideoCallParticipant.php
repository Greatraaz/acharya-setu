<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;

class VideoCallParticipant extends Model
{
    protected $table = 'video_call_participants';
 
    protected $fillable = [
        'video_call_log_id', 'user_id', 'display_name', 'role',
        'joined_at', 'left_at', 'duration_seconds',
        'mic_enabled', 'camera_enabled', 'meta',
    ];
 
    protected $casts = [
        'joined_at'       => 'datetime',
        'left_at'         => 'datetime',
        'meta'            => 'array',
        'mic_enabled'     => 'boolean',
        'camera_enabled'  => 'boolean',
        'duration_seconds' => 'integer',
    ];
 
    public function callLog()
    {
        return $this->belongsTo(VideoCallLog::class, 'video_call_log_id');
    }
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}