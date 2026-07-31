<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $table = 'job_applications';

    protected $fillable = [
        'user_id',
        'jobId',
        'fullname',
        'jobRole',
        'qualification',
        'specification',
        'skills',
        'experience',
        'lastJob',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobListing::class, 'jobId');
    }
}
