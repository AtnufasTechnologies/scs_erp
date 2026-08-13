<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeachingGroupItem extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'subject_id',
    'allocation_group_id',
    'curriculum_row_id',
    'course_id',
    'batch_id',
    'semester_id',
    'student_program_id',
    'program_type',
    'delivery_type',
    'offering_dept_id',
    'faculty_id',
    'room_no',
    'created_by',
  ];

  public function course()
  {
    return $this->belongsTo(ProgramCourseMaster::class, 'course_id');
  }

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }
}
