<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorAvailability extends Model
{
    protected $fillable = [
        'mentor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}