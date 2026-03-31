<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
  protected $table = 'exam_students';
  protected $fillable = [
    'erp_student_id',
    'program_id',
    'enrollment_no',
    'status'
  ];

  public function registrations(): HasMany
  {
    return $this->hasMany(Registration::class, 'exam_student_id');
  }

  public function backlogs(): HasMany
  {
    return $this->hasMany(Backlog::class, 'exam_student_id');
  }

  public function credits(): HasMany
  {
    return $this->hasMany(StudentCredit::class, 'exam_student_id');
  }
}
