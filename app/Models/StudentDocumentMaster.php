<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDocumentMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'slug',
    'is_resume',
    'is_active',
    'sort_order',
  ];

  protected $casts = [
    'is_resume' => 'boolean',
    'is_active' => 'boolean',
  ];
}
