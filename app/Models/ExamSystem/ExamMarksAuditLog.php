<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamMarksAuditLog extends Model
{
  protected $table = 'exam_marks_audit_logs';

  protected $fillable = [
    'exam_marks_entry_id',
    'exam_session_id',
    'erp_student_id',
    'erp_subject_id',
    'old_marks',
    'new_marks',
    'action',
    'changed_by',
    'mac_address',
    'remarks',
  ];

  protected $casts = [
    'old_marks' => 'decimal:2',
    'new_marks' => 'decimal:2',
  ];

  public function marksEntry()
  {
    return $this->belongsTo(ExamMarksEntry::class, 'exam_marks_entry_id');
  }

  public function examSession()
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function student()
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'erp_student_id');
  }

  public function subjectMaster()
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'erp_subject_id', 'erp_subject_id');
  }

  public function changedByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'changed_by');
  }
}
