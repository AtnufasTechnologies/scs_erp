<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAttendance extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'routine_id',
    'student_id',
    'attendance_date',
    'lecture_start_time',
    'lecture_end_time',
    'status',
    'remarks',
  ];

  protected $casts = [
    'attendance_date' => 'date',
    'lecture_start_time' => 'datetime:H:i',
    'lecture_end_time' => 'datetime:H:i',
  ];

  /**
   * Get the routine assignment
   */
  public function routine()
  {
    return $this->belongsTo(SubjectHasRoutine::class, 'routine_id');
  }

  /**
   * Get the student
   */
  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  /**
   * Scope to filter by date range
   */
  public function scopeDateRange($query, $startDate, $endDate)
  {
    return $query->whereBetween('attendance_date', [$startDate, $endDate]);
  }

  /**
   * Scope to filter by status
   */
  public function scopeStatus($query, $status)
  {
    return $query->where('status', $status);
  }

  /**
   * Get attendance percentage for a student
   */
  public static function getAttendancePercentage($studentId, $routineId)
  {
    $total = self::where('student_id', $studentId)
      ->where('routine_id', $routineId)
      ->count();

    if ($total === 0) {
      return 0;
    }

    $present = self::where('student_id', $studentId)
      ->where('routine_id', $routineId)
      ->where('status', 'present')
      ->count();

    return round(($present / $total) * 100, 2);
  }
}
