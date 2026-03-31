<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamMarksEntry extends Model
{
  protected $table = 'exam_marks_entries';

  protected $fillable = [
    'exam_session_id',
    'erp_student_id',
    'erp_subject_id',
    'marks',
    'entered_by',
    'mac_address',
    'entered_at',
  ];

  protected $casts = [
    'marks' => 'decimal:2',
    'entered_at' => 'datetime',
  ];

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

  public function enteredByUser()
  {
    return $this->belongsTo(\App\Models\User::class, 'entered_by');
  }
}
