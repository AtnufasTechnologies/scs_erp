<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrVacancyApplication extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'vacancy_id',
    'application_number',
    'applicant_name',
    'email',
    'phone',
    'date_of_birth',
    'gender',
    'address',
    'highest_qualification',
    'specialization',
    'total_experience_years',
    'teaching_experience_years',
    'current_employment',
    'resume_attachment',
    'photo_attachment',
    'additional_documents',
    'cover_letter',
    'status',
    'application_date',
    'interview_date',
    'interview_time',
    'interview_venue',
    'interview_remarks',
    'interview_score',
    'rejection_reason',
    'hr_remarks',
    'reviewed_by',
    'reviewed_at',
  ];

  protected $casts = [
    'date_of_birth' => 'date',
    'application_date' => 'date',
    'interview_date' => 'date',
    'reviewed_at' => 'datetime',
    'additional_documents' => 'array',
    'total_experience_years' => 'integer',
    'teaching_experience_years' => 'integer',
    'interview_score' => 'integer',
  ];

  /**
   * Get the vacancy
   */
  public function vacancy()
  {
    return $this->belongsTo(HrVacancy::class, 'vacancy_id');
  }

  /**
   * Get the reviewer
   */
  public function reviewer()
  {
    return $this->belongsTo(User::class, 'reviewed_by');
  }

  /**
   * Scope for submitted applications
   */
  public function scopeSubmitted($query)
  {
    return $query->where('status', 'submitted');
  }

  /**
   * Scope for under review applications
   */
  public function scopeUnderReview($query)
  {
    return $query->where('status', 'under_review');
  }

  /**
   * Scope for shortlisted applications
   */
  public function scopeShortlisted($query)
  {
    return $query->where('status', 'shortlisted');
  }

  /**
   * Scope for selected applications
   */
  public function scopeSelected($query)
  {
    return $query->where('status', 'selected');
  }

  /**
   * Scope for rejected applications
   */
  public function scopeRejected($query)
  {
    return $query->where('status', 'rejected');
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return match ($this->status) {
      'submitted' => 'info',
      'under_review' => 'warning',
      'shortlisted' => 'primary',
      'interview_scheduled' => 'info',
      'selected' => 'success',
      'rejected' => 'danger',
      'withdrawn' => 'secondary',
      default => 'secondary',
    };
  }

  /**
   * Check if interview is scheduled
   */
  public function hasInterviewScheduled()
  {
    return !is_null($this->interview_date) && !is_null($this->interview_time);
  }
}
