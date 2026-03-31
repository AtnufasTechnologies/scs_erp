<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationDuty extends Model
{
  protected $fillable = [
    'exam_id',
    'faculty_id',
    'subject_id',
    'copies_assigned',
    'copies_evaluated',
    'status'
  ];

  public function faculty(): BelongsTo
  {
    return $this->belongsTo(FacultyProfile::class, 'faculty_id');
  }

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeCompleted($query)
  {
    return $query->where('status', 'completed');
  }
}
