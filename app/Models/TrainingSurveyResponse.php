<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSurveyResponse extends Model
{
  use HasFactory;

  protected $fillable = [
    'training_attempt_id',
    'training_survey_question_id',
    'training_survey_option_id',
    'awarded_score',
  ];

  public function attempt()
  {
    return $this->belongsTo(TrainingAttempt::class, 'training_attempt_id');
  }

  public function question()
  {
    return $this->belongsTo(TrainingSurveyQuestion::class, 'training_survey_question_id');
  }

  public function option()
  {
    return $this->belongsTo(TrainingSurveyOption::class, 'training_survey_option_id');
  }
}
