<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectHasSyllabus extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'batch_id',
        'semester_id',
        'course_id'
    ];


    function batchmaster()
    {
        return $this->belongsTo(BatchMaster::class, 'batch_id', 'id');
    }

    function sessionmaster()
    {
        return $this->belongsTo(BatchMaster::class, 'session_id', 'id');
    }

    function semestermaster()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id');
    }
    function subtypemaster()
    {
        return $this->belongsTo(SubjectTypeMaster::class, 'subject_type_id', 'id');
    }

    function timetable()
    {
        return $this->hasOne(SubjectHasRoutine::class, 'syllabus_id', 'id');
    }

    function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'id');
    }

    function courseLink()
    {
        return $this->belongsTo(SubjectCourseMaster::class, 'course_id', 'course_master_id');
    }

    function syllabusManagers()
    {
        return $this->hasMany(SyllabusManager::class, 'subject_id', 'subject_id')
            ->where('batch_id', $this->batch_id)
            ->where('semester_id', $this->semester_id);
    }

    function syllabusunits()
    {
        return $this->hasManyThrough(
            SyllabusSubunit::class,
            SyllabusManager::class,
            'subject_id', // Foreign key on SyllabusManager table
            'syllabus_manager_id', // Foreign key on SyllabusSubunit table
            'subject_id', // Local key on SubjectHasSyllabus table
            'id' // Local key on SyllabusManager table
        )
            ->whereHas('syllabusManager', function ($query) {
                $query->where('batch_id', $this->batch_id)
                    ->where('semester_id', $this->semester_id);
            });
    }

    function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id');
    }

    function courseCombination()
    {
        return $this->belongsTo(CourseCombination::class, 'course_id', 'id');
    }

    function coursemaster()
    {
        return $this->belongsTo(ProgramCourseMaster::class, 'course_id', 'id');
    }
}
