<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectCourseOffering extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'subject_id',
    'batch_id',
    'semester_id',
    'course_type_id',
    'intake_capacity',
    'is_registration_open',
    'registration_opens_at',
    'registration_closes_at',
  ];

  protected $casts = [
    'is_registration_open'    => 'boolean',
    'registration_opens_at'   => 'datetime',
    'registration_closes_at'  => 'datetime',
  ];

  public function subject()
  {
    return $this->belongsTo(Subject::class, 'subject_id');
  }

  public function batch()
  {
    return $this->belongsTo(BatchMaster::class, 'batch_id');
  }

  public function semester()
  {
    return $this->belongsTo(Semester::class, 'semester_id');
  }

  public function courseType()
  {
    return $this->belongsTo(SubjectTypeMaster::class, 'course_type_id');
  }

  public function registrations()
  {
    return $this->hasMany(StudentOfferingRegistration::class, 'offering_id');
  }

  public function confirmedRegistrations()
  {
    return $this->hasMany(StudentOfferingRegistration::class, 'offering_id')
      ->where('status', 'confirmed');
  }

  public function waitlistedRegistrations()
  {
    return $this->hasMany(StudentOfferingRegistration::class, 'offering_id')
      ->where('status', 'waitlisted');
  }

  /** Seats still available (confirmed only). */
  public function getAvailableSeatsAttribute(): int
  {
    return max(0, $this->intake_capacity - $this->confirmedRegistrations()->count());
  }
}
