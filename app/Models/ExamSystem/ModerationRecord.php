<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationRecord extends Model
{
  protected $fillable = [
    'exam_session_id',
    'erp_student_id',
    'erp_subject_id',
    'evaluator_marks',
    'moderator_marks',
    'adjusted_marks',
    'difference',
    'moderator_id',
    'adjusted_by',
    'status',
    'remarks',
    'exam_marks_entry_id',
  ];

  protected $casts = [
    'evaluator_marks' => 'decimal:2',
    'moderator_marks' => 'decimal:2',
    'adjusted_marks' => 'decimal:2',
    'difference' => 'decimal:2',
  ];

  public function examSession(): BelongsTo
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function student(): BelongsTo
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'erp_student_id', 'erp_student_id');
  }

  public function subjectMaster(): BelongsTo
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'erp_subject_id', 'erp_subject_id');
  }

  public function moderator(): BelongsTo
  {
    return $this->belongsTo(\App\Models\Faculty::class, 'moderator_id');
  }

  public function adjustedByUser(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'adjusted_by');
  }

  public function marksEntry(): BelongsTo
  {
    return $this->belongsTo(ExamMarksEntry::class, 'exam_marks_entry_id');
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeModerated($query)
  {
    return $query->where('status', 'moderated');
  }

  public function scopeFinalized($query)
  {
    return $query->where('status', 'finalized');
  }

  public function scopeFlagged($query, $threshold = 10)
  {
    return $query->whereNotNull('difference')->where('difference', '>', $threshold);
  }
}
