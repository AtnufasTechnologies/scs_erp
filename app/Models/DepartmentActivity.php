<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentActivity extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'subject_id',
    'title',
    'activity_type',
    'description',
    'venue',
    'activity_date',
    'start_time',
    'end_time',
    'organizer_name',
    'organizer_email',
    'organizer_phone',
    'expected_participants',
    'actual_participants',
    'budget',
    'actual_expense',
    'status',
    'iqac_approval_status',
    'iqac_review_remarks',
    'iqac_reviewed_by_user_id',
    'iqac_reviewed_at',
    'remarks',
    'banner_image',
    'attachments',
    'created_by',
    'updated_by'
  ];

  protected $casts = [
    'activity_date' => 'date',
    'iqac_reviewed_at' => 'datetime',
    'attachments' => 'array',
    'budget' => 'decimal:2',
    'actual_expense' => 'decimal:2'
  ];

  // Relationships
  public function subject()
  {
    return $this->belongsTo(Subject::class, 'subject_id');
  }

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function updater()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  // Scopes
  public function scopeUpcoming($query)
  {
    return $query->where('activity_date', '>=', now()->toDateString())
      ->where('status', '!=', 'cancelled')
      ->orderBy('activity_date', 'asc');
  }

  public function scopeCompleted($query)
  {
    return $query->where('status', 'completed')
      ->orderBy('activity_date', 'desc');
  }

  public function scopeByType($query, $type)
  {
    return $query->where('activity_type', $type);
  }

  // Accessors
  public function getFormattedDateAttribute()
  {
    return $this->activity_date->format('d M Y');
  }

  public function getStatusBadgeAttribute()
  {
    $badges = [
      'planned' => 'warning',
      'ongoing' => 'info',
      'completed' => 'success',
      'cancelled' => 'danger'
    ];
    return $badges[$this->status] ?? 'secondary';
  }

  public function participants()
  {
    return $this->hasMany(DepartmentActivityHasParticipant::class, 'activity_id');
  }
}
