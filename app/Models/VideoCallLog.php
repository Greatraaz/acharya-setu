<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class VideoCallLog extends Model
{
    use SoftDeletes;
 
    protected $fillable = [
        'host_id', 'participant_id',
        'channel_name', 'session_id', 'provider', 'call_type',
        'booking_id',
        'started_at', 'ended_at', 'duration_seconds',
        'status', 'end_reason',
        'host_rating', 'participant_rating',
        'host_notes', 'meta',
        'is_recorded', 'recording_url', 'recording_size_kb',
    ];
 
    protected $casts = [
        'started_at'       => 'datetime',
        'ended_at'         => 'datetime',
        'meta'             => 'array',
        'is_recorded'      => 'boolean',
        'duration_seconds' => 'integer',
        'host_rating'      => 'integer',
        'participant_rating' => 'integer',
    ];
 
    const PROVIDER_AGORA  = 'agora';
    const PROVIDER_ZOOM   = 'zoom';
    const PROVIDER_GOOGLE = 'google';
 
    const STATUS_INITIATED = 'initiated';
    const STATUS_ONGOING   = 'ongoing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_MISSED    = 'missed';
    const STATUS_FAILED    = 'failed';
    const STATUS_CANCELLED = 'cancelled';
 
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }
 
    public function participant()
    {
        return $this->belongsTo(User::class, 'participant_id');
    }
 
    public function booking()
    {
        return $this->belongsTo(ConsultationSession::class, 'booking_id');
    }
 
    public function participants()
    {
        return $this->hasMany(VideoCallParticipant::class, 'video_call_log_id');
    }
}