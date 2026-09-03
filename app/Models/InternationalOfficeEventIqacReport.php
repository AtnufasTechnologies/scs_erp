<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalOfficeEventIqacReport extends Model
{
  use HasFactory;

  protected $fillable = [
    'international_office_event_id',
    'report_title',
    'submitted_on',
    'report_file_path',
    'submission_note',
    'submitted_by_user_id',
  ];

  protected $casts = [
    'submitted_on' => 'date',
  ];

  public function event()
  {
    return $this->belongsTo(InternationalOfficeEvent::class, 'international_office_event_id');
  }
}
