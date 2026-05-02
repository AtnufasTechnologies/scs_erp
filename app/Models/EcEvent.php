<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcEvent extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'ec_events';

  protected $fillable = [
    'title',
    'description',
    'start_date',
    'end_date',
    'venue',
    'status',
    'total_budget',
    'banner_image',
    'created_by',
  ];

  protected $casts = [
    'start_date'   => 'date',
    'end_date'     => 'date',
    'total_budget' => 'decimal:2',
  ];

  public function programs()
  {
    return $this->hasMany(EcProgram::class, 'event_id');
  }

  public function facultyDuties()
  {
    return $this->hasMany(EcFacultyDuty::class, 'event_id');
  }

  public function fundTransactions()
  {
    return $this->hasMany(EcFundTransaction::class, 'event_id');
  }

  public function sponsors()
  {
    return $this->hasMany(EcSponsor::class, 'event_id');
  }

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function getTotalExpenseAttribute()
  {
    return $this->fundTransactions()->where('type', 'expense')->sum('amount');
  }

  public function getTotalIncomeAttribute()
  {
    return $this->fundTransactions()->where('type', 'income')->sum('amount');
  }
}
