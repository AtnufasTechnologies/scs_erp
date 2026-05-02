<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcSponsor extends Model
{
  use HasFactory;

  protected $table = 'ec_sponsors';

  protected $fillable = [
    'event_id',
    'name',
    'contact_person',
    'phone',
    'email',
    'address',
    'pledged_amount',
    'received_amount',
    'tier',
    'benefits_offered',
    'logo',
    'status',
    'notes',
  ];

  protected $casts = [
    'pledged_amount'  => 'decimal:2',
    'received_amount' => 'decimal:2',
  ];

  public function event()
  {
    return $this->belongsTo(EcEvent::class, 'event_id');
  }
}
