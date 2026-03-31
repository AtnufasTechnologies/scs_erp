<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Backlog extends Model
{
  protected $fillable = [
    'exam_student_id',
    'exam_subject_id',
    'exam_id',
    'status'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(Subject::class, 'exam_subject_id');
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }
}
