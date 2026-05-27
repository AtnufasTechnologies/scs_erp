<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrFdpProgram extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'program_code',
    'program_title',
    'description',
    'program_type',
    'organizer',
    'venue',
    'start_date',
    'end_date',
    'duration_days',
    'program_fee',
    'max_participants',
    'target_audience',
    'status',
    'coordinator_name',
    'coordinator_contact',
    'attachment',
    'remarks',
    'created_by',
  ];

  protected $casts = [
    'start_date' => 'date',
    'end_date' => 'date',
    'program_fee' => 'decimal:2',
    'max_participants' => 'integer',
    'duration_days' => 'integer',
  ];

  /**
   * Get the creator of this program
   */
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * Get all participants for this program
   */
  public function participants()
  {
    return $this->hasMany(HrFdpParticipant::class, 'fdp_program_id');
  }

  /**
   * Get approved participants
   */
  public function approvedParticipants()
  {
    return $this->hasMany(HrFdpParticipant::class, 'fdp_program_id')
      ->where('status', 'approved');
  }

  /**
   * Get completed participants
   */
  public function completedParticipants()
  {
    return $this->hasMany(HrFdpParticipant::class, 'fdp_program_id')
      ->where('status', 'completed');
  }

  /**
   * Scope for active programs
   */
  public function scopeActive($query)
  {
    return $query->whereIn('status', ['open', 'ongoing']);
  }

  /**
   * Scope for upcoming programs
   */
  public function scopeUpcoming($query)
  {
    return $query->where('start_date', '>', now())
      ->where('status', 'open');
  }

  /**
   * Scope for ongoing programs
   */
  public function scopeOngoing($query)
  {
    return $query->where('status', 'ongoing')
      ->where('start_date', '<=', now())
      ->where('end_date', '>=', now());
  }

  /**
   * Scope for completed programs
   */
  public function scopeCompleted($query)
  {
    return $query->where('status', 'completed');
  }

  /**
   * Check if program is full
   */
  public function isFull()
  {
    if (!$this->max_participants) {
      return false;
    }

    return $this->approvedParticipants()->count() >= $this->max_participants;
  }

  /**
   * Get available seats
   */
  public function getAvailableSeatsAttribute()
  {
    if (!$this->max_participants) {
      return 'Unlimited';
    }

    $occupied = $this->approvedParticipants()->count();
    return max(0, $this->max_participants - $occupied);
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return match ($this->status) {
      'draft' => 'secondary',
      'open' => 'primary',
      'ongoing' => 'info',
      'completed' => 'success',
      'cancelled' => 'danger',
      default => 'secondary',
    };
  }
}
