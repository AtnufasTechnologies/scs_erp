<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamMarksWeightage extends Model
{
  protected $table = 'exam_marks_weightages';

  protected $fillable = [
    'exam_session_id',
    'erp_subject_id',
    'component',
    'weightage',
  ];

  protected $casts = [
    'weightage' => 'decimal:2',
  ];

  public function examSession()
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function subjectMaster()
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'erp_subject_id', 'erp_subject_id');
  }
}
