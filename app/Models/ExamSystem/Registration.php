<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
  protected $table = 'exam_registrations';

  protected $fillable = [
    'erp_student_id',
    'exam_session_id',
    'program_type',
    'is_backlog',
    'status',
    'registered_at',
  ];

  protected $casts = [
    'is_backlog' => 'boolean',
    'registered_at' => 'datetime',
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'erp_student_id');
  }

  public function examSession(): BelongsTo
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function seatingAllocation()
  {
    return $this->hasOne(ExamSeatingArrangement::class, 'erp_student_id', 'erp_student_id')
      ->where('exam_session_id', $this->exam_session_id);
  }

  public function dummyNumber()
  {
    return $this->hasOne(ExamDummyNumber::class, 'erp_student_id', 'erp_student_id')
      ->where('exam_session_id', $this->exam_session_id);
  }

  public function subjects()
  {
    return $this->hasManyThrough(
      ExamSubjectMaster::class,
      ExamSubjectEntry::class,
      'exam_session_id',
      'erp_subject_id',
      'exam_session_id',
      'erp_subject_id'
    );
  }

  public function examSubjects()
  {
    return $this->hasMany(ExamSubjectEntry::class, 'exam_session_id', 'exam_session_id');
  }

  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeRejected($query)
  {
    return $query->where('status', 'rejected');
  }

  public function scopeBacklog($query)
  {
    return $query->where('is_backlog', true);
  }
}
