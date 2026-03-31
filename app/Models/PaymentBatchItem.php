<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentBatchItem extends Model
{
  use HasFactory;

  protected $fillable = [
    'batch_id',
    'faculty_remuneration_id',
  ];

  public function batch()
  {
    return $this->belongsTo(PaymentBatch::class, 'batch_id');
  }

  public function facultyRemuneration()
  {
    return $this->belongsTo(FacultyRemuneration::class);
  }
}
