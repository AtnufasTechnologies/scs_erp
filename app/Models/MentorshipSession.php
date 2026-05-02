<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipSession extends Model
{
  protected $fillable = [
    'mentorship_group_id',
    'title',
    'agenda',
    'minutes',
    'session_date',
    'start_time',
    'end_time',
    'mode',
    'status',
  ];

  protected $casts = [
    'session_date' => 'date',
  ];

  public function group()
  {
    return $this->belongsTo(MentorshipGroup::class, 'mentorship_group_id');
  }

  public function attendances()
  {
    return $this->hasMany(MentorshipSessionAttendance::class, 'mentorship_session_id');
  }
}
