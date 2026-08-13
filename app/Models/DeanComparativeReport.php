<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeanComparativeReport extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'metric_code',
    'title',
    'remarks',
    'status',
  ];
}
