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
    'subject_id',
    'is_active',
    'created_by',
  ];

  protected $casts = [
    'month' => 'integer',
    'drive_date' => 'date',
    'apply_deadline' => 'date',
  ];

  public function targetRoles()
  {
    return $this->hasMany(PlacementTargetRole::class);
  }

  public function subject()
  {
    return $this->belongsTo(Subject::class);
  }
}
