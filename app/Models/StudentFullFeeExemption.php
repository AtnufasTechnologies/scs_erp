<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFullFeeExemption extends Model
{
  protected $fillable = [
    'student_id',
    'reason',
    'approved_by',
    'approved_at',
    'is_active',
    'revoked_by',
    'revoked_at'
  ];

  protected $casts = [
    'approved_at' => 'datetime',
    'revoked_at' => 'datetime',
    'is_active' => 'boolean'
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function approver()
  {
    return $this->belongsTo(\App\Models\User::class, 'approved_by');
  }

  public function revoker()
  {
    return $this->belongsTo(\App\Models\User::class, 'revoked_by');
  }
}
