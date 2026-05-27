<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySalaryMaster extends Model
{
  use HasFactory;

  protected $fillable = [
    'faculty_id',
    'pay_matrix_id',
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
    'other_deductions',
    'working_days',
    'status',
    'effective_from',
    'effective_to',
    'remarks',
  ];

  protected $casts = [
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
    'other_deductions' => 'decimal:2',
    'effective_from' => 'date',
    'effective_to' => 'date',
  ];

  /**
   * Get the faculty that owns the salary master
   */
  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id', 'id');
  }

  /**
   * Get the pay matrix that this salary is based on
   */
  public function payMatrix()
  {
    return $this->belongsTo(HrPayMatrix::class, 'pay_matrix_id');
  }

  /**
   * Scope for active salary masters
   */
  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  /**
   * Calculate total earnings
   */
  public function getTotalEarningsAttribute()
  {
    return $this->basic_salary +
      $this->da +
      $this->hra +
      $this->ta +
      $this->medical_allowance +
      $this->special_allowance +
      $this->other_allowances;
  }

  /**
   * Calculate total deductions (excluding loan)
   */
  public function getTotalDeductionsAttribute()
  {
    return $this->pf +
      $this->esi +
      $this->professional_tax +
      $this->tds +
      $this->other_deductions;
  }

  /**
   * Calculate net salary (without loan deduction)
   */
  public function getNetSalaryAttribute()
  {
    return $this->total_earnings - $this->total_deductions;
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return $this->status === 'active' ? 'success' : 'secondary';
  }
}
