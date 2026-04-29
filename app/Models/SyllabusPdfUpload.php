<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SyllabusPdfUpload extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'subject_id',
    'batch_id',
    'semester_id',
    'course_master_id',
    'file_path',
    'original_name',
    'uploaded_by',
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

  public function courseMaster()
  {
    return $this->belongsTo(ProgramCourseMaster::class, 'course_master_id');
  }

  public function uploader()
  {
    return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
  }

  /** Generate a public URL for the stored PDF. */
  public function getUrlAttribute(): string
  {
    return Storage::disk('s3')->url($this->file_path);
  }
}
