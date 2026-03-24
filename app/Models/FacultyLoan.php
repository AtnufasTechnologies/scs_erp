<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultyLoan extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'faculty_id',
    'loan_number',
    'loan_type',
    'loan_amount',
    'emi_amount',
    'total_installments',
    'paid_installments',
    'total_paid',
    'remaining_amount',
    'start_date',
    'end_date',
    'status',
    'remarks',
    'approved_by',
    'approved_at',
  ];

  protected $casts = [
    'loan_amount' => 'decimal:2',
    'emi_amount' => 'decimal:2',
    'total_paid' => 'decimal:2',
    'remaining_amount' => 'decimal:2',
    'start_date' => 'date',
    'end_date' => 'date',
    'approved_at' => 'datetime',
  ];

  /**
   * Get the faculty that owns the loan
   */
  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  /**
   * Get the admin who approved the loan
   */
  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }

  /**
   * Scope a query to only include active loans
   */
  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  /**
   * Scope a query to only include completed loans
   */
  public function scopeCompleted($query)
  {
    return $query->where('status', 'completed');
  }

  /**
   * Get progress percentage
   */
  public function getProgressPercentageAttribute()
  {
    if ($this->total_installments == 0) {
      return 0;
    }
    return round(($this->paid_installments / $this->total_installments) * 100, 2);
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return [
      'active' => 'warning',
      'completed' => 'success',
      'suspended' => 'danger',
    ][$this->status] ?? 'secondary';
  }

  /**
   * Process EMI deduction
   */
  public function deductEMI()
  {
    if ($this->status !== 'active') {
      return false;
    }

    $this->paid_installments += 1;
    $this->total_paid += $this->emi_amount;
    $this->remaining_amount = max(0, $this->remaining_amount - $this->emi_amount);

    // Mark as completed if all installments paid
    if ($this->paid_installments >= $this->total_installments || $this->remaining_amount <= 0) {
      $this->status = 'completed';
      $this->end_date = now();
    }

    $this->save();
    return true;
  }
}
