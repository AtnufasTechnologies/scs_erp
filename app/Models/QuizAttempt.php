<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
  use HasFactory;

  protected $fillable = [
    'quiz_id',
    'student_id',
    'attempt_no',
    'status',
    'raw_score',
    'total_questions',
    'score',
    'started_at',
    'expires_at',
    'submitted_at',
    'submitted_by_timeout',
  ];

  protected $casts = [
    'started_at' => 'datetime',
    'expires_at' => 'datetime',
    'submitted_at' => 'datetime',
    'submitted_by_timeout' => 'boolean',
    'score' => 'decimal:2',
  ];

  public function quiz()
  {
    return $this->belongsTo(Quiz::class);
  }

  public function answers()
  {
    return $this->hasMany(QuizAttemptAnswer::class);
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
