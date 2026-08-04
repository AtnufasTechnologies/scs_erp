<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentTeachingAllocationSetting extends Model
{
  use HasFactory;

  protected $fillable = [
    'subject_id',
    'allow_multiple_primary_faculty',
    'created_by',
    'updated_by',
  ];

  protected $casts = [
    'allow_multiple_primary_faculty' => 'boolean',
  ];

  public function subject()
  {
    return $this->belongsTo(Subject::class, 'subject_id');
  }
}
