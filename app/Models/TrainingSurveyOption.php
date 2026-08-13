<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSurveyOption extends Model
{
  use HasFactory;

  protected $fillable = [
    'training_survey_question_id',
    'option_text',
    'score',
    'option_order',
  ];

  public function question()
  {
    return $this->belongsTo(TrainingSurveyQuestion::class, 'training_survey_question_id');
  }
}
