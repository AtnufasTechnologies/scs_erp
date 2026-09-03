<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalOfficeActivityTypeMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'slug',
    'description',
    'sort_order',
    'is_active',
    'is_system',
  ];

  protected $casts = [
    'is_active' => 'boolean',
    'is_system' => 'boolean',
  ];
}
