<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalOfficeEvent extends Model
{
  use HasFactory;

  protected $fillable = [
    'activity_type_master_id',
    'nature_of_activity',
    'department_scope',
    'department_subject_ids',
    'approval_type',
    'visiting_institution_name',
    'visiting_institution_contact',
    'visiting_institution_email',
    'visiting_institution_address',
    'has_mou',
    'mou_document_path',
    'trip_start_date',
    'trip_end_date',
    'geotagged_photo_paths',
    'visit_photo_paths',
    'members_json',
    'remarks',
    'created_by_user_id',
  ];

  protected $casts = [
    'department_subject_ids' => 'array',
    'geotagged_photo_paths' => 'array',
    'visit_photo_paths' => 'array',
    'members_json' => 'array',
    'has_mou' => 'boolean',
    'trip_start_date' => 'date',
    'trip_end_date' => 'date',
  ];

  public function activityType()
  {
    return $this->belongsTo(InternationalOfficeActivityTypeMaster::class, 'activity_type_master_id');
  }

  public function financeNotes()
  {
    return $this->hasMany(InternationalOfficeEventFinanceNote::class, 'international_office_event_id');
  }

  public function iqacReports()
  {
    return $this->hasMany(InternationalOfficeEventIqacReport::class, 'international_office_event_id');
  }
}
