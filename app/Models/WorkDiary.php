<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkDiary extends Model
{
  use HasFactory;

  protected $table = 'work_diaries';

  protected $fillable = [
    'faculty_id',
    'date',
    'hour',
    'description',
    'methodology',
    'class_type',
    'work_type',
    'document_path',
    'status'
  ];

  protected $casts = [
    'date' => 'date',
    'hour' => 'integer'
  ];

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  public function methodologyMaster()
  {
    return $this->belongsTo(MethodologyMaster::class, 'methodology', 'name');
  }
}
