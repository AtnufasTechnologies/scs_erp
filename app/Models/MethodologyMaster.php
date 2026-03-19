<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MethodologyMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'description',
    'is_active',
    'sort_order'
  ];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  /**
   * Scope to get only active methodologies
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Scope to order by sort order
   */
  public function scopeOrdered($query)
  {
    return $query->orderBy('sort_order');
  }

  /**
   * Get work diaries using this methodology
   */
  public function workDiaries()
  {
    return $this->hasMany(WorkDiary::class, 'methodology', 'name');
  }
}
