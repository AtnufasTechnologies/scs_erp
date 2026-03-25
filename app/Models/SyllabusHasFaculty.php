<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusHasFaculty extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'faculty_id',
    ];

    function syllabusroutine()
    {
        return $this->hasOne(SubjectHasSyllabus::class, 'id', 'syllabus_id');
    }

    function faculty()
    {
        return $this->belongsTo(Faculty::class, 'faculty_id', 'id');
    }

    function attendances()
    {
        return $this->hasMany(StudentAttendance::class, 'syllabus_faculty_id', 'id');
    }
}
