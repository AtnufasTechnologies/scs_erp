<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLateFeeExemption extends Model
{
  protected $fillable = [
    'student_id',
    'fee_structure_id',
    'reason',
    'approved_by',
    'approved_at',
    'is_active'
  ];

  protected $casts = [
    'approved_at' => 'datetime',
    'is_active' => 'boolean'
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function feeStructure()
  {
    return $this->belongsTo(FeesStructure::class, 'fee_structure_id');
  }

  public function approver()
  {
    return $this->belongsTo(\App\Models\User::class, 'approved_by');
  }
}
