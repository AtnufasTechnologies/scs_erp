<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyProfile extends Model
{
  protected $fillable = [
    'erp_faculty_id',
    'name',
    'department',
    'designation'
  ];

  public function invigilationDuties(): HasMany
  {
    return $this->hasMany(InvigilationDuty::class, 'faculty_id');
  }

  public function evaluationDuties(): HasMany
  {
    return $this->hasMany(EvaluationDuty::class, 'faculty_id');
  }

  public function moderationDuties(): HasMany
  {
    return $this->hasMany(ModerationDuty::class, 'faculty_id');
  }
}
