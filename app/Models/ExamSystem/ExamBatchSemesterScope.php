<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamBatchSemesterScope extends Model
{
  protected $fillable = [
    'exam_id',
    'batch_id',
    'semester_id',
  ];

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }
}
