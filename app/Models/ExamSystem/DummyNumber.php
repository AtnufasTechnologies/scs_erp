<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DummyNumber extends Model
{
  protected $fillable = [
    'exam_id',
    'exam_student_id',
    'dummy_number',
    'locked',
  ];

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function examStudent(): BelongsTo
  {
    return $this->belongsTo(ExamStudent::class, 'exam_student_id');
  }

  /**
   * Alias: access the StudentMaster through ExamStudent
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(ExamStudent::class, 'exam_student_id');
  }
}
