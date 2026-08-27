<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingPlacementOptIn extends Model
{
  use HasFactory;

  protected $fillable = [
    'student_id',
    'user_id',
    'form_file_path',
    'policy_accepted',
    'policy_accepted_at',
    'opted_at',
    'approval_status',
    'approved_by',
    'approved_at',
    'rejection_reason',
    'rejected_by',
    'rejected_at',
  ];

  protected $casts = [
    'policy_accepted' => 'boolean',
    'policy_accepted_at' => 'datetime',
    'opted_at' => 'datetime',
    'approved_at' => 'datetime',
    'rejected_at' => 'datetime',
  ];
}
