<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntegratedProgramSublayerSetting extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'student_program_id',
    'ug_max_year',
    'ug_label',
    'pg_label',
    'is_active',
  ];

  public function studentProgram()
  {
    return $this->belongsTo(StudentProgram::class, 'student_program_id', 'id');
  }
}
