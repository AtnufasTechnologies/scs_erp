<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class HrPayMatrix extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'hr_pay_matrix';

  protected $fillable = [
    'matrix_code',
    'matrix_name',
    'designation',
    'grade_level',
    'designation_id',
    'grade_level_id',
    'pay_band',
    'grade_pay',
    'employment_type',
    'basic_salary',
    'da_percentage',
    'da_fixed',
    'hra_percentage',
    'hra_fixed',
    'ta',
    'medical_allowance',
    'special_allowance',
    'other_allowances',
    'pf_percentage',
    'pf_fixed',
    'esi_percentage',
    'esi_fixed',
    'professional_tax',
    'tds_percentage',
    'other_deductions',
    'annual_increment_percentage',
    'increment_month',
    'default_working_days',
    'status',
    'effective_from',
    'effective_to',
    'description',
    'remarks',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'basic_salary' => 'decimal:2',
    'da_percentage' => 'decimal:2',
    'da_fixed' => 'decimal:2',
    'hra_percentage' => 'decimal:2',
    'hra_fixed' => 'decimal:2',
    'ta' => 'decimal:2',
    'medical_allowance' => 'decimal:2',
    'special_allowance' => 'decimal:2',
    'other_allowances' => 'decimal:2',
    'pf_percentage' => 'decimal:2',
    'pf_fixed' => 'decimal:2',
    'esi_percentage' => 'decimal:2',
    'esi_fixed' => 'decimal:2',
    'professional_tax' => 'decimal:2',
    'tds_percentage' => 'decimal:2',
    'other_deductions' => 'decimal:2',
    'annual_increment_percentage' => 'decimal:2',
    'effective_from' => 'date',
    'effective_to' => 'date',
  ];

  // Boot method to auto-generate matrix code and set creator
  protected static function boot()
  {
    parent::boot();

    static::creating(function ($payMatrix) {
      if (empty($payMatrix->matrix_code)) {
        $payMatrix->matrix_code = self::generateMatrixCode();
      }
      if (Auth::check()) {
        $payMatrix->created_by = Auth::id();
      }
    });

    static::updating(function ($payMatrix) {
      if (Auth::check()) {
        $payMatrix->updated_by = Auth::id();
      }
    });
  }

  /**
   * Generate unique matrix code
   */
  public static function generateMatrixCode()
  {
    $year = date('Y');
    $lastMatrix = self::withTrashed()
      ->where('matrix_code', 'like', "PM{$year}%")
      ->orderBy('matrix_code', 'desc')
      ->first();

    if ($lastMatrix) {
      $lastNumber = intval(substr($lastMatrix->matrix_code, -4));
      $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
      $newNumber = '0001';
    }

    return "PM{$year}{$newNumber}";
  }

  /**
   * Relationships
   */

  // Faculty members using this pay matrix
  public function facultySalaries()
  {
    return $this->hasMany(FacultySalaryMaster::class, 'pay_matrix_id');
  }

  // Creator
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  // Last updater
  public function updater()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  /**
   * Scopes
   */

  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }

  public function scopeInactive($query)
  {
    return $query->where('status', 'inactive');
  }

  public function scopeByEmploymentType($query, $type)
  {
    return $query->where('employment_type', $type);
  }

  public function scopeByDesignation($query, $designation)
  {
    return $query->where('designation', $designation);
  }

  public function scopeEffectiveOn($query, $date)
  {
    return $query->where(function ($q) use ($date) {
      $q->where('effective_from', '<=', $date)
        ->where(function ($q2) use ($date) {
          $q2->whereNull('effective_to')
            ->orWhere('effective_to', '>=', $date);
        });
    });
  }

  /**
   * Helper Methods
   */

  // Calculate DA amount (percentage or fixed)
  public function calculateDA()
  {
    if ($this->da_percentage > 0) {
      return ($this->basic_salary * $this->da_percentage) / 100;
    }
    return $this->da_fixed;
  }

  // Calculate HRA amount (percentage or fixed)
  public function calculateHRA()
  {
    if ($this->hra_percentage > 0) {
      return ($this->basic_salary * $this->hra_percentage) / 100;
    }
    return $this->hra_fixed;
  }

  // Calculate PF amount (percentage or fixed)
  public function calculatePF()
  {
    return 0;
  }

  // Calculate ESI amount (percentage or fixed)
  public function calculateESI()
  {
    return 0;
  }

  // Calculate TDS amount
  public function calculateTDS()
  {
    return 0;
  }

  // Calculate gross salary
  public function calculateGrossSalary()
  {
    return $this->basic_salary
      + $this->calculateDA()
      + $this->calculateHRA()
      + $this->ta
      + $this->medical_allowance
      + $this->special_allowance
      + $this->other_allowances;
  }

  // Calculate total deductions
  public function calculateTotalDeductions()
  {
    return 0;
  }

  // Calculate net salary
  public function calculateNetSalary()
  {
    return $this->calculateGrossSalary() - $this->calculateTotalDeductions();
  }

  // Get all salary components as array
  public function getSalaryComponents()
  {
    return [
      'earnings' => [
        'basic_salary' => $this->basic_salary,
        'da' => $this->calculateDA(),
        'hra' => $this->calculateHRA(),
        'ta' => $this->ta,
        'medical_allowance' => $this->medical_allowance,
        'special_allowance' => $this->special_allowance,
        'other_allowances' => $this->other_allowances,
      ],
      'deductions' => [
        'pf' => $this->calculatePF(),
        'esi' => $this->calculateESI(),
        'professional_tax' => $this->professional_tax,
        'tds' => $this->calculateTDS(),
        'other_deductions' => $this->other_deductions,
      ],
      'summary' => [
        'gross_salary' => $this->calculateGrossSalary(),
        'total_deductions' => $this->calculateTotalDeductions(),
        'net_salary' => $this->calculateNetSalary(),
      ],
    ];
  }

  // Check if matrix is currently effective
  public function isEffective($date = null)
  {
    $date = $date ?? now()->format('Y-m-d');

    if ($this->effective_from && $this->effective_from > $date) {
      return false;
    }

    if ($this->effective_to && $this->effective_to < $date) {
      return false;
    }

    return $this->status === 'active';
  }

  // Get faculty count using this matrix
  public function getFacultyCountAttribute()
  {
    return $this->facultySalaries()->count();
  }

  // Get formatted designation with grade
  public function getFullDesignationAttribute()
  {
    return "{$this->designation} - {$this->grade_level}";
  }

  // Get status badge color
  public function getStatusColorAttribute()
  {
    return match ($this->status) {
      'active' => 'success',
      'inactive' => 'warning',
      'archived' => 'secondary',
      default => 'secondary',
    };
  }
}
