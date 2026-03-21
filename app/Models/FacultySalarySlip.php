<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacultySalarySlip extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'faculty_id',
    'annual_session_id',
    'month',
    'year',
    'salary_slip_number',
    'basic_salary',
    'da',
    'hra',
    'ta',
    'medical_allowance',
    'special_allowance',
    'other_allowances',
    'pf',
    'esi',
    'professional_tax',
    'tds',
    'loan_deduction',
    'other_deductions',
    'gross_salary',
    'total_deductions',
    'net_salary',
    'working_days',
    'present_days',
    'leave_days',
    'remarks',
    'status',
    'payment_date',
    'payment_mode',
    'payment_reference',
    'approved_by',
    'approved_at',
  ];

  protected $casts = [
    'payment_date' => 'date',
    'approved_at' => 'datetime',
    'basic_salary' => 'decimal:2',
    'da' => 'decimal:2',
    'hra' => 'decimal:2',
    'ta' => 'decimal:2',
    'medical_allowance' => 'decimal:2',
    'special_allowance' => 'decimal:2',
    'other_allowances' => 'decimal:2',
    'pf' => 'decimal:2',
    'esi' => 'decimal:2',
    'professional_tax' => 'decimal:2',
    'tds' => 'decimal:2',
    'loan_deduction' => 'decimal:2',
    'other_deductions' => 'decimal:2',
    'gross_salary' => 'decimal:2',
    'total_deductions' => 'decimal:2',
    'net_salary' => 'decimal:2',
  ];

  /**
   * Get the faculty that owns the salary slip
   */
  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  /**
   * Get the annual session
   */
  public function annualSession()
  {
    return $this->belongsTo(AnnualSession::class, 'annual_session_id');
  }

  /**
   * Get the admin who approved the salary slip
   */
  public function approver()
  {
    return $this->belongsTo(User::class, 'approved_by');
  }

  /**
   * Scope for approved salary slips
   */
  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }

  /**
   * Scope for paid salary slips
   */
  public function scopePaid($query)
  {
    return $query->where('status', 'paid');
  }

  /**
   * Scope for draft salary slips
   */
  public function scopeDraft($query)
  {
    return $query->where('status', 'draft');
  }

  /**
   * Scope for specific month and year
   */
  public function scopeForMonth($query, $month, $year)
  {
    return $query->where('month', $month)->where('year', $year);
  }

  /**
   * Scope for current session
   */
  public function scopeCurrentSession($query)
  {
    return $query->where('annual_session_id', \App\Http\Controllers\StaticController::activeSessionId());
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return match ($this->status) {
      'draft' => 'secondary',
      'approved' => 'success',
      'paid' => 'primary',
      default => 'info'
    };
  }

  /**
   * Get formatted month name
   */
  public function getMonthNameAttribute()
  {
    return \Carbon\Carbon::parse($this->year . '-' . $this->month . '-01')->format('F');
  }

  /**
   * Get formatted month and year (e.g., "March 2026")
   */
  public function getMonthYearAttribute()
  {
    return \Carbon\Carbon::parse($this->year . '-' . $this->month . '-01')->format('F Y');
  }

  /**
   * Calculate and update totals
   */
  public function calculateTotals()
  {
    $this->gross_salary = $this->basic_salary
      + $this->da
      + $this->hra
      + $this->ta
      + $this->medical_allowance
      + $this->special_allowance
      + $this->other_allowances;

    $this->total_deductions = $this->pf
      + $this->esi
      + $this->professional_tax
      + $this->tds
      + $this->loan_deduction
      + $this->other_deductions;

    $this->net_salary = $this->gross_salary - $this->total_deductions;

    $this->save();
  }
}
