<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingAttempt extends Model
{
  use HasFactory;

  protected $fillable = [
    'training_program_id',
    'user_id',
    'completed_at',
    'total_score',
    'max_score',
  ];

  protected $casts = [
    'completed_at' => 'datetime',
  ];

  public function trainingProgram()
  {
    return $this->belongsTo(TrainingProgram::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function responses()
  {
    return $this->hasMany(TrainingSurveyResponse::class);
  }
}
