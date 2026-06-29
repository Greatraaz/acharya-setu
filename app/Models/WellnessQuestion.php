<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class WellnessQuestion extends Model
{
    protected $fillable = ['survey_id', 'question', 'type', 'options', 'order', 'required'];
    protected $casts    = ['options' => 'array', 'required' => 'boolean'];
 
    public function survey()  { return $this->belongsTo(WellnessSurvey::class, 'survey_id'); }
    public function answers() { return $this->hasMany(WellnessAnswer::class, 'question_id'); }
}
