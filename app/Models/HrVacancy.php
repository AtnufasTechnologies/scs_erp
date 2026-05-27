<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrVacancy extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'vacancy_code',
    'position_title',
    'department_id',
    'employment_type',
    'recruitment_type',
    'number_of_positions',
    'job_description',
    'qualifications_required',
    'experience_required',
    'salary_range',
    'application_start_date',
    'application_end_date',
    'expected_joining_date',
    'status',
    'publish_to_website',
    'published_date',
    'contact_person',
    'contact_email',
    'contact_phone',
    'attachment',
    'remarks',
    'created_by',
  ];

  protected $casts = [
    'application_start_date' => 'date',
    'application_end_date' => 'date',
    'expected_joining_date' => 'date',
    'published_date' => 'date',
    'publish_to_website' => 'boolean',
    'number_of_positions' => 'integer',
  ];

  /**
   * Get the department/subject
   */
  public function department()
  {
    return $this->belongsTo(Subject::class, 'department_id');
  }

  /**
   * Get the creator of this vacancy
   */
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * Get all applications for this vacancy
   */
  public function applications()
  {
    return $this->hasMany(HrVacancyApplication::class, 'vacancy_id');
  }

  /**
   * Get shortlisted applications
   */
  public function shortlistedApplications()
  {
    return $this->hasMany(HrVacancyApplication::class, 'vacancy_id')
      ->where('status', 'shortlisted');
  }

  /**
   * Get selected applications
   */
  public function selectedApplications()
  {
    return $this->hasMany(HrVacancyApplication::class, 'vacancy_id')
      ->where('status', 'selected');
  }

  /**
   * Scope for active vacancies
   */
  public function scopeActive($query)
  {
    return $query->where('status', 'published')
      ->where('application_end_date', '>=', now());
  }

  /**
   * Scope for published vacancies
   */
  public function scopePublished($query)
  {
    return $query->where('status', 'published')
      ->where('publish_to_website', true);
  }

  /**
   * Scope for open vacancies
   */
  public function scopeOpen($query)
  {
    return $query->where('status', 'published')
      ->where('application_start_date', '<=', now())
      ->where('application_end_date', '>=', now());
  }

  /**
   * Scope for closed vacancies
   */
  public function scopeClosed($query)
  {
    return $query->where(function ($q) {
      $q->where('status', 'closed')
        ->orWhere('application_end_date', '<', now());
    });
  }

  /**
   * Check if vacancy is open for applications
   */
  public function isOpen()
  {
    return $this->status === 'published' &&
      $this->application_start_date <= now() &&
      $this->application_end_date >= now();
  }

  /**
   * Check if vacancy is closed
   */
  public function isClosed()
  {
    return $this->status === 'closed' ||
      $this->status === 'filled' ||
      $this->application_end_date < now();
  }

  /**
   * Get remaining positions
   */
  public function getRemainingPositionsAttribute()
  {
    $filled = $this->selectedApplications()->count();
    return max(0, $this->number_of_positions - $filled);
  }

  /**
   * Get status badge color
   */
  public function getStatusBadgeAttribute()
  {
    return match ($this->status) {
      'draft' => 'secondary',
      'published' => 'success',
      'closed' => 'warning',
      'cancelled' => 'danger',
      'filled' => 'primary',
      default => 'secondary',
    };
  }

  /**
   * Get recruitment type badge color
   */
  public function getRecruitmentTypeBadgeAttribute()
  {
    return match ($this->recruitment_type) {
      'regular' => 'primary',
      'adhoc' => 'info',
      'contractual' => 'warning',
      'guest' => 'secondary',
      'visiting' => 'dark',
      default => 'secondary',
    };
  }
}
