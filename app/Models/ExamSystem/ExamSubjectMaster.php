<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamSubjectMaster extends Model
{
  protected $table = 'exam_subject_masters';

  protected $fillable = [
    'erp_subject_id',
    'program_id',
    'subject_code',
    'name',
    'credits',
    'type',
  ];

  public function subject()
  {
    return $this->belongsTo(\App\Models\Subject::class, 'erp_subject_id');
  }

  public function program()
  {
    return $this->belongsTo(\App\Models\StudentProgram::class, 'program_id');
  }
}
