<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DsaAttendanceRegularization extends Model
{
  use HasFactory;

  protected $fillable = [
    'request_no',
    'event_source',
    'event_id',
    'event_start_date',
    'event_end_date',
    'approval_status',
    'requested_by',
    'approved_by',
    'approved_at',
    'remarks',
  ];

  protected $casts = [
    'event_start_date' => 'date',
    'event_end_date' => 'date',
    'approved_at' => 'datetime',
  ];

  public function items()
  {
    return $this->hasMany(DsaAttendanceRegularizationItem::class, 'regularization_id');
  }
}
