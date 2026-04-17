<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ResultLock extends Model
{
  protected $table = 'result_locks';

  protected $fillable = [
    'exam_session_id',
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

  public function lockedByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'locked_by');
  }

  public function unlockedByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'unlocked_by');
  }

  public static function isLocked($examSessionId): bool
  {
    return static::where('exam_session_id', $examSessionId)
      ->where('is_locked', true)
      ->exists();
  }
}
