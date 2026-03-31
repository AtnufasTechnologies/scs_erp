<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramRegulation extends Model
{
  protected $fillable = [
    'program_id',
    'regulation_name',
    'regulation_type',
    'start_year',
    'end_year'
  ];

  public function program(): BelongsTo
  {
    return $this->belongsTo(Program::class);
  }

  public function exams(): HasMany
  {
    return $this->hasMany(Exam::class, 'regulation_id');
  }
}
