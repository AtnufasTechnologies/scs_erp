<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamSubjectEntry extends Model
{
  protected $table = 'exam_subjects';

  protected $fillable = [
    'erp_subject_id',
    'exam_session_id',
    'is_backlog',
  ];

  protected $casts = [
    'is_backlog' => 'boolean',
  ];

  public function examSession()
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function subject()
  {
    return $this->belongsTo(\App\Models\Subject::class, 'erp_subject_id');
  }

  public function master()
  {
    return $this->hasOne(ExamSubjectMaster::class, 'erp_subject_id', 'erp_subject_id');
  }
}
