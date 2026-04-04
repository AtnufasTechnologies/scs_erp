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

  protected $casts = [
    'total_amount' => 'decimal:2',
  ];

  public function items()
  {
    return $this->hasMany(PaymentBatchItem::class, 'batch_id');
  }

  public function scopeDraft($query)
  {
    return $query->where('status', 'draft');
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
