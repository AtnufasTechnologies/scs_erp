<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsaStudentCouncilMember extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'council_id',
    'student_id',
    'role_slug',
    'role_title',
    'is_executive',
    'appointed_on',
    'ended_on',
    'status',
  ];

  protected $casts = [
    'is_executive' => 'boolean',
    'appointed_on' => 'date',
    'ended_on' => 'date',
  ];

  public function council()
  {
    return $this->belongsTo(DsaStudentCouncil::class, 'council_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
