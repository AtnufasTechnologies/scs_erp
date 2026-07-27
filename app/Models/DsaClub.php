<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DsaClub extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'name',
    'slug',
    'club_type',
    'subject_id',
    'faculty_coordinator_id',
    'description',
    'established_on',
    'status',
    'created_by',
  ];

  protected $casts = [
    'established_on' => 'date',
  ];

  public function memberships()
  {
    return $this->hasMany(DsaClubMembership::class, 'club_id');
  }

  public function coordinator()
  {
    return $this->belongsTo(Faculty::class, 'faculty_coordinator_id');
  }
}
