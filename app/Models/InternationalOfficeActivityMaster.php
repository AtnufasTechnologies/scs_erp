<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalOfficeActivityMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'activity_title',
    'institution_name',
    'has_mou',
    'mou_signing_date',
    'mou_copy_path',
    'activity_type',
    'participant_type',
    'department_scope',
    'department_details',
    'approval_status',
    'activity_date',
    'report_path',
    'geotagged_photo_paths',
    'finance_grant_kind',
    'finance_count',
    'remarks',
    'is_active',
  ];

  protected $casts = [
    'has_mou' => 'boolean',
    'is_active' => 'boolean',
    'mou_signing_date' => 'date',
    'activity_date' => 'date',
    'geotagged_photo_paths' => 'array',
  ];
}
