<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalMarkLog extends Model
{
  use HasFactory;

  protected $table = 'internal_mark_logs';

  protected $fillable = [
    'internal_mark_id',
    'student_id',
    'course_id',
    'semester',
    'old_mark',
    'new_mark',
    'changed_by',
    'changed_by_name',
    'change_reason',
  ];

  public function internalMark()
  {
    return $this->belongsTo(InterMark::class, 'internal_mark_id', 'id');
  }

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id', 'id');
  }

  public function course()
  {
    return $this->belongsTo(ProgramCourseMaster::class, 'course_id', 'id');
  }

  public function changedByUser()
  {
    return $this->belongsTo(User::class, 'changed_by', 'id');
  }
}
