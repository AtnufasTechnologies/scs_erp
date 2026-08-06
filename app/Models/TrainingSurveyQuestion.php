<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSurveyQuestion extends Model
{
  use HasFactory;

  protected $fillable = [
    'training_program_id',
    'question_text',
    'question_order',
    'is_required',
  ];

  public function trainingProgram()
  {
    return $this->belongsTo(TrainingProgram::class);
  }

  public function options()
  {
    return $this->hasMany(TrainingSurveyOption::class)->orderBy('option_order');
  }
}
