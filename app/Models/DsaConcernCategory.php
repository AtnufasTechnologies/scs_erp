<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DsaConcernCategory extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'description',
    'sort_order',
    'is_active',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  public function counsellingCases()
  {
    return $this->hasMany(DsaCounsellingCase::class, 'concern_category_id');
  }
}
