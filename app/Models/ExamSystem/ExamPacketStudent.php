<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class ExamPacketStudent extends Model
{
  protected $table = 'exam_packet_students';

  protected $fillable = [
    'exam_packet_id',
    'erp_student_id',
    'dummy_number',
  ];

  public function packet()
  {
    return $this->belongsTo(ExamPacket::class, 'exam_packet_id');
  }

  public function student()
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'erp_student_id');
  }
}
