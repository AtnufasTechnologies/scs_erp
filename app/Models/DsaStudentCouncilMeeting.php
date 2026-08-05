<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsaStudentCouncilMeeting extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'council_id',
    'meeting_no',
    'title',
    'meeting_date',
    'start_time',
    'end_time',
    'venue',
    'agenda',
    'minutes',
    'resolutions',
    'convened_by',
    'status',
  ];

  protected $casts = [
    'meeting_date' => 'date',
  ];

  public function council()
  {
    return $this->belongsTo(DsaStudentCouncil::class, 'council_id');
  }

  public function documents()
  {
    return $this->hasMany(DsaStudentCouncilDocument::class, 'meeting_id');
  }
}
