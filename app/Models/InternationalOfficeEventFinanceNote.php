<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternationalOfficeEventFinanceNote extends Model
{
  use HasFactory;

  protected $fillable = [
    'international_office_event_id',
    'entry_type',
    'amount',
    'note_date',
    'reference_no',
    'note_text',
    'created_by_user_id',
  ];

  protected $casts = [
    'amount' => 'decimal:2',
    'note_date' => 'date',
  ];

  public function event()
  {
    return $this->belongsTo(InternationalOfficeEvent::class, 'international_office_event_id');
  }
}
