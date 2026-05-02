<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcProgram extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'ec_programs';

  protected $fillable = [
    'event_id',
    'name',
    'program_type',
    'program_scope',
    'description',
    'program_date',
    'start_time',
    'end_time',
    'venue',
    'registration_fee',
    'registration_start_date',
    'registration_end_date',
    'max_participants',
    'status',
  ];

  protected $casts = [
    'program_date'            => 'date',
    'registration_start_date' => 'date',
    'registration_end_date'   => 'date',
    'registration_fee'        => 'decimal:2',
  ];

  const SCOPE_NATIONAL = 'national';
  const SCOPE_INTERNATIONAL = 'international';

  public function isInternational(): bool
  {
    return $this->program_scope === self::SCOPE_INTERNATIONAL;
  }

  const TYPE_INTRA = 'intra-college';
  const TYPE_INTER = 'inter-college';

  public function isInterCollege(): bool
  {
    return $this->program_type === self::TYPE_INTER;
  }

  public function event()
  {
    return $this->belongsTo(EcEvent::class, 'event_id');
  }

  public function facultyDuties()
  {
    return $this->hasMany(EcFacultyDuty::class, 'program_id');
  }

  public function fundTransactions()
  {
    return $this->hasMany(EcFundTransaction::class, 'program_id');
  }

  public function colleges()
  {
    return $this->hasMany(EcProgramCollege::class, 'program_id');
  }

  public function participants()
  {
    return $this->hasMany(EcProgramParticipant::class, 'program_id');
  }
}
