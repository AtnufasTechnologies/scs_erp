<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcProgramCollege extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'ec_program_colleges';

  protected $fillable = [
    'program_id',
    'college_name',
    'college_code',
    'contact_person',
    'contact_email',
    'contact_phone',
    'address',
  ];

  public function program()
  {
    return $this->belongsTo(EcProgram::class, 'program_id');
  }

  public function participants()
  {
    return $this->hasMany(EcProgramParticipant::class, 'college_id');
  }
}
