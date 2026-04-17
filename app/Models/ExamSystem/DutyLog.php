<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutyLog extends Model
{
  protected $fillable = [
    'faculty_id',
    'duty_type',
    'reference_id',
    'action',
    'timestamp',
    'assigned_by',
  ];

  public function faculty(): BelongsTo
  {
    return $this->belongsTo(FacultyProfile::class, 'faculty_id');
  }

  public function assignedByUser(): BelongsTo
  {
    return $this->belongsTo(\App\Models\User::class, 'assigned_by');
  }

  public function scopePending($query)
  {
    return $query->where('action', 'pending');
  }

  public function scopeCompleted($query)
  {
    return $query->where('action', 'completed');
  }
}
