<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhitePaperDownload extends Model
{
    protected $fillable = [
        'white_paper_id',
        'user_id',
        'full_name',
        'email',
        'phone',
        'organization',
        'role',
        'ip_address',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(WhitePaper::class, 'white_paper_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
