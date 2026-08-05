<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsaCounsellingCase extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'case_no',
    'student_id',
    'referred_by_user_id',
    'referral_source',
    'risk_level',
    'concern_category',
    'concern_category_id',
    'referred_on',
    'closed_on',
    'status',
    'summary',
    'intervention_plan',
    'created_by',
  ];

  protected $casts = [
    'referred_on' => 'date',
    'closed_on' => 'date',
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function followups()
  {
    return $this->hasMany(DsaCounsellingFollowup::class, 'counselling_case_id');
  }

  public function concernCategory()
  {
    return $this->belongsTo(DsaConcernCategory::class, 'concern_category_id');
  }
}
