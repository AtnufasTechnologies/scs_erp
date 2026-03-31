<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemunerationRate extends Model
{
  use HasFactory;

  protected $fillable = [
    'duty_type',
    'rate_type',
    'amount',
    'program_type',
  ];
}
