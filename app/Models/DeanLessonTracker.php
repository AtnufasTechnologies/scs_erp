<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeanLessonTracker extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'course_subject',
    'unit_module',
    'topics_planned',
    'plan_to_complete_date',
    'topics_completed',
    'completion_date',
    'classes_planned',
    'assessment_conducted',
    'syllabus_completion_percent',
  ];
}
