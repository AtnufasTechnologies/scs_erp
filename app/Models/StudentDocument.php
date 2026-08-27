<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
  use HasFactory;

  protected $fillable = [
    'student_id',
    'user_id',
    'document_key',
    'title',
    'file_path',
    'mime_type',
    'file_size',
    'is_resume',
    'is_active',
  ];

  protected $casts = [
    'is_resume' => 'boolean',
    'is_active' => 'boolean',
  ];

  public function student()
  {
    return $this->belongsTo(StudentMaster::class, 'student_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
