<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class WellnessAnswer extends Model
{
    protected $fillable = ['response_id', 'question_id', 'answer'];
 
    public function response() { return $this->belongsTo(WellnessResponse::class, 'response_id'); }
    public function question() { return $this->belongsTo(WellnessQuestion::class, 'question_id'); }
}