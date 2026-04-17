<?php

namespace App\Models\ExamSystem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCredit extends Model
{
  use SoftDeletes;

  protected $table = 'student_credits';

  const TYPE_EARNED = 'earned';
  const TYPE_TRANSFERRED = 'transferred';

  const STATUS_ACTIVE = 'active';
  const STATUS_UNDER_REVIEW = 'under_review';
  const STATUS_VERIFIED = 'verified';
  const STATUS_REJECTED = 'rejected';

  protected $fillable = [
    'exam_student_id',
    'exam_subject_id',
    'exam_session_id',
    'credits_earned',
    'credit_type',
    'semester',
    'grade',
    'grade_point',
    'source_institution',
    'source_subject_code',
    'source_subject_name',
    'transfer_date',
    'transfer_reference',
    'verified_by',
    'verified_at',
    'status',
    'remarks',
  ];

  protected $casts = [
    'transfer_date' => 'date',
    'verified_at' => 'datetime',
    'credits_earned' => 'decimal:2',
    'grade_point' => 'decimal:2',
  ];

  // Scopes
  public function scopeEarned($query)
  {
    return $query->where('credit_type', self::TYPE_EARNED);
  }

  public function scopeTransferred($query)
  {
    return $query->where('credit_type', self::TYPE_TRANSFERRED);
  }

  public function scopeActive($query)
  {
    return $query->where('status', self::STATUS_ACTIVE);
  }

  public function scopeVerified($query)
  {
    return $query->where('status', self::STATUS_VERIFIED);
  }

  // Relationships
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class, 'exam_student_id');
  }

  public function subject(): BelongsTo
  {
    return $this->belongsTo(ExamSubjectMaster::class, 'exam_subject_id');
  }

  public function verifier(): BelongsTo
  {
    return $this->belongsTo(\App\Models\StudentMaster::class, 'verified_by');
  }

  // Helpers
  public function isEarned(): bool
  {
    return $this->credit_type === self::TYPE_EARNED;
  }

  public function isTransferred(): bool
  {
    return $this->credit_type === self::TYPE_TRANSFERRED;
  }
}
