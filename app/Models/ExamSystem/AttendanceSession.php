<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSession extends Model
{
  protected $fillable = [
    'exam_id',
    'room_id',
    'faculty_id',
    'session',
    'date',
    'status',
  ];

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function room(): BelongsTo
  {
    return $this->belongsTo(Room::class, 'room_id');
  }

  public function faculty(): BelongsTo
  {
    return $this->belongsTo(FacultyProfile::class, 'faculty_id');
  }
}
