<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentExitRecord extends Model
{
  protected $fillable = [
    'exam_student_id',
    'exit_type',
    'exit_date',
    'certificate_no'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }
}
