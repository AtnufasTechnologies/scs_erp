<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrFacultyStatusHistory extends Model
{
  use HasFactory;

  protected $fillable = [
    'faculty_id',
    'event_type',
    'status_on',
    'old_status',
    'new_status',
    'remark',
    'acted_by',
  ];

  public function faculty()
  {
    return $this->belongsTo(Faculty::class, 'faculty_id');
  }

  public function actor()
  {
    return $this->belongsTo(User::class, 'acted_by');
  }
}
