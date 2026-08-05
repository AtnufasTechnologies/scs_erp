<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DsaCounsellingFollowup extends Model
{
  use HasFactory;

  protected $fillable = [
    'counselling_case_id',
    'followup_date',
    'next_followup_date',
    'counsellor_user_id',
    'wellbeing_score',
    'notes',
    'status',
  ];

  protected $casts = [
    'followup_date' => 'date',
    'next_followup_date' => 'date',
  ];

  public function counsellingCase()
  {
    return $this->belongsTo(DsaCounsellingCase::class, 'counselling_case_id');
  }
}
