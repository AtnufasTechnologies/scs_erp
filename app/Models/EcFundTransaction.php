<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcFundTransaction extends Model
{
  use HasFactory;

  protected $table = 'ec_fund_transactions';

  protected $fillable = [
    'event_id',
    'program_id',
    'type',
    'category',
    'description',
    'amount',
    'transaction_date',
    'receipt_no',
    'payment_mode',
    'attachment',
    'recorded_by',
  ];

  protected $casts = [
    'transaction_date' => 'date',
    'amount'           => 'decimal:2',
  ];

  public function event()
  {
    return $this->belongsTo(EcEvent::class, 'event_id');
  }

  public function program()
  {
    return $this->belongsTo(EcProgram::class, 'program_id');
  }

  public function recordedBy()
  {
    return $this->belongsTo(User::class, 'recorded_by');
  }
}
