<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
  protected $table = 'marks';
  protected $fillable = [
    'exam_id',
    'exam_student_id',
    'exam_subject_id',
    'total_marks'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class, 'exam_subject_id');
  }

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }
}
