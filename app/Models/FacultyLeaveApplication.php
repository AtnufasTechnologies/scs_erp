<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyLeaveApplication extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'faculty_id',
    'annual_session_id',
    'leave_type_id',
    'leave_type',
    'start_date',
    'end_date',
    'total_days',
    'reason',
    'contact_during_leave',
    'attachment',
    'status',
    'approved_by',
    'approved_at',
    'rejection_reason',
    'admin_remarks',
    'forwarded_to',
    'forwarded_by',
    'forwarded_at',
    'forwarded_remarks',
    'dept_action',
    'dept_action_by',
    'dept_action_at',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'approved_at' => 'datetime',
    'forwarded_at' => 'datetime',
    'dept_action_at' => 'datetime',
  ];

  /**
   * Get the faculty that owns the leave application
   */
  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  /**
   * Get the leave type master
   */
  public function leaveMaster()
  {
    return $this->belongsTo(LeaveMaster::class, 'leave_type_id');
  }

  /**
   * Get the annual session
   */
  public function annualSession()
  {
    return $this->belongsTo(AnnualSession::class, 'annual_session_id');
  }

  /**
   * Get the admin who approved/rejected the leave
   */
  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }

  /**
   * Scope for pending applications
   */
  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  /**
   * Scope for approved applications
   */
  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }

  /**
   * Scope for rejected applications
   */
  public function scopeRejected($query)
  {
    return $query->where('status', 'rejected');
  }

  /**
   * Scope for current session applications
   */
  public function scopeCurrentSession($query)
  {
    return $query->where('annual_session_id', \App\Http\Controllers\StaticController::activeSessionId());
  }

  /**
   * Scope for specific session
   */
  public function scopeForSession($query, $sessionId)
  {
    return $query->where('annual_session_id', $sessionId);
  }

  /**
   * Scope for archived (past sessions) applications
   */
  public function scopeArchived($query)
  {
    return $query->where('annual_session_id', '!=', \App\Http\Controllers\StaticController::activeSessionId())
      ->orWhereNull('annual_session_id');
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return match ($this->status) {
      'pending' => 'warning',
      'approved' => 'success',
      'rejected' => 'danger',
      'cancelled' => 'secondary',
      default => 'info'
    };
  }

  /**
   * Get leave type badge color
   */
  public function getLeaveTypeBadgeAttribute()
  {
    // Try to get from LeaveMaster first
    if ($this->leaveMaster) {
      return $this->leaveMaster->badge_color;
    }

    // Fallback to old logic for backward compatibility
    return match ($this->leave_type) {
      'casual' => 'primary',
      'sick' => 'danger',
      'earned' => 'success',
      'maternity' => 'info',
      'paternity' => 'info',
      default => 'secondary'
    };
  }

  /**
   * Get leave type name
   */
  public function getLeaveTypeNameAttribute()
  {
    if ($this->leaveMaster) {
      return $this->leaveMaster->leave_type_name;
    }

    // Fallback for old records
    return ucfirst($this->leave_type);
  }

  public function forwarder()
  {
    return $this->belongsTo(User::class, 'forwarded_by');
  }

  public function deptActionUser()
  {
    return $this->belongsTo(User::class, 'dept_action_by');
  }

  public function scopeForwarded($query)
  {
    return $query->where('dept_action', 'forwarded');
  }

  public function scopeDeptRejected($query)
  {
    return $query->where('dept_action', 'rejected');
  }

  public function getDeptStatusLabelAttribute()
  {
    return match ($this->dept_action) {
      'forwarded' => 'Forwarded to ' . $this->forwarded_to,
      'rejected' => 'Rejected by Dept',
      default => 'Pending Dept Review'
    };
  }
}
