<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamRoomGeneratedSeat extends Model
{
  protected $fillable = [
    'exam_operational_session_id',
    'room_id',
    'row_no',
    'column_no',
    'seat_number',
    'seat_code',
    'is_allocated',
  ];

  public function session()
  {
    return $this->belongsTo(ExamOperationalSession::class, 'exam_operational_session_id');
  }

  public function room()
  {
    return $this->belongsTo(Room::class, 'room_id');
  }
}
