<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcProgramParticipant extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'ec_program_participants';

  protected $fillable = [
    'program_id',
    'college_id',
    'first_name',
    'last_name',
    'email',
    'phone',
  ];

  public function program()
  {
    return $this->belongsTo(EcProgram::class, 'program_id');
  }

  public function college()
  {
    return $this->belongsTo(EcProgramCollege::class, 'college_id');
  }

  public function getFullNameAttribute(): string
  {
    return $this->first_name . ' ' . $this->last_name;
  }
}
