<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DsaAttendanceRegularizationItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'regularization_id',
    'attendance_id',
    'student_id',
    'attendance_date',
    'original_status',
    'effective_status',
    'remarks',
    'actioned_by',
    'actioned_at',
  ];

  protected $casts = [
    'attendance_date' => 'date',
    'actioned_at' => 'datetime',
  ];

  public function regularization()
  {
    return $this->belongsTo(DsaAttendanceRegularization::class, 'regularization_id');
  }

  public function attendance()
  {
    return $this->belongsTo(StudentAttendance::class, 'attendance_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
