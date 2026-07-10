<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttemptPermission extends Model
{
  use HasFactory;

  protected $fillable = [
    'quiz_id',
    'student_id',
    'max_attempts',
    'allowed_by',
  ];

  public function quiz()
  {
    return $this->belongsTo(Quiz::class);
  }
}
