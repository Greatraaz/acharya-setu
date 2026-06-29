<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class QuizAttempt extends Model
{
    protected $fillable = ['quiz_id', 'user_id', 'score', 'total_marks', 'percentage', 'passed', 'started_at', 'completed_at'];
    protected $casts    = ['passed' => 'boolean', 'started_at' => 'datetime', 'completed_at' => 'datetime'];
 
    public function quiz()    { return $this->belongsTo(Quiz::class); }
    public function user()    { return $this->belongsTo(User::class); }
    public function answers() { return $this->hasMany(QuizAnswer::class, 'attempt_id'); }
}
