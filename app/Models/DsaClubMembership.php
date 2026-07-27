<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsaClubMembership extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'club_id',
    'student_id',
    'role_title',
    'joined_on',
    'left_on',
    'status',
  ];

  protected $casts = [
    'joined_on' => 'date',
    'left_on' => 'date',
  ];

  public function club()
  {
    return $this->belongsTo(DsaClub::class, 'club_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
