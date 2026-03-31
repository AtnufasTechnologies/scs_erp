<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentBatch extends Model
{
  use HasFactory;

  protected $fillable = [
    'batch_name',
    'total_amount',
    'status',
  ];

  public function items()
  {
    return $this->hasMany(PaymentBatchItem::class, 'batch_id');
  }
}
