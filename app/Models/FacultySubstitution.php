<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultySubstitution extends Model
{
    use HasFactory;

    protected $fillable = [
        'routine_id',
        'original_faculty_id',
        'substitute_faculty_id',
        'substitution_date',
        'hour_number',
        'day_of_week',
        'reason',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'substitution_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function routine()
    {
        return $this->belongsTo(SubjectHasRoutine::class, 'routine_id');
    }

    public function originalFaculty()
    {
        return $this->belongsTo(Faculty::class, 'original_faculty_id');
    }

    public function substituteFaculty()
    {
        return $this->belongsTo(Faculty::class, 'substitute_faculty_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
