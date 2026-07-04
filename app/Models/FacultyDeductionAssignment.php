<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyDeductionAssignment extends Model
{
  use HasFactory;

  protected $fillable = [
    'faculty_id',
    'deduction_master_id',
    'amount_override',
    'percentage_override',
    'effective_from',
    'effective_to',
    'status',
    'remarks',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'amount_override' => 'decimal:2',
    'percentage_override' => 'decimal:2',
    'effective_from' => 'date',
    'effective_to' => 'date',
  ];

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  public function deductionMaster()
  {
    return $this->belongsTo(DeductionMaster::class, 'deduction_master_id');
  }

  public function scopeActive($query)
  {
    return $query->where('status', 'active');
  }
}
