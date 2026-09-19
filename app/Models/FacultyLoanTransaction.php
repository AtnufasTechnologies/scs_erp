<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyLoanTransaction extends Model
{
  use HasFactory;

  protected $fillable = [
    'faculty_loan_id',
    'faculty_id',
    'transaction_type',
    'payment_date',
    'payment_mode',
    'receipt_number',
    'emi_count',
    'amount',
    'remarks',
    'processed_by',
  ];

  protected $casts = [
    'payment_date' => 'date',
    'emi_count' => 'integer',
    'amount' => 'decimal:2',
  ];

  public function loan()
  {
    return $this->belongsTo(FacultyLoan::class, 'faculty_loan_id');
  }

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  public function processor()
  {
    return $this->belongsTo(User::class, 'processed_by');
  }
}
