<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceQrMaster extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'syllabus_faculty_id',
    'routine_id',
    'faculty_id',
    'course_id',
    'semester_id',
    'batch_id',
    'hour_id',
    'attendance_date',
    'attendance_type',
    'code',
    'scan_url',
    'expires_at',
    'status',
  ];

  protected $casts = [
    'attendance_date' => 'date',
    'expires_at' => 'datetime',
  ];

  public function syllabusFaculty()
  {
    return $this->belongsTo(SyllabusHasFaculty::class, 'syllabus_faculty_id');
  }

  public function routine()
  {
    return $this->belongsTo(SubjectHasRoutine::class, 'routine_id')->with('hourmaster');
  }

  function hourmaster()
  {
    return $this->hasOne(HourMaster::class, 'id', 'hour_id');
  }
}
