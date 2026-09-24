<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamOperationalSession extends Model
{
  protected $fillable = [
    'exam_date',
    'start_time',
    'end_time',
    'session_label',
    'status',
  ];

  protected $casts = [
    'exam_date' => 'date',
  ];

  public function generatedSeats()
  {
    return $this->hasMany(ExamRoomGeneratedSeat::class, 'exam_operational_session_id');
  }
}
