<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipSessionAttendance extends Model
{
  protected $fillable = [
    'mentorship_session_id',
    'student_id',
    'status',
    'remarks',
  ];

  public function session()
  {
    return $this->belongsTo(MentorshipSession::class, 'mentorship_session_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
