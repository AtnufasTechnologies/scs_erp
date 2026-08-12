<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeanPerformanceScorecard extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'category',
    'covers',
    'max_score',
    'score_given',
    'verified_by',
    'reviewed_by',
    'remarks',
  ];
}
