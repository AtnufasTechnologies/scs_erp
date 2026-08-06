<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TpoEvent extends Model
{
  use HasFactory;

  protected $fillable = [
    'event_type',
    'title',
    'resource_person',
    'campus_id',
    'subject_id',
    'event_date',
    'program_description',
    'participant_count',
    'report_path',
    'approval_status',
    'approved_by',
    'approved_at',
    'created_by',
  ];

  protected $casts = [
    'event_date' => 'date',
    'participant_count' => 'integer',
    'approved_at' => 'datetime',
  ];

  public function campus()
  {
    return $this->belongsTo(Campus::class);
  }

  public function subject()
  {
    return $this->belongsTo(Subject::class);
  }

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }
}
