<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LateralEntryAuditLog extends Model
{
  use HasFactory;

  public $timestamps = false;

  protected $fillable = [
    'student_id',
    'user_id',
    'entry_type',
    'remarks',
    'source',
    'application_form_path',
    'sourced_application_code',
    'application_snapshot',
    'created_at',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'application_snapshot' => 'array',
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
