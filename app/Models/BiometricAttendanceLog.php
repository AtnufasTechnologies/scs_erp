<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricAttendanceLog extends Model
{
  protected $fillable = [
    'employee_no',
    'punch_time',
    'event_type',
    'device_ip',
    'device_name',
    'verify_mode',
    'door_no',
    'source_ip',
    'payload',
    'raw_payload',
  ];

  protected $casts = [
    'payload' => 'array',
    'punch_time' => 'datetime',
  ];
}
