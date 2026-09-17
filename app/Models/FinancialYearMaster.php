<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialYearMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'start_date',
    'end_date',
    'is_active',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'is_active' => 'boolean',
  ];
}
