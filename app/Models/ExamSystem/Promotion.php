<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
  protected $fillable = [
    'exam_student_id',
    'from_exam_id',
    'to_exam_id',
    'promoted_at'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function fromExam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'from_exam_id');
  }

  public function toExam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'to_exam_id');
  }
}
