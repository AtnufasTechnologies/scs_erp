<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadershipRoleAssignment extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'faculty_id',
    'role_master_id',
    'role_name',
    'assignment_scope',
    'effective_from',
    'effective_to',
    'is_active',
    'remarks',
    'assigned_by',
    'relieved_by',
    'relieved_reason',
  ];

  protected $casts = [
    'effective_from' => 'date',
    'effective_to' => 'date',
    'is_active' => 'boolean',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id', 'id');
  }

  public function roleMaster()
  {
    return $this->belongsTo(RoleMaster::class, 'role_master_id', 'id');
  }

  public function assignedByUser()
  {
    return $this->belongsTo(User::class, 'assigned_by', 'id');
  }

  public function relievedByUser()
  {
    return $this->belongsTo(User::class, 'relieved_by', 'id');
  }
}
