<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotionHistory extends Model
{
  protected $table = 'student_promotion_histories';

  protected $fillable = [
    'exam_student_id',
    'exam_session_id',
    'promotion_id',
    'semester',
    'result_status',
    'promotion_status',
    'sgpa',
    'percentage',
    'total_credits_earned',
    'earned_credits',
    'transferred_credits',
    'pending_backlogs',
    'backlog_subjects',
    'subjects_snapshot',
  ];

  protected $casts = [
    'sgpa' => 'decimal:2',
    'percentage' => 'decimal:2',
    'subjects_snapshot' => 'array',
    'backlog_subjects' => 'array',
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function examSession(): BelongsTo
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function promotion(): BelongsTo
  {
    return $this->belongsTo(Promotion::class, 'promotion_id');
  }
}
