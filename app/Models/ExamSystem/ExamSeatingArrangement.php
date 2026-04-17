<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamSeatingArrangement extends Model
{
  protected $table = 'exam_seating_arrangements';

  protected $fillable = [
    'exam_session_id',
    'room_no',
    'seat_no',
    'erp_student_id',
  ];

  public function examSession()
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function student()
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'erp_student_id');
  }
}
