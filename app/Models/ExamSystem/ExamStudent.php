<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamStudent extends Model
{
  protected $table = 'exam_students';

  protected $fillable = [
    'erp_student_id',
    'program_id',
    'enrollment_no',
    'status',
  ];

  public function student()
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'erp_student_id');
  }

  public function program()
  {
    return $this->belongsTo(\App\Models\StudentProgram::class, 'program_id');
  }

  public function seatingAllocations()
  {
    return $this->hasMany(SeatingAllocation::class, 'exam_student_id');
  }

  public function dummyNumbers()
  {
    return $this->hasMany(DummyNumber::class, 'exam_student_id');
  }

  public function registrations()
  {
    return $this->hasMany(Registration::class, 'erp_student_id', 'erp_student_id');
  }
}
