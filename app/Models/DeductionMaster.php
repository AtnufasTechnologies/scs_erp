<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeductionMaster extends Model
{
  use HasFactory;

  protected $table = 'accounts_deduction_masters';

  protected $fillable = [
    'title',
    'TDS',
    'EPF',
    'PT',
    'ESIC',
    'LWF',
    'status',
  ];



  public function assignments()
  {
    return $this->hasMany(FacultyDeductionAssignment::class, 'deduction_master_id');
  }

  public function scopeActive($query)
  {
    return $query->where('status', 1);
  }
}
