<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingResource extends Model
{
  use HasFactory;

  protected $fillable = [
    'training_program_id',
    'resource_title',
    'file_name',
    'file_path',
    'file_type',
    'file_size',
    'uploaded_by',
  ];

  public function trainingProgram()
  {
    return $this->belongsTo(TrainingProgram::class);
  }
}
