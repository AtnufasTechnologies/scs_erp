<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamDummyNumber extends Model
{
  protected $table = 'exam_dummy_numbers';

  protected $fillable = [
    'exam_session_id',
    'erp_student_id',
    'dummy_number',
  ];

  public function examSession()
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function student()
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'erp_student_id');
  }
}
