<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsaStudentCouncil extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'title',
    'academic_year',
    'campus_id',
    'constituted_on',
    'status',
    'created_by',
    'approved_by',
    'remarks',
  ];

  protected $casts = [
    'constituted_on' => 'date',
  ];

  public function members()
  {
    return $this->hasMany(DsaStudentCouncilMember::class, 'council_id');
  }

  public function meetings()
  {
    return $this->hasMany(DsaStudentCouncilMeeting::class, 'council_id');
  }

  public function documents()
  {
    return $this->hasMany(DsaStudentCouncilDocument::class, 'council_id');
  }
}
