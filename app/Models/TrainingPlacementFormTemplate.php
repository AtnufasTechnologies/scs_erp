<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingPlacementFormTemplate extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'file_path',
    'uploaded_by',
    'is_active',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];
}
