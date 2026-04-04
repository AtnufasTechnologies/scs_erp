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
    'exam_session_id',
    'semester',
    'credits',
    'status',
    'attempt_number',
    'max_attempts',
    'previous_marks',
    'previous_grade',
    'remarks',
    'registered_at',
    'cleared_at',
    'cleared_exam_session_id',
    'cleared_marks',
    'cleared_grade',
  ];

  protected $casts = [
    'previous_marks' => 'decimal:2',
    'cleared_marks' => 'decimal:2',
    'attempt_number' => 'integer',
    'credits' => 'integer',
    'registered_at' => 'datetime',
    'cleared_at' => 'datetime',
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'exam_subject_id');
  }

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function examSession(): BelongsTo
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeCleared($query)
  {
    return $query->where('status', 'cleared');
  }

  public function scopeRegistered($query)
  {
    return $query->where('status', 'registered');
  }
}
