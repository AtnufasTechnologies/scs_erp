<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InterMark extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'internal_marks';

  protected $fillable = [
    'student_id',
    'course_id',
    'internal_mark',
    'semester',
    'exam_setting_id',
    'entry_id',
    'academic_year',
    'semester_type',
    'is_deleted',
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id', 'id');
  }

  public function course()
  {
    return $this->belongsTo(ProgramCourseMaster::class, 'course_id', 'id');
  }

  public function semester()
  {
    return $this->belongsTo(Semester::class, 'SEMESTER', 'id');
  }
}
