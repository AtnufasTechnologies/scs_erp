<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class MalpracticeCase extends Model
{
  protected $fillable = [
    'exam_id',
    'student_id',
    'subject_id',
    'room_id',
    'remarks',
    'status',
    'reported_by',
    'reported_at',
  ];
}
