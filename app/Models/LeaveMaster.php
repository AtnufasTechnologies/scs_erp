<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'leave_type_name',
    'leave_type_code',
    'description',
    'allowed_days_per_year',
    'requires_attachment',
    'is_active',
    'display_order',
    'badge_color',
  ];

  protected $casts = [
    'requires_attachment' => 'boolean',
    'is_active' => 'boolean',
    'allowed_days_per_year' => 'integer',
    'display_order' => 'integer',
  ];

  /**
   * Scope to get only active leave types
   */
  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * Scope to get ordered leave types
   */
  public function scopeOrdered($query)
  {
    return $query->orderBy('display_order')->orderBy('leave_type_name');
  }

  /**
   * Get all leave applications for this leave type
   */
  public function leaveApplications()
  {
    return $this->hasMany(FacultyLeaveApplication::class, 'leave_type_id');
  }

  /**
   * Check if this leave type has unlimited days
   */
  public function isUnlimited()
  {
    return is_null($this->allowed_days_per_year);
  }

  /**
   * Get formatted allowed days text
   */
  public function getAllowedDaysTextAttribute()
  {
    return $this->isUnlimited() ? 'Unlimited' : $this->allowed_days_per_year . ' days/year';
  }
}
