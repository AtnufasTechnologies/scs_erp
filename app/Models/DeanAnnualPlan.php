<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeanAnnualPlan extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'activity_goal',
    'category',
    'target',
    'expected_completion_date',
    'priority',
    'semester_month',
    'expected_outcome',
    'achievement_actual_result',
    'evidence_required',
    'status',
    'verified_by',
  ];
}
