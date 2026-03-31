<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
  protected $fillable = [
    'name',
    'capacity',
    'location',
  ];

  public function examAttendances()
  {
    return $this->hasMany(\App\Models\ExamSystem\ExamAttendance::class, 'room_id');
  }
}
