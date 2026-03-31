<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttendance extends Model
{
  protected $fillable = [
    'exam_id',
    'student_id',
    'subject_id',
    'room_id',
    'seat_no',
    'dummy_no',
    'status',
    'marked_by',
    'marked_at',
    'remarks',
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'student_id');
  }

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
  }

  public function room(): BelongsTo
  {
    return $this->belongsTo(Room::class, 'room_id');
  }

  public function faculty(): BelongsTo
  {
    return $this->belongsTo(FacultyProfile::class, 'marked_by');
  }

  public function scopePresent($query)
  {
    return $query->where('status', 'present');
  }

  public function scopeAbsent($query)
  {
    return $query->where('status', 'absent');
  }

  public function scopeMalpractice($query)
  {
    return $query->where('status', 'malpractice');
  }
}
