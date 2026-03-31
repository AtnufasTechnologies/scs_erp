<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CondonationRule extends Model
{
  protected $fillable = [
    'program_id',
    'rule_name',
    'description',
    'max_absences'
  ];

  public function program(): BelongsTo
  {
    return $this->belongsTo(Program::class);
  }
}
