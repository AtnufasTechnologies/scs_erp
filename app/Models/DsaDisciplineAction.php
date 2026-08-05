<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DsaDisciplineAction extends Model
{
  use HasFactory;

  protected $fillable = [
    'discipline_case_id',
    'action_type',
    'action_from',
    'action_to',
    'remarks',
    'document_path',
    'issued_by',
  ];

  protected $casts = [
    'action_from' => 'date',
    'action_to' => 'date',
  ];

  public function disciplineCase()
  {
    return $this->belongsTo(DsaDisciplineCase::class, 'discipline_case_id');
  }
}
