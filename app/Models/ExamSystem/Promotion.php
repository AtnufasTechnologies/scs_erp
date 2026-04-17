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
    'exam_session_id',
    'regulation_type',
    'promotion_status',
    'from_semester',
    'to_semester',
    'total_credits',
    'earned_credits',
    'transferred_credits',
    'pending_backlogs',
    'backlog_subjects',
    'reason',
    'promoted_at',
  ];

  protected $casts = [
    'promoted_at' => 'datetime',
    'backlog_subjects' => 'array',
  ];

  public function isPromotedWithBacklogs(): bool
  {
    return $this->promotion_status === 'promoted_with_backlogs';
  }

  public function isPromoted(): bool
  {
    return in_array($this->promotion_status, ['promoted', 'promoted_with_backlogs']);
  }

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

  public function examSession(): BelongsTo
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function history()
  {
    return $this->hasOne(StudentPromotionHistory::class, 'promotion_id');
  }
}
