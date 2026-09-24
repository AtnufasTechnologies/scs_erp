<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class SeatingAllocation extends Model
{
  protected $fillable = [
    'exam_schedule_id',
    'room_id',
    'exam_student_id',
    'seat_no',
    'status',
  ];

  public function examSchedule()
  {
    return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
  }

  public function room()
  {
    return $this->belongsTo(Room::class, 'room_id');
  }

  public function examStudent()
  {
    return $this->belongsTo(ExamStudent::class, 'exam_student_id');
  }

  // Backward-compatible alias for older views expecting $allocation->exam
  public function exam()
  {
    return $this->examSchedule();
  }

  // Backward-compatible alias for older views expecting $allocation->student
  public function student()
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'exam_student_id');
  }
}
