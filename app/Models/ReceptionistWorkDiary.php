<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceptionistWorkDiary extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'entry_date',
    'work_summary',
    'notes',
    'status',
  ];

  protected $casts = [
    'entry_date' => 'date',
  ];

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
