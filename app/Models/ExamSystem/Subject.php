<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
  protected $table = 'exam_subjects';
  protected $fillable = [
    'erp_subject_id',
    'program_id',
    'subject_code',
    'name',
    'credits',
    'type'
  ];

  public function program(): BelongsTo
  {
    return $this->belongsTo(Program::class);
  }

  public function examAttendances()
  {
    return $this->hasMany(\App\Models\ExamSystem\ExamAttendance::class, 'subject_id');
  }
}
