<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'id', 'title', 'month', 'description', 'questions',
    ];

    protected $casts = [
        'questions' => 'array',
        'month'     => 'integer',
    ];
}
