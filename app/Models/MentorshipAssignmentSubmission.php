<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipAssignmentSubmission extends Model
{
  protected $fillable = [
    'mentorship_assignment_id',
    'student_id',
    'response',
    'submission_path',
    'marks_obtained',
    'feedback',
    'status',
    'submitted_at',
  ];

  protected $casts = [
    'submitted_at' => 'datetime',
  ];

  public function assignment()
  {
    return $this->belongsTo(MentorshipAssignment::class, 'mentorship_assignment_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
