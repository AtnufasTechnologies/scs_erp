<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrincipalAppointment extends Model
{
  use HasFactory;

  protected $fillable = [
    'visitor_name',
    'visitor_phone',
    'visitor_email',
    'appointment_date',
    'appointment_time',
    'purpose',
    'notes',
    'status',
    'created_by',
  ];

  protected $casts = [
    'appointment_date' => 'date',
  ];

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
