<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipGroup extends Model
{
  protected $fillable = [
    'faculty_id',
    'name',
    'description',
    'academic_year',
    'semester',
    'status',
  ];

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  public function groupStudents()
  {
    return $this->hasMany(MentorshipGroupStudent::class, 'mentorship_group_id');
  }

  public function students()
  {
    return $this->belongsToMany(StudentMaster::class, 'mentorship_group_students', 'mentorship_group_id', 'student_id')
      ->withPivot('notes')
      ->withTimestamps();
  }

  public function sessions()
  {
    return $this->hasMany(MentorshipSession::class, 'mentorship_group_id');
  }

  public function assignments()
  {
    return $this->hasMany(MentorshipAssignment::class, 'mentorship_group_id');
  }

  public function studentNotes()
  {
    return $this->hasMany(MentorshipStudentNote::class, 'mentorship_group_id');
  }
}
