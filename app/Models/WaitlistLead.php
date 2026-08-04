<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaitlistLead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'who',
    ];
}
