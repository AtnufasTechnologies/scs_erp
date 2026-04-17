<?php

namespace App\Models;

use App\Models\ExamSystem\FacultyProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyRemuneration extends Model
{
  use HasFactory;

  protected $fillable = [
    'faculty_id',
    'duty_type',
    'reference_id',
    'quantity',
    'rate',
    'total_amount',
    'status',
    'generated_at',
  ];

  protected $casts = [
    'rate' => 'decimal:2',
    'total_amount' => 'decimal:2',
    'generated_at' => 'datetime',
  ];

  public function faculty()
  {
    return $this->belongsTo(FacultyProfile::class, 'faculty_id');
  }

  public function scopePending($query)
  {
    return $query->where('status', 'pending');
  }

  public function scopeApproved($query)
  {
    return $query->where('status', 'approved');
  }

  public function scopePaid($query)
  {
    return $query->where('status', 'paid');
  }
}
