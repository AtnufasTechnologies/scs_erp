<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
  use HasFactory;

  protected $fillable = [
    'subject_id',
    'course_id',
    'syllabus_id',
    'batch_id',
    'semester_id',
    'faculty_id',
    'sup_cia_component_id',
    'cia_group_id',
    'title',
    'total_marks',
    'open_at',
    'close_at',
    'shuffle_questions',
    'shuffle_options',
    'time_limit_minutes',
    'is_published',
    'created_by',
  ];

  protected $casts = [
    'open_at' => 'datetime',
    'close_at' => 'datetime',
    'shuffle_questions' => 'boolean',
    'shuffle_options' => 'boolean',
    'time_limit_minutes' => 'integer',
    'is_published' => 'boolean',
    'total_marks' => 'decimal:2',
  ];

  public function questions()
  {
    return $this->hasMany(QuizQuestion::class)->orderBy('position');
  }

  public function attempts()
  {
    return $this->hasMany(QuizAttempt::class);
  }

  public function attemptPermissions()
  {
    return $this->hasMany(QuizAttemptPermission::class);
  }

  public function ciaComponent()
  {
    return $this->belongsTo(SupCiaComponent::class, 'sup_cia_component_id');
  }

  public function course()
  {
    return $this->belongsTo(ProgramCourseMaster::class, 'course_id');
  }

  public function subject()
  {
    return $this->belongsTo(Subject::class, 'subject_id');
  }
}
