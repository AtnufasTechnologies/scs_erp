<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCampusTransferLog extends Model
{
  use HasFactory;

  public $timestamps = false;

  protected $fillable = [
    'student_id',
    'roll_no',
    'from_campus_id',
    'to_campus_id',
    'from_program_id',
    'to_program_id',
    'from_department_id',
    'to_department_id',
    'changed_by',
    'reason',
    'old_snapshot',
    'new_snapshot',
    'created_at',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'old_snapshot' => 'array',
    'new_snapshot' => 'array',
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function fromCampus()
  {
    return $this->belongsTo(Campus::class, 'from_campus_id');
  }

  public function toCampus()
  {
    return $this->belongsTo(Campus::class, 'to_campus_id');
  }

  public function fromProgram()
  {
    return $this->belongsTo(StudentProgram::class, 'from_program_id');
  }

  public function toProgram()
  {
    return $this->belongsTo(StudentProgram::class, 'to_program_id');
  }

  public function changedByUser()
  {
    return $this->belongsTo(User::class, 'changed_by');
  }
}
