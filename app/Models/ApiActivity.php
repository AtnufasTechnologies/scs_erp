<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'faculty_id',
        'academic_year_id',
        'activity_type',
        'activity_name',
        'description',
        'role',
        'start_date',
        'end_date',
        'duration_days',
        'level',
        'document_path',
        'api_score',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'api_score' => 'decimal:2',
    ];

    /**
     * Get the faculty that owns this activity
     */
    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the academic year
     */
    public function academicYear()
    {
        return $this->belongsTo(ApiAcademicYear::class);
    }

    /**
     * Get activity type label
     */
    public function getTypeLabel()
    {
        return match ($this->activity_type) {
            'cocurricular' => 'Co-curricular Activity',
            'managerial' => 'Managerial Contribution',
            'professional_development' => 'Professional Development',
            'seminar_conference' => 'Seminar/Conference',
            default => $this->activity_type,
        };
    }
}
