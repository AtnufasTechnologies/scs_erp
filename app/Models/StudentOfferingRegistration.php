<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentOfferingRegistration extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'offering_id',
    'student_id',
    'queue_position',
    'status',
  ];

  public function offering()
  {
    return $this->belongsTo(SubjectCourseOffering::class, 'offering_id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }
}
