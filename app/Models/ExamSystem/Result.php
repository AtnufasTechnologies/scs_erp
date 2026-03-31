<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
  protected $fillable = [
    'exam_id',
    'exam_student_id',
    'sgpa',
    'cgpa',
    'result_status'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function scopePending($query)
  {
    return $query->where('result_status', 'pending');
  }

  public function scopeApproved($query)
  {
    return $query->where('result_status', 'approved');
  }
}
