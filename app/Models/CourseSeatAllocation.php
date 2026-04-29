<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseSeatAllocation extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'subject_id',
    'batch_id',
    'semester_id',
    'course_master_id',
    'total_seats',
    'is_open',
  ];

  protected $casts = [
    'is_open' => 'boolean',
  ];

  public function subject()
  {
    return $this->belongsTo(Subject::class, 'subject_id');
  }

  public function batch()
  {
    return $this->belongsTo(BatchMaster::class, 'batch_id');
  }

  public function semester()
  {
    return $this->belongsTo(Semester::class, 'semester_id');
  }

  public function courseMaster()
  {
    return $this->belongsTo(ProgramCourseMaster::class, 'course_master_id');
  }
}
