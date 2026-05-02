<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipGroupStudent extends Model
{
  protected $fillable = [
    'mentorship_group_id',
    'student_id',
    'notes',
  ];

  public function group()
  {
    return $this->belongsTo(MentorshipGroup::class, 'mentorship_group_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
