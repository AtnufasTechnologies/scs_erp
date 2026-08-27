<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlacementOpportunity extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'category',
    'month',
    'company_name',
    'drive_date',
    'apply_deadline',
    'description',
    'location',
    'country',
    'logo_path',
    'student_year',
    'campus_id',
    'subject_id',
    'subject_ids',
    'internship_stipend_type',
    'placement_type',
    'opening_type',
    'documentation_required',
    'is_active',
    'created_by',
  ];

  protected $casts = [
    'month' => 'integer',
    'drive_date' => 'date',
    'apply_deadline' => 'date',
    'subject_ids' => 'array',
    'documentation_required' => 'array',
  ];

  public function targetRoles()
  {
    return $this->hasMany(PlacementTargetRole::class);
  }

  public function subject()
  {
    return $this->belongsTo(Subject::class);
  }

  public function campus()
  {
    return $this->belongsTo(Campus::class);
  }

  public function applications()
  {
    return $this->hasMany(PlacementApplication::class, 'placement_opportunity_id');
  }
}
