<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcFacultyDuty extends Model
{
  use HasFactory;

  protected $table = 'ec_faculty_duties';

  protected $fillable = [
    'event_id',
    'program_id',
    'faculty_id',
    'duty_title',
    'responsibility',
    'status',
    'remarks',
    'assigned_by',
  ];

  public function event()
  {
    return $this->belongsTo(EcEvent::class, 'event_id');
  }

  public function program()
  {
    return $this->belongsTo(EcProgram::class, 'program_id');
  }

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  public function assignedBy()
  {
    return $this->belongsTo(User::class, 'assigned_by');
  }
}
