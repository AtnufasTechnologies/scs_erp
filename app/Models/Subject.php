<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'main_dept_id',
        'has_shift_delivery',
        'shift_ids',
        'allow_multi_primary_faculty',
    ];

    protected $casts = [
        'shift_ids' => 'array',
        'has_shift_delivery' => 'boolean',
        'allow_multi_primary_faculty' => 'boolean',
    ];

    function campusmaster()
    {
        return $this->hasOne(Campus::class, 'id', 'campus_id');
    }

    function syllabus()
    {
        return $this->hasMany(SubjectHasSyllabus::class, 'subject_id', 'id');
    }

    function semesters()
    {
        return $this->hasMany(SubjectHasSemester::class, 'subject_id', 'id');
    }


    function combinations()
    {
        return $this->hasMany(SubjectHasStudentProgam::class, 'subject_id', 'id');
    }

    function courseMasterPivot()
    {
        return $this->hasMany(SubjectCourseMaster::class, 'subject_id', 'id');
    }
}
