<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
  protected $fillable = [
    'program_id',
    'name',
    'exam_date',
    'exam_type',
    'semester',
    'start_date',
    'end_date',
    'regulation_id',
    'status'
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
}
