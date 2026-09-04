<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcEventIqacReport extends Model
{
  use HasFactory;

  protected $table = 'ec_event_iqac_reports';

  protected $fillable = [
    'ec_event_id',
    'report_title',
    'submitted_on',
    'report_file_path',
    'submission_note',
    'submitted_by_user_id',
    'approval_status',
    'review_remarks',
    'reviewed_by_user_id',
    'reviewed_at',
  ];

  protected $casts = [
    'submitted_on' => 'date',
    'reviewed_at' => 'datetime',
  ];

  public function event()
  {
    return $this->belongsTo(EcEvent::class, 'ec_event_id');
  }

  public function submittedBy()
  {
    return $this->belongsTo(User::class, 'submitted_by_user_id');
  }

  public function reviewedBy()
  {
    return $this->belongsTo(User::class, 'reviewed_by_user_id');
  }
}
