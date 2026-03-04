<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
  use HasFactory;

  public $timestamps = false;

  protected $fillable = [
    'user_id',
    'event',
    'auditable_type',
    'auditable_id',
    'description',
    'ip_address',
    'method',
    'url',
    'user_agent',
    'old_values',
    'new_values',
    'created_at',
  ];

  protected $casts = [
    'old_values' => 'array',
    'new_values' => 'array',
    'created_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
