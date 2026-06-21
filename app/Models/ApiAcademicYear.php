<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiAcademicYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year_name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get all faculty scores for this academic year
     */
    public function facultyScores()
    {
        return $this->hasMany(ApiFacultyScore::class, 'academic_year_id');
    }

    /**
     * Get all publications for this academic year
     */
    public function publications()
    {
        return $this->hasMany(ApiPublication::class, 'academic_year_id');
    }

    /**
     * Get all activities for this academic year
     */
    public function activities()
    {
        return $this->hasMany(ApiActivity::class, 'academic_year_id');
    }

    /**
     * Scope to get active academic year
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the currently active academic year
     */
    public static function getActive()
    {
        return self::where('status', 'active')->first();
    }
}
