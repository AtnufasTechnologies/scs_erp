<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DsaDisciplineHearing extends Model
{
  use HasFactory;

  protected $fillable = [
    'discipline_case_id',
    'hearing_date',
    'committee_members',
    'notes',
    'outcome',
    'status',
    'created_by',
  ];

  protected $casts = [
    'hearing_date' => 'date',
    'committee_members' => 'array',
  ];

  public function disciplineCase()
  {
    return $this->belongsTo(DsaDisciplineCase::class, 'discipline_case_id');
  }
}
