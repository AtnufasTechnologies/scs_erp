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
    'course_id',
    'faculty_id',
    'student_id',
    'attendance_date',
    'hour_id',
    'semester_id',
    'batch',
    'qr_url',
    'attendance_method',
    'lecture_start_time',
    'lecture_end_time',
    'status',
    'remarks',
    'extra',
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
   * Get the faculty who took the attendance
   */
  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  /**
   * Get the course information
   */
  public function courseinfo()
  {
    return $this->belongsTo(ProgramCourseMaster::class, 'course_id');
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
  public static function getAttendancePercentage($studentId, $course_id)
  {
    $total = self::where('student_id', $studentId)
      ->where('course_id', $course_id)
      ->count();

    if ($total === 0) {
      return 0;
    }

    $present = self::where('student_id', $studentId)
      ->where('course_id', $course_id)
      ->where('status', 'present')
      ->count();

    return round(($present / $total) * 100, 2);
  }

  function hourmaster()
  {
    return $this->hasOne(HourMaster::class, 'id', 'hour_id');
  }
}
