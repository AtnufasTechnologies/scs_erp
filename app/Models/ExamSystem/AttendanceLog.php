<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
  protected $fillable = [
    'attendance_id',
    'action',
    'performed_by',
    'timestamp',
  ];

  public function attendance(): BelongsTo
  {
    return $this->belongsTo(ExamAttendance::class, 'attendance_id');
  }

  public function performer(): BelongsTo
  {
    return $this->belongsTo(FacultyProfile::class, 'performed_by');
  }
}
