<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultSubject extends Model
{
  protected $table = 'result_subjects';

  protected $fillable = [
    'result_id',
    'erp_subject_id',
    'subject_code',
    'subject_name',
    'fa_marks',
    'sa_marks',
    'total_marks',
    'max_marks',
    'credits',
    'grade_point',
    'grade',
    'result_status',
  ];

  protected $casts = [
    'fa_marks' => 'decimal:2',
    'sa_marks' => 'decimal:2',
    'total_marks' => 'decimal:2',
    'max_marks' => 'decimal:2',
    'grade_point' => 'decimal:2',
    'credits' => 'integer',
  ];

  public function result(): BelongsTo
  {
    return $this->belongsTo(Result::class, 'result_id');
  }

  public function subjectMaster(): BelongsTo
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'erp_subject_id', 'erp_subject_id');
  }
}
