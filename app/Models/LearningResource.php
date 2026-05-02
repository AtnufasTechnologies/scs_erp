<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearningResource extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'syllabus_subunit_id',
    'batch_id',
    'semester_id',
    'subject_id',
    'uploader_id',
    'title',
    'description',
    'file_path',
    'file_type',
    'file_size',
  ];

  /**
   * Get the syllabus subunit that owns the resource.
   */
  public function syllabusSubunit()
  {
    return $this->belongsTo(SyllabusSubunit::class, 'syllabus_subunit_id');
  }

  /**
   * Get the batch for this resource.
   */
  public function batch()
  {
    return $this->belongsTo(BatchMaster::class, 'batch_id');
  }

  /**
   * Get the semester for this resource.
   */
  public function semester()
  {
    return $this->belongsTo(Semester::class, 'semester_id');
  }

  /**
   * Get the subject for this resource.
   */
  public function subject()
  {
    return $this->belongsTo(Subject::class, 'subject_id');
  }

  /**
   * Get the faculty who uploaded this resource.
   */
  public function uploader()
  {
    return $this->belongsTo(Faculty::class, 'uploader_id');
  }

  /**
   * Get human-readable file size.
   */
  public function getFormattedFileSizeAttribute()
  {
    if (!$this->file_size) {
      return 'Unknown';
    }

    $units = ['B', 'KB', 'MB', 'GB'];
    $size = $this->file_size;
    $unitIndex = 0;

    while ($size >= 1024 && $unitIndex < count($units) - 1) {
      $size /= 1024;
      $unitIndex++;
    }

    return round($size, 2) . ' ' . $units[$unitIndex];
  }
}
