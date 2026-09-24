<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ExamSystem\ExamBatchSemesterScope;

class Exam extends Model
{
  protected $fillable = [
    'program_id',
    'name',
    'assessment_type',
    'exam_date',
    'exam_type',
    'semester',
    'start_date',
    'end_date',
    'regulation_id',
    'status',
    'registration_mode',
    'selected_batch_ids'
  ];

  protected $casts = [
    'selected_batch_ids' => 'array',
  ];

  public function registrations(): HasMany
  {
    return $this->hasMany(Registration::class, 'exam_id');
  }

  public function program(): BelongsTo
  {
    return $this->belongsTo(Program::class);
  }

  public function regulation(): BelongsTo
  {
    return $this->belongsTo(ProgramRegulation::class, 'regulation_id');
  }

  public function batchSemesterScopes(): HasMany
  {
    return $this->hasMany(ExamBatchSemesterScope::class, 'exam_id');
  }
}
