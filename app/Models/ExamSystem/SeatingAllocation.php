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
  ];

  public function examSchedule()
  {
    return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
  }

  public function room()
  {
    return $this->belongsTo(\App\Models\RoomMaster::class, 'room_id');
  }

  public function examStudent()
  {
    return $this->belongsTo(ExamStudent::class, 'exam_student_id');
  }
}
