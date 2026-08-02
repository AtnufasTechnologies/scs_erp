<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeachingAssignmentFaculty extends Model
{
  use HasFactory;

  public const ROLE_PRIMARY = 'Primary';
  public const ROLE_CO_FACULTY = 'Co-Faculty';

  protected $fillable = [
    'teaching_assignment_id',
    'faculty_id',
    'teaching_role',
  ];

  public function teachingAssignment()
  {
    return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
  }

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }
}
