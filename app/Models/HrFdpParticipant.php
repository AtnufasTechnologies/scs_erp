<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrFdpParticipant extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'fdp_program_id',
    'faculty_id',
    'participant_type',
    'registration_date',
    'status',
    'attendance_status',
    'days_attended',
    'certificate_issued',
    'certificate_number',
    'certificate_date',
    'feedback',
    'rating',
    'fee_paid',
    'payment_receipt',
    'remarks',
    'approved_by',
    'approved_at',
  ];

  protected $casts = [
    'registration_date' => 'date',
    'certificate_date' => 'date',
    'approved_at' => 'datetime',
    'certificate_issued' => 'boolean',
    'days_attended' => 'integer',
    'rating' => 'integer',
    'fee_paid' => 'decimal:2',
  ];

  /**
   * Get the FDP program
   */
  public function fdpProgram()
  {
    return $this->belongsTo(HrFdpProgram::class, 'fdp_program_id');
  }

  /**
   * Get the faculty/staff member
   */
  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  /**
   * Get the approver
   */
  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }

  /**
   * Scope for approved participants
   */
  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }

  /**
   * Scope for completed participants
   */
  public function scopeCompleted($query)
  {
    return $query->where('status', 'completed');
  }

  /**
   * Scope for pending participants
   */
  public function scopePending($query)
  {
    return $query->where('status', 'registered');
  }

  /**
   * Scope for attended participants
   */
  public function scopeAttended($query)
  {
    return $query->where('attendance_status', 'present');
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return match ($this->status) {
      'registered' => 'warning',
      'approved' => 'info',
      'rejected' => 'danger',
      'attended' => 'primary',
      'absent' => 'secondary',
      'completed' => 'success',
      default => 'secondary',
    };
  }

  /**
   * Get attendance badge color
   */
  public function getAttendanceBadgeAttribute()
  {
    return match ($this->attendance_status) {
      'present' => 'success',
      'absent' => 'danger',
      'partial' => 'warning',
      default => 'secondary',
    };
  }
}
