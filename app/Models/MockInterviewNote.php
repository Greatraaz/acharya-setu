<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockInterviewNote extends Model
{
    protected $fillable = [
        'mock_interview_request_id',
        'author_id',
        'type',
        'content',
        'resource_url',
        'is_shared',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
    ];

    public function mockInterview(): BelongsTo
    {
        return $this->belongsTo(MockInterviewRequest::class, 'mock_interview_request_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
