<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipStudentNote extends Model
{
  protected $fillable = [
    'mentorship_group_id',
    'faculty_id',
    'student_id',
    'note',
    'category',
    'noted_on',
  ];

  protected $casts = [
    'noted_on' => 'date',
  ];

  public function group()
  {
    return $this->belongsTo(MentorshipGroup::class, 'mentorship_group_id');
  }

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
