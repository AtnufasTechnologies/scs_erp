<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamMarksLock extends Model
{
  protected $table = 'exam_marks_locks';

  protected $fillable = [
    'exam_session_id',
    'erp_subject_id',
    'is_locked',
    'locked_by',
    'locked_at',
    'unlocked_by',
    'unlocked_at',
    'remarks',
  ];

  protected $casts = [
    'is_locked' => 'boolean',
    'locked_at' => 'datetime',
    'unlocked_at' => 'datetime',
  ];

  public function examSession()
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function subjectMaster()
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'erp_subject_id', 'erp_subject_id');
  }

  public function lockedByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'locked_by');
  }

  public function unlockedByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'unlocked_by');
  }

  /**
   * Check if marks are locked for a given session and subject.
   */
  public static function isLocked($examSessionId, $erpSubjectId): bool
  {
    return static::where('exam_session_id', $examSessionId)
      ->where('erp_subject_id', $erpSubjectId)
      ->where('is_locked', true)
      ->exists();
  }
}
