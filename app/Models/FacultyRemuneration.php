<?php

namespace App\Models;

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

  public function faculty()
  {
    return $this->belongsTo(Faculty::class);
  }
}
