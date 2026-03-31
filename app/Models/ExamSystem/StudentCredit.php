<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCredit extends Model
{
  protected $table = 'student_credits';
  protected $fillable = [
    'exam_student_id',
    'exam_subject_id',
    'credits_earned'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class, 'exam_subject_id');
  }
}
