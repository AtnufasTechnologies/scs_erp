<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingTargetRole extends Model
{
  use HasFactory;

  protected $fillable = [
    'training_program_id',
    'role_name',
  ];

  public function trainingProgram()
  {
    return $this->belongsTo(TrainingProgram::class);
  }
}
