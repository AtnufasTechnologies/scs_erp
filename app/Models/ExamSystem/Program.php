<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
  protected $fillable = [
    'name',
    'code',
    'type'
  ];

  public function subjects(): HasMany
  {
    return $this->hasMany(Subject::class);
  }

  public function regulations(): HasMany
  {
    return $this->hasMany(ProgramRegulation::class);
  }
}
