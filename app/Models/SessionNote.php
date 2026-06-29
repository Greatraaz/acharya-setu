<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
 
class SessionNote extends Model
{
    protected $fillable = [
        'session_id', 'author_id', 'type', 'content', 'resource_url', 'is_shared',
    ];
 
    protected $casts = ['is_shared' => 'boolean'];
 
    public function session(): BelongsTo
    {
        return $this->belongsTo(ConsultationSession::class, 'session_id');
    }
 
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
