<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondonationApplication extends Model
{
  protected $fillable = [
    'exam_student_id',
    'condonation_rule_id',
    'status',
    'remarks'
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function rule(): BelongsTo
  {
    return $this->belongsTo(CondonationRule::class, 'condonation_rule_id');
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
