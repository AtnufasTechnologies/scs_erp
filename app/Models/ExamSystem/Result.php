<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Result extends Model
{
  protected $fillable = [
    'exam_id',
    'exam_session_id',
    'exam_student_id',
    'sgpa',
    'cgpa',
    'percentage',
    'earned_credits',
    'result_status',
    'is_published',
    'published_at',
  ];

  protected $casts = [
    'sgpa' => 'decimal:2',
    'cgpa' => 'decimal:2',
    'percentage' => 'decimal:2',
    'is_published' => 'boolean',
    'published_at' => 'datetime',
  ];

  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function exam(): BelongsTo
  {
    return $this->belongsTo(Exam::class, 'exam_id');
  }

  public function examSession(): BelongsTo
  {
    return $this->belongsTo(ExamSession::class, 'exam_session_id');
  }

  public function resultSubjects(): HasMany
  {
    return $this->hasMany(ResultSubject::class, 'result_id');
  }

  public function scopePending($query)
  {
    return $query->where('result_status', 'pending');
  }

  public function scopeApproved($query)
  {
    return $query->where('result_status', 'approved');
  }

  public function scopePublished($query)
  {
    return $query->where('is_published', true);
  }

  public function scopeUnpublished($query)
  {
    return $query->where('is_published', false);
  }
}
