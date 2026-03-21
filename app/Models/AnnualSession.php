<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualSession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'status'
    ];

    /**
     * Get leave applications for this session
     */
    public function facultyLeaveApplications()
    {
        return $this->hasMany(FacultyLeaveApplication::class, 'annual_session_id');
    }

    /**
     * Scope for active session
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the active session title
     */
    public function getIsActiveAttribute()
    {
        return $this->status == 1;
    }
}
