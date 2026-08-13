<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeanWeeklyProgress extends Model
{
  use HasFactory;

  protected $table = 'dean_weekly_progresses';

  protected $fillable = [
    'user_id',
    'week_date',
    'activities_completed',
    'activities_in_progress',
    'pending_activities',
    'completion_percent',
    'reason_for_delay',
    'evidence_remarks',
  ];
}
