<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'slug',
    'is_active',
    'is_system',
    'sort_order',
  ];
}
