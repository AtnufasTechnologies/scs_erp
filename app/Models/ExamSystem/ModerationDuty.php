<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationDuty extends Model
{
  protected $fillable = [
    'exam_id',
    'faculty_id',
    'subject_id',
    'moderation_type',
    'status'
  ];

  public function faculty(): BelongsTo
  {
    return $this->belongsTo(\App\Models\Faculty::class, 'faculty_id');
  }

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'subject_id');
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
