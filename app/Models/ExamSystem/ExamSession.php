<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
  protected $table = 'exam_sessions';

  protected $fillable = [
    'name',
    'academic_year',
    'semester',
    'program_type',
    'regulation_id',
    'start_date',
    'end_date',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
  ];

  public function regulation()
  {
    return $this->belongsTo(\App\Models\ExamSystem\ProgramRegulation::class, 'regulation_id');
  }

  public function registrations()
  {
    return $this->hasMany(Registration::class, 'exam_session_id');
  }

  public function examSubjects()
  {
    return $this->hasMany(ExamSubjectEntry::class, 'exam_session_id');
  }

  public function seatingArrangements()
  {
    return $this->hasMany(ExamSeatingArrangement::class, 'exam_session_id');
  }

  public function dummyNumbers()
  {
    return $this->hasMany(ExamDummyNumber::class, 'exam_session_id');
  }
}
