<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorshipAssignment extends Model
{
  protected $fillable = [
    'mentorship_group_id',
    'title',
    'description',
    'due_date',
    'max_marks',
    'status',
    'attachment_path',
  ];

  protected $casts = [
    'due_date' => 'date',
  ];

  public function group()
  {
    return $this->belongsTo(MentorshipGroup::class, 'mentorship_group_id');
  }

  public function submissions()
  {
    return $this->hasMany(MentorshipAssignmentSubmission::class, 'mentorship_assignment_id');
  }
}
