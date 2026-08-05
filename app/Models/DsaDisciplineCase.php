<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsaDisciplineCase extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'case_no',
    'student_id',
    'complaint_source',
    'complainant_name',
    'incident_date',
    'severity',
    'status',
    'committee_name',
    'summary',
    'details',
    'created_by',
  ];

  protected $casts = [
    'incident_date' => 'date',
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function hearings()
  {
    return $this->hasMany(DsaDisciplineHearing::class, 'discipline_case_id');
  }

  public function actions()
  {
    return $this->hasMany(DsaDisciplineAction::class, 'discipline_case_id');
  }
}
