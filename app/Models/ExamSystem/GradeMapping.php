<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeMapping extends Model
{
  protected $fillable = [
    'program_id',
    'grade',
    'min_marks',
    'max_marks',
    'grade_point'
  ];

  public function program(): BelongsTo
  {
    return $this->belongsTo(Program::class);
  }
}
