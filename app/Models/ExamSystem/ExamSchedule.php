<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
  protected $table = 'exam_schedules';

  protected $fillable = [
    'exam_id',
    'exam_subject_id',
    'exam_date',
    'start_time',
    'end_time',
    'room_id',
  ];

  protected $casts = [
    'exam_date' => 'date',
  ];

  public function exam()
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function room()
  {
    return $this->belongsTo(\App\Models\RoomMaster::class, 'room_id');
  }

  public function subject()
  {
    return $this->belongsTo(\App\Models\Subject::class, 'exam_subject_id');
  }

  public function seatingAllocations()
  {
    return $this->hasMany(SeatingAllocation::class, 'exam_schedule_id');
  }
}
