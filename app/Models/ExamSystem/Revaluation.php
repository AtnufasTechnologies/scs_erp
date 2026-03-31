<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revaluation extends Model
{
  protected $fillable = [
    'marks_id',
    'exam_student_id',
    'status',
    'remarks'
  ];

  public function mark(): BelongsTo
  {
    return $this->belongsTo(Mark::class, 'marks_id');
  }

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }
}
