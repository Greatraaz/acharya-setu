<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Quiz extends Model
{
    protected $fillable = ['title', 'description', 'time_limit', 'pass_score', 'is_published', 'show_results', 'created_by'];
    protected $casts    = ['is_published' => 'boolean', 'show_results' => 'boolean'];
 
    public function creator()   { return $this->belongsTo(User::class, 'created_by'); }
    public function questions() { return $this->hasMany(QuizQuestion::class)->orderBy('order'); }
    public function attempts()  { return $this->hasMany(QuizAttempt::class); }
 
    public function userAttempt(User $user)
    {
        return $this->attempts()->where('user_id', $user->id)->latest()->first();
    }
 
    public function getTotalMarksAttribute(): int
    {
        return $this->questions()->sum('marks');
    }
}
