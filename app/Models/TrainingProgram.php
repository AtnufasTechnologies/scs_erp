<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingProgram extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'description',
    'created_by',
    'is_active',
  ];

  public function targetRoles()
  {
    return $this->hasMany(TrainingTargetRole::class);
  }

  public function resources()
  {
    return $this->hasMany(TrainingResource::class);
  }

  public function surveyQuestions()
  {
    return $this->hasMany(TrainingSurveyQuestion::class)->orderBy('question_order');
  }

  public function attempts()
  {
    return $this->hasMany(TrainingAttempt::class);
  }
}
